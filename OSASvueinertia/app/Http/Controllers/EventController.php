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

        // Extract event information from the text using the service
        $extractedInfo = $this->eventExtractionService->parseEventInformation($text);
        
        // Add the file path to the response
        $extractedInfo['source_document'] = $filePath;

        return response()->json($extractedInfo);
    }
}