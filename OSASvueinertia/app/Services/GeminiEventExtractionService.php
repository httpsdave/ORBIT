<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GeminiEventExtractionService
{
    protected $apiKey;
    protected $model;
    protected $fallbackService;

    public function __construct(EventExtractionService $fallbackService)
    {
        $this->apiKey = config('services.google.gemini_api_key');
        $this->model = config('services.google.gemini_model', 'gemini-2.5-flash');
        $this->fallbackService = $fallbackService;
    }

    /**
     * Parse event information using Google Gemini API
     * Falls back to the original EventExtractionService if API fails
     */
    public function parseEventInformation($text)
    {
        // If API key is not configured, use fallback immediately
        if (empty($this->apiKey)) {
            Log::warning('Google Gemini API key not configured, using fallback service');
            return $this->fallbackService->parseEventInformation($text);
        }

        try {
            // Call Gemini API
            $response = $this->callGeminiAPI($text);
            
            // Parse and validate the response
            $extractedData = $this->parseGeminiResponse($response);
            
            // Validate that we got meaningful data
            if ($this->isValidExtraction($extractedData)) {
                return $extractedData;
            }
            
            // If validation fails, use fallback
            Log::warning('Gemini extraction produced invalid data, using fallback service');
            return $this->fallbackService->parseEventInformation($text);
            
        } catch (\Exception $e) {
            // Log the error and use fallback
            Log::error('Gemini API error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->fallbackService->parseEventInformation($text);
        }
    }

    /**
     * Call Google Gemini API with the extracted text
     */
    protected function callGeminiAPI($text)
    {
        $prompt = $this->buildPrompt($text);
        
        $endpoint = "https://generativelanguage.googleapis.com/v1/models/{$this->model}:generateContent?key={$this->apiKey}";
        
        $response = Http::timeout(30)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($endpoint, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'topK' => 1,
                    'topP' => 1,
                    'maxOutputTokens' => 2048,
                ],
                'safetySettings' => [
                    [
                        'category' => 'HARM_CATEGORY_HARASSMENT',
                        'threshold' => 'BLOCK_NONE'
                    ],
                    [
                        'category' => 'HARM_CATEGORY_HATE_SPEECH',
                        'threshold' => 'BLOCK_NONE'
                    ],
                    [
                        'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                        'threshold' => 'BLOCK_NONE'
                    ],
                    [
                        'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                        'threshold' => 'BLOCK_NONE'
                    ],
                ],
            ]);

        if (!$response->successful()) {
            throw new \Exception('Gemini API request failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Build the prompt for Gemini API
     */
    protected function buildPrompt($text)
    {
        return <<<PROMPT
You are an expert at extracting event information from documents. Analyze the following text and extract event details.

**IMPORTANT INSTRUCTIONS:**
1. Extract ONLY factual information present in the text
2. For dates, identify both start and end dates if mentioned
3. Recognize date ranges in formats like:
   - "September 22-28, 2025"
   - "September 22 to September 28, 2025"
   - "September 22 through 28, 2025"
   - "From September 22 to September 28, 2025"
4. For times, extract start and end times in 24-hour format (HH:MM)
5. Skip common document headers like "Republic of the Philippines", "Laguna State Polytechnic University", etc.
6. The title should be the actual event name, not the document type
7. Extract the organizing body/organization if mentioned

**Text to analyze:**
{$text}

**Return ONLY a valid JSON object with these exact fields:**
{
    "title": "Event title (required, skip headers)",
    "date": "YYYY-MM-DD (required, start date)",
    "end_date": "YYYY-MM-DD (same as date if single day, or actual end date for multi-day events)",
    "start_time": "HH:MM (24-hour format, or null if not specified)",
    "end_time": "HH:MM (24-hour format, or null if not specified)",
    "description": "Brief event description, use only content that is in the file, do not add or subtract words. (or null)",
    "location": "Event location/venue (or null)",
    "organization": "Organizing body/department (or null)"
}

**Rules:**
- If a field is not found, use null (not empty string)
- Dates must be in YYYY-MM-DD format
- Times must be in 24-hour HH:MM format
- Return ONLY the JSON object, no additional text
- If you cannot extract any information, return all fields as null
PROMPT;
    }

    /**
     * Parse the Gemini API response
     */
    protected function parseGeminiResponse($response)
    {
        // Extract the text content from Gemini response
        $content = $response['candidates'][0]['content']['parts'][0]['text'] ?? null;
        
        if (empty($content)) {
            throw new \Exception('Empty response from Gemini API');
        }

        // Clean the response (remove markdown code blocks if present)
        $content = preg_replace('/```json\s*/', '', $content);
        $content = preg_replace('/```\s*$/', '', $content);
        $content = trim($content);

        // Parse JSON
        $data = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON from Gemini API: ' . json_last_error_msg());
        }

        // Ensure all required fields exist
        $result = [
            'title' => $data['title'] ?? null,
            'date' => $data['date'] ?? null,
            'end_date' => $data['end_date'] ?? $data['date'] ?? null,
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'description' => $data['description'] ?? null,
            'location' => $data['location'] ?? null,
            'organization' => $data['organization'] ?? null,
        ];

        // Convert empty strings to null
        foreach ($result as $key => $value) {
            if ($value === '' || $value === 'null') {
                $result[$key] = null;
            }
        }

        // Validate and format dates
        $result = $this->validateAndFormatDates($result);

        return $result;
    }

    /**
     * Validate and format date fields
     */
    protected function validateAndFormatDates($data)
    {
        // Validate and format start date
        if ($data['date']) {
            try {
                $date = Carbon::parse($data['date']);
                $data['date'] = $date->format('Y-m-d');
            } catch (\Exception $e) {
                $data['date'] = null;
            }
        }

        // Validate and format end date
        if ($data['end_date']) {
            try {
                $endDate = Carbon::parse($data['end_date']);
                $data['end_date'] = $endDate->format('Y-m-d');
            } catch (\Exception $e) {
                $data['end_date'] = $data['date']; // Fall back to start date
            }
        } else {
            $data['end_date'] = $data['date']; // Default to start date
        }

        // Validate time formats (HH:MM)
        if ($data['start_time'] && !preg_match('/^\d{2}:\d{2}$/', $data['start_time'])) {
            $data['start_time'] = null;
        }

        if ($data['end_time'] && !preg_match('/^\d{2}:\d{2}$/', $data['end_time'])) {
            $data['end_time'] = null;
        }

        return $data;
    }

    /**
     * Check if the extracted data is valid and meaningful
     */
    protected function isValidExtraction($data)
    {
        // At minimum, we need a title or a date
        if (empty($data['title']) && empty($data['date'])) {
            return false;
        }

        // If we have a title, it should be meaningful (not just whitespace or too short)
        if (!empty($data['title'])) {
            $title = trim($data['title']);
            if (strlen($title) < 3) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get usage statistics (for monitoring API calls)
     */
    public function getUsageStats()
    {
        // This could be expanded to track API usage, costs, etc.
        return [
            'api_configured' => !empty($this->apiKey),
            'model' => $this->model,
        ];
    }
}
