<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\EventExtractionService;
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

    public function __construct(EventExtractionService $eventExtractionService)
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
                $text = (new TesseractOCR(storage_path('app/public/' . $filePath)))
                    ->run();
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
    }
}