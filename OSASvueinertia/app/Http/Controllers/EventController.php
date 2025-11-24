<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\EventExtractionService;
use App\Services\GeminiEventExtractionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    protected $eventExtractionService;

    public function __construct(GeminiEventExtractionService $eventExtractionService)
    {
        $this->eventExtractionService = $eventExtractionService;
    }

    /**
     * Display the calendar page
     */
    public function index()
    {
        $events = Event::all();
        $isAdmin = Auth::check() && Auth::user()->isAdmin();

        return Inertia::render('Calendar', [
            'initialEvents' => $events,
            'isAdmin' => $isAdmin
        ]);
    }

    /**
     * Store a new event
     */
    public function store(Request $request)
    {
        // Check if user is admin
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'organization' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,cancelled'
        ]);

        $event = Event::create($validated);

        return response()->json($event, 201);
    }

    /**
     * Update an event
     */
    public function update(Request $request, Event $event)
    {
        // Check if user is admin
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'organization' => 'nullable|string|max:255',
            'status' => 'sometimes|in:active,cancelled'
        ]);

        $event->update($validated);

        return response()->json($event);
    }

    /**
     * Cancel an event
     */
    public function cancel(Event $event)
    {
        // Check if user is admin
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event->update(['status' => 'cancelled']);
        return response()->json($event);
    }

    /**
     * Delete an event
     */
    public function destroy(Event $event)
    {
        // Check if user is admin
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event->delete();
        return response()->json(null, 204);
    }

    /**
     * Get all events (excluding past events)
     */
    public function getEvents()
    {
        // Get events that haven't ended yet (future and current events) and are not cancelled
        $events = Event::where(function($query) {
                        $query->where('end_date', '>=', Carbon::today())
                              ->orWhere(function($subQuery) {
                                  // Include events with no end_date that started today or later
                                  $subQuery->whereNull('end_date')
                                          ->whereDate('start_date', '>=', Carbon::today());
                              });
                    })
                    ->where(function($query) {
                        $query->where('status', '!=', 'cancelled')
                              ->orWhereNull('status');
                    })
                    ->orderBy('start_date', 'asc')
                    ->get();
        
        return response()->json($events);
    }

    /**
     * Extract event info from document
     */
    public function extractEventInfo(Request $request)
    {
        // Check if user is admin
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'document' => 'required|file|mimes:jpeg,png,pdf|max:10240',
        ]);

        $file = $request->file('document');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('documents', $fileName, 'public');

        // Extract text based on file type
        $text = '';
        $extension = $file->getClientOriginalExtension();
        $ocrMethod = 'tesseract'; // Track which OCR method was used
        
        if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
            // Try Tesseract OCR first
            try {
                // Optionally upscale low-resolution images before OCR
                $processedPath = $this->preprocessImageForOCR(storage_path('app/public/' . $filePath), $extension);
                
                $ocr = new TesseractOCR($processedPath);
                
                // Configure Tesseract for better accuracy with resolution scaling
                $ocr->lang('eng')  // English language
                    ->psm(3)       // Automatic page segmentation (assumes single block of text)
                    ->oem(1)       // Neural nets LSTM engine (better accuracy)
                    ->dpi(300);    // Set DPI to 300 for optimal character recognition
                
                $text = $ocr->run();
                
                // Clean up temporary preprocessed file if it was created
                if ($processedPath !== storage_path('app/public/' . $filePath) && file_exists($processedPath)) {
                    @unlink($processedPath);
                }
                
                // If text is empty or too short, it might have failed
                if (empty(trim($text)) || strlen(trim($text)) < 10) {
                    throw new \Exception("Tesseract returned insufficient text");
                }
                
            } catch (\Exception $e) {
                // If Tesseract fails, fall back to OCR.Space
                try {
                    $text = $this->extractTextWithOCRSpace($file);
                    $ocrMethod = 'ocrspace';
                } catch (\Exception $ocrSpaceException) {
                    return response()->json([
                        'error' => 'Failed to extract text from image. Both Tesseract and OCR.Space failed.',
                        'tesseract_error' => $e->getMessage(),
                        'ocrspace_error' => $ocrSpaceException->getMessage()
                    ], 500);
                }
            }
        } else if ($extension === 'pdf') {
            // For PDFs, use PDF Parser
            try {
                $parser = new Parser();
                $pdf = $parser->parseFile(storage_path('app/public/' . $filePath));
                $text = $pdf->getText();
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Failed to parse PDF file.',
                    'pdf_error' => $e->getMessage()
                ], 500);
            }
        }

        // Extract event information from the text using the service
        $extractedInfo = $this->eventExtractionService->parseEventInformation($text);
        
        // Add the file path, raw text, and OCR method to the response
        $extractedInfo['source_document'] = $filePath;
        $extractedInfo['raw_text'] = $text;
        $extractedInfo['ocr_method'] = $ocrMethod;

        return response()->json($extractedInfo);
    }

    /**
     * Extract text from image using OCR.Space API
     */
    private function extractTextWithOCRSpace($file)
    {
        $apiKey = 'K84437262088957';
        $url = 'https://api.ocr.space/parse/image';
        
        // Prepare the file for upload
        $filePath = $file->getRealPath();
        $fileName = $file->getClientOriginalName();
        $maxSizeBytes = 1024 * 1024; // 1MB limit for OCR.Space
        $isCompressed = false;
        
        // Check if file needs compression
        if (filesize($filePath) > $maxSizeBytes) {
            $filePath = $this->compressImage($filePath, $file->getClientOriginalExtension());
            $isCompressed = true;
        }
        
        try {
            // Create cURL request
            $ch = curl_init();
            
            $postData = [
                'apikey' => $apiKey,
                'language' => 'eng',
                'isOverlayRequired' => 'false',
                'filetype' => strtolower($file->getClientOriginalExtension()),
                'detectOrientation' => 'true',
                'scale' => 'true',
                'OCREngine' => '2' // Use OCR Engine 2 for better accuracy
            ];
            
            // Add file to the request
            $postData['file'] = new \CURLFile($filePath, $file->getMimeType(), $fileName);
            
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                throw new \Exception("cURL Error: " . $error);
            }
            
            if ($httpCode !== 200) {
                throw new \Exception("OCR.Space API returned HTTP code: " . $httpCode);
            }
            
            $result = json_decode($response, true);
            
            if (!$result) {
                throw new \Exception("Failed to decode OCR.Space response");
            }
            
            if ($result['IsErroredOnProcessing']) {
                throw new \Exception("OCR.Space processing error: " . ($result['ErrorMessage'] ?? 'Unknown error'));
            }
            
            if (empty($result['ParsedResults'])) {
                throw new \Exception("No text found in the image");
            }
            
            // Extract text from all parsed results
            $extractedText = '';
            foreach ($result['ParsedResults'] as $parsedResult) {
                if (isset($parsedResult['ParsedText'])) {
                    $extractedText .= $parsedResult['ParsedText'] . "\n";
                }
            }
            
            return trim($extractedText);
            
        } finally {
            // Clean up temporary compressed file
            if ($isCompressed && file_exists($filePath)) {
                @unlink($filePath);
            }
        }
    }

    /**
     * Preprocess image for better OCR accuracy
     * Upscales low-resolution images and enhances contrast
     */
    private function preprocessImageForOCR($filePath, $extension)
    {
        $minDPI = 200; // Minimum recommended DPI for OCR
        $targetDPI = 300; // Optimal DPI for OCR
        
        try {
            // Load image based on type
            $image = null;
            switch (strtolower($extension)) {
                case 'jpg':
                case 'jpeg':
                    $image = imagecreatefromjpeg($filePath);
                    break;
                case 'png':
                    $image = imagecreatefrompng($filePath);
                    break;
                default:
                    return $filePath; // Return original if unsupported type
            }
            
            if (!$image) {
                return $filePath;
            }
            
            $width = imagesx($image);
            $height = imagesy($image);
            
            // Calculate approximate DPI based on image dimensions
            // Assume standard letter size (8.5 x 11 inches) if image is small
            $estimatedDPI = max($width / 8.5, $height / 11);
            
            // Only upscale if image appears to be low resolution
            if ($estimatedDPI < $minDPI) {
                $scaleFactor = $targetDPI / $estimatedDPI;
                $newWidth = (int)($width * $scaleFactor);
                $newHeight = (int)($height * $scaleFactor);
                
                // Create high-resolution image with bicubic interpolation
                $upscaled = imagecreatetruecolor($newWidth, $newHeight);
                
                // Preserve transparency for PNG
                if (strtolower($extension) === 'png') {
                    imagealphablending($upscaled, false);
                    imagesavealpha($upscaled, true);
                    $transparent = imagecolorallocatealpha($upscaled, 0, 0, 0, 127);
                    imagefill($upscaled, 0, 0, $transparent);
                }
                
                // Use bicubic interpolation for smoother upscaling
                imagecopyresampled($upscaled, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                
                // Apply slight sharpening to enhance text edges
                $sharpenMatrix = [
                    [-1, -1, -1],
                    [-1, 16, -1],
                    [-1, -1, -1]
                ];
                $divisor = 8;
                $offset = 0;
                imageconvolution($upscaled, $sharpenMatrix, $divisor, $offset);
                
                // Enhance contrast for better OCR
                imagefilter($upscaled, IMG_FILTER_CONTRAST, -10);
                
                // Save preprocessed image
                $tempPath = storage_path('app/temp_ocr_' . uniqid() . '.' . $extension);
                if (strtolower($extension) === 'png') {
                    imagepng($upscaled, $tempPath, 0); // No compression for OCR
                } else {
                    imagejpeg($upscaled, $tempPath, 95); // High quality for OCR
                }
                
                imagedestroy($image);
                imagedestroy($upscaled);
                
                return $tempPath;
            }
            
            // Image is already good resolution, return original
            imagedestroy($image);
            return $filePath;
            
        } catch (\Exception $e) {
            // If preprocessing fails, return original
            if (isset($image) && $image) {
                imagedestroy($image);
            }
            return $filePath;
        }
    }

    /**
     * Compress image to reduce file size for OCR.Space API
     */
    private function compressImage($filePath, $extension)
    {
        $maxSizeBytes = 1024 * 1024; // 1MB
        $quality = 85; // Start with 85% quality
        $tempPath = storage_path('app/temp_compressed_' . uniqid() . '.' . $extension);
        
        try {
            // Load image based on type
            $image = null;
            switch (strtolower($extension)) {
                case 'jpg':
                case 'jpeg':
                    $image = imagecreatefromjpeg($filePath);
                    break;
                case 'png':
                    $image = imagecreatefrompng($filePath);
                    break;
                default:
                    return $filePath; // Return original if unsupported type
            }
            
            if (!$image) {
                return $filePath;
            }
            
            // Try compressing with decreasing quality until under 1MB
            while ($quality > 20) {
                // Save compressed image
                if (strtolower($extension) === 'png') {
                    // PNG compression level (0-9, where 9 is highest compression)
                    $pngQuality = floor((100 - $quality) / 10);
                    imagepng($image, $tempPath, $pngQuality);
                } else {
                    imagejpeg($image, $tempPath, $quality);
                }
                
                // Check if file size is acceptable
                if (filesize($tempPath) <= $maxSizeBytes) {
                    imagedestroy($image);
                    return $tempPath;
                }
                
                // Reduce quality for next iteration
                $quality -= 15;
            }
            
            // If still too large, resize the image
            $width = imagesx($image);
            $height = imagesy($image);
            $newWidth = (int)($width * 0.7);
            $newHeight = (int)($height * 0.7);
            
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preserve transparency for PNG
            if (strtolower($extension) === 'png') {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
            }
            
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            
            // Save resized image
            if (strtolower($extension) === 'png') {
                imagepng($resized, $tempPath, 6);
            } else {
                imagejpeg($resized, $tempPath, 70);
            }
            
            imagedestroy($image);
            imagedestroy($resized);
            
            return $tempPath;
            
        } catch (\Exception $e) {
            // If compression fails, return original
            if (isset($image) && $image) {
                imagedestroy($image);
            }
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
            return $filePath;
        }
    }
}