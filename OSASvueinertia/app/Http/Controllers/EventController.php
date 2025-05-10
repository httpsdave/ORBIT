<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
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
            'description' => 'nullable|string'
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
            'description' => 'nullable|string'
        ]);

        $event->update($validated);

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
     * Get all events
     */
    public function getEvents()
    {
        $events = Event::all();
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
        
        if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
            // For images, use Tesseract OCR
            $text = (new TesseractOCR(storage_path('app/public/' . $filePath)))
                ->run();
        } else if ($extension === 'pdf') {
            // For PDFs, use PDF Parser
            $parser = new Parser();
            $pdf = $parser->parseFile(storage_path('app/public/' . $filePath));
            $text = $pdf->getText();
        }

        // Extract event information from the text
        $extractedInfo = $this->parseEventInformation($text);
        
        // Add the file path to the response
        $extractedInfo['source_document'] = $filePath;

        return response()->json($extractedInfo);
    }

    /**
     * Parse text to extract event information
     */
    private function parseEventInformation($text)
    {
        $result = [
            'title' => null,
            'date' => null,
            'start_time' => null,
            'end_time' => null,
            'description' => null,
        ];

        // Extract date (looking for common formats)
        $datePatterns = [
            // MM/DD/YYYY
            '/(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})/',
            // Month DD, YYYY
            '/(January|February|March|April|May|June|July|August|September|October|November|December)\s+(\d{1,2})[,\s]+(\d{4})/',
            // DD Month YYYY
            '/(\d{1,2})\s+(January|February|March|April|May|June|July|August|September|October|November|December)[,\s]+(\d{4})/',
            // YYYY-MM-DD
            '/(\d{4})[\/\-\.](\d{1,2})[\/\-\.](\d{1,2})/',
        ];

        foreach ($datePatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                // Convert to YYYY-MM-DD format based on the matched pattern
                if (count($matches) >= 4) {
                    if (preg_match('/January|February|March|April|May|June|July|August|September|October|November|December/', $matches[1])) {
                        // Month DD, YYYY format
                        $month = date('m', strtotime($matches[1]));
                        $day = $matches[2];
                        $year = $matches[3];
                        $result['date'] = "$year-$month-$day";
                    } elseif (preg_match('/January|February|March|April|May|June|July|August|September|October|November|December/', $matches[2])) {
                        // DD Month YYYY format
                        $day = $matches[1];
                        $month = date('m', strtotime($matches[2]));
                        $year = $matches[3];
                        $result['date'] = "$year-$month-$day";
                    } elseif (strlen($matches[1]) == 4) {
                        // YYYY-MM-DD format
                        $year = $matches[1];
                        $month = $matches[2];
                        $day = $matches[3];
                        $result['date'] = "$year-$month-$day";
                    } else {
                        // MM/DD/YYYY format
                        $month = $matches[1];
                        $day = $matches[2];
                        $year = $matches[3];
                        $result['date'] = "$year-$month-$day";
                    }
                    break;
                }
            }
        }

        // Extract time
        $timePattern = '/(\d{1,2}):(\d{2})(?:\s*(AM|PM|am|pm))?/';
        preg_match_all($timePattern, $text, $timeMatches, PREG_SET_ORDER);
        
        if (count($timeMatches) >= 1) {
            // Process first time found as start time
            $hour = $timeMatches[0][1];
            $minute = $timeMatches[0][2];
            $ampm = $timeMatches[0][3] ?? '';
            
            // Convert to 24-hour format if AM/PM is provided
            if (strtolower($ampm) === 'pm' && $hour < 12) {
                $hour += 12;
            } elseif (strtolower($ampm) === 'am' && $hour == 12) {
                $hour = 0;
            }
            
            $result['start_time'] = sprintf('%02d:%02d', $hour, $minute);
            
            // If we found a second time, use it as end time
            if (count($timeMatches) >= 2) {
                $hour = $timeMatches[1][1];
                $minute = $timeMatches[1][2];
                $ampm = $timeMatches[1][3] ?? '';
                
                // Convert to 24-hour format if AM/PM is provided
                if (strtolower($ampm) === 'pm' && $hour < 12) {
                    $hour += 12;
                } elseif (strtolower($ampm) === 'am' && $hour == 12) {
                    $hour = 0;
                }
                
                $result['end_time'] = sprintf('%02d:%02d', $hour, $minute);
            }
        }

        // Extract title (assuming it might be preceded by "Event:" or similar)
        $titlePatterns = [
            '/Event[:\s]+([^\n.]+)/',
            '/Title[:\s]+([^\n.]+)/',
            '/Meeting[:\s]+([^\n.]+)/',
            '/Appointment[:\s]+([^\n.]+)/',
        ];
        
        foreach ($titlePatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $result['title'] = trim($matches[1]);
                break;
            }
        }
        
        // If no title was found with the patterns, use the first line or sentence as a fallback
        if (!$result['title']) {
            // Try the first line
            $lines = explode("\n", $text);
            if (!empty($lines[0])) {
                $result['title'] = trim($lines[0]);
            } else {
                // Or try the first sentence
                $sentences = preg_split('/(?<=[.!?])\s+/', $text, 2);
                if (!empty($sentences[0])) {
                    $result['title'] = trim($sentences[0]);
                }
            }
        }
        
        // Truncate title if needed
        if ($result['title'] && strlen($result['title']) > 255) {
            $result['title'] = substr($result['title'], 0, 252) . '...';
        }

        // Extract description (rest of the text, limiting to a reasonable size)
        $description = trim($text);
        if (strlen($description) > 1000) {
            $description = substr($description, 0, 997) . '...';
        }
        $result['description'] = $description;

        return $result;
    }
}