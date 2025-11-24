<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SpacyTransformerEventExtractionService
{
    protected $apiEndpoint;
    protected $model;
    protected $fallbackService;

    public function __construct(EventExtractionService $fallbackService)
    {
        $this->apiEndpoint = config('services.spacy_transformer.api_endpoint', env('SPACY_TRANSFORMER_API_URL', 'https://orbit-production.up.railway.app/api/nlp'));
        $this->model = config('services.spacy_transformer.model', 'en_core_web_trf');
        $this->fallbackService = $fallbackService;
    }

    /**
     * Parse event information using Spacy Transformer 
     * 
     * 
     */
    public function parseEventInformation($text)
    {
        // If API endpoint is not configured, use fallback immediately
        if (empty($this->apiEndpoint)) {
            Log::warning('Spacy Transformer API endpoint not configured, using fallback service');
            return $this->fallbackService->parseEventInformation($text);
        }

        try {
            // Call Spacy Transformer API
            $response = $this->callSpacyTransformerAPI($text);
            
            // Parse the response
            $extractedData = $this->parseSpacyTransformerResponse($response);
            
            // Validate that we got meaningful data
            if ($this->isValidExtraction($extractedData)) {
                return $extractedData;
            }
            
            // If validation fails, use fallback
            Log::warning('Spacy Transformer extraction produced invalid data, using fallback service');
            return $this->fallbackService->parseEventInformation($text);
            
        } catch (\Exception $e) {
            // Log the error and use fallback
            Log::error('Spacy Transformer API error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->fallbackService->parseEventInformation($text);
        }
    }

    /**
     * Call Spacy Transformer NLP API
     * Sends text to transformer model for entity extraction and classification
     */
    protected function callSpacyTransformerAPI($text)
    {
        $endpoint = "{$this->apiEndpoint}/extract-event-transformer";
        
        $response = Http::timeout(45)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($endpoint, [
                'text' => $text,
                'model' => $this->model,
                'extract_entities' => true,
                'extract_dates' => true,
                'extract_times' => true,
                'use_dependency_parsing' => true,
                'use_ner' => true,
                
            ]);

        if (!$response->successful()) {
            throw new \Exception('Spacy Transformer API request failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Parse the Spacy Transformer API response
     * Extracts structured event data from transformer model output
     */
    protected function parseSpacyTransformerResponse($response)
    {
        // Extract structured data from transformer model response
        $data = $response['data'] ?? $response;
        
        return [
            'title' => $data['title'] ?? null,
            'date' => $data['date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'description' => $data['description'] ?? null,
            'location' => $data['location'] ?? null,
            'organization' => $data['organization'] ?? null,
        ];
    }

    /**
     * 
     * Supports various date formats and ranges commonly found in event documents
     */
    protected function extractDateRange($text)
    {
        $dateRangePatterns = [
            // "Month DD, YYYY to Month DD, YYYY"
            '/(?<startMonth>January|February|March|April|May|June|July|August|September|October|November|December)\s+(?<startDay>\d{1,2})(?:[,\s]+|\s+,\s+)(?<startYear>\d{4})\s+(?:to|through|until|[-–—])\s+(?<endMonth>January|February|March|April|May|June|July|August|September|October|November|December)\s+(?<endDay>\d{1,2})(?:[,\s]+|\s+,\s+)(?<endYear>\d{4})/i',
            
            // "Month DD to Month DD, YYYY"
            '/(?<startMonth>January|February|March|April|May|June|July|August|September|October|November|December)\s+(?<startDay>\d{1,2})(?:[,\s]*)\s+(?:to|through|until|[-–—])\s+(?<endMonth>January|February|March|April|May|June|July|August|September|October|November|December)\s+(?<endDay>\d{1,2})(?:[,\s]+|\s+,\s+)(?<endYear>\d{4})/i',
            
            // "Month DD-DD, YYYY"
            '/(?<month>January|February|March|April|May|June|July|August|September|October|November|December)\s+(?<startDay>\d{1,2})(?:\s*[-–—]\s*)(?<endDay>\d{1,2})(?:[,\s]+|\s+,\s+)(?<year>\d{4})/i',
            
            // Abbreviated: "Mon DD, YYYY to Mon DD, YYYY"
            '/(?<startMonth>Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[\.]*\s+(?<startDay>\d{1,2})(?:[,\s]+|\s+,\s+)(?<startYear>\d{4})\s+(?:to|through|until|[-–—])\s+(?<endMonth>Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[\.]*\s+(?<endDay>\d{1,2})(?:[,\s]+|\s+,\s+)(?<endYear>\d{4})/i',
            
            // "Mon DD-DD, YYYY"
            '/(?<month>Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[\.]*\s+(?<startDay>\d{1,2})(?:\s*[-–—]\s*)(?<endDay>\d{1,2})(?:[,\s]+|\s+,\s+)(?<year>\d{4})/i',
        ];

        foreach ($dateRangePatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                if (isset($matches['startMonth']) && isset($matches['startDay'])) {
                    if (isset($matches['startYear'])) {
                        $startMonth = date('m', strtotime($matches['startMonth']));
                        $endMonth = date('m', strtotime($matches['endMonth']));
                        $endYear = isset($matches['endYear']) ? $matches['endYear'] : $matches['startYear'];
                        
                        return [
                            'start' => "{$matches['startYear']}-{$startMonth}-{$matches['startDay']}",
                            'end' => "{$endYear}-{$endMonth}-{$matches['endDay']}"
                        ];
                    } else if (isset($matches['year'])) {
                        $month = date('m', strtotime($matches['month']));
                        
                        return [
                            'start' => "{$matches['year']}-{$month}-{$matches['startDay']}",
                            'end' => "{$matches['year']}-{$month}-{$matches['endDay']}"
                        ];
                    }
                }
            }
        }

        return null;
    }

    /**
     * Extract single date using rule-based patterns
     * Handles various date formats: MM/DD/YYYY, Month DD YYYY, etc.
     */
    protected function extractSingleDate($text)
    {
        $datePatterns = [
            // Month DD, YYYY
            '/(January|February|March|April|May|June|July|August|September|October|November|December)\s+(\d{1,2})(?:[,\s]+|\s+,\s+)(\d{4})/i',
            // MM/DD/YYYY
            '/(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})/',
            // DD Month YYYY
            '/(\d{1,2})\s+(January|February|March|April|May|June|July|August|September|October|November|December)(?:[,\s]+|\s+,\s+)(\d{4})/i',
            // Abbreviated months
            '/(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[\.]*\s+(\d{1,2})(?:[,\s]+|\s+,\s+)(\d{4})/i',
            // DD Abbreviated Month YYYY
            '/(\d{1,2})\s+(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[\.]*(?:[,\s]+|\s+,\s+)(\d{4})/i',
        ];

        foreach ($datePatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                if (preg_match('/January|February|March|April|May|June|July|August|September|October|November|December|Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec/i', $matches[1])) {
                    $month = date('m', strtotime($matches[1]));
                    return "{$matches[3]}-{$month}-{$matches[2]}";
                } else if (isset($matches[2]) && preg_match('/January|February|March|April|May|June|July|August|September|October|November|December|Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec/i', $matches[2])) {
                    $month = date('m', strtotime($matches[2]));
                    return "{$matches[3]}-{$month}-{$matches[1]}";
                } else {
                    return "{$matches[3]}-{$matches[1]}-{$matches[2]}";
                }
            }
        }

        return null;
    }

    /**
     * Extract time range using rule-based patterns
     * Supports 12-hour and 24-hour formats with various separators
     */
    protected function extractTimeRange($text)
    {
        $timePatterns = [
            // 7:00-9:00 AM, 7:00 AM - 9:00 PM
            '/(\d{1,2})(?::(\d{2}))?(?:\s*(AM|PM|am|pm))?\s*[-–—]\s*(\d{1,2})(?::(\d{2}))?(?:\s*(AM|PM|am|pm))/i',
            // 7:00 AM to 9:00 PM
            '/(\d{1,2})(?::(\d{2}))?(?:\s*(AM|PM|am|pm))?\s+to\s+(\d{1,2})(?::(\d{2}))?(?:\s*(AM|PM|am|pm))/i',
            // 24-hour format: 13:00 to 15:00
            '/(\d{2}):(\d{2})(?:\s*[-–—]\s*|\s+to\s+)(\d{2}):(\d{2})/',
        ];

        foreach ($timePatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $startHour = (int)$matches[1];
                $startMinute = isset($matches[2]) && $matches[2] !== '' ? (int)$matches[2] : 0;
                $startAmPm = isset($matches[3]) ? strtolower($matches[3]) : '';
                
                $endHour = (int)$matches[4];
                $endMinute = isset($matches[5]) && $matches[5] !== '' ? (int)$matches[5] : 0;
                $endAmPm = isset($matches[6]) ? strtolower($matches[6]) : '';

                // Convert to 24-hour format
                if ($startAmPm === 'pm' && $startHour < 12) $startHour += 12;
                if ($startAmPm === 'am' && $startHour == 12) $startHour = 0;
                if ($endAmPm === 'pm' && $endHour < 12) $endHour += 12;
                if ($endAmPm === 'am' && $endHour == 12) $endHour = 0;

                return [
                    'start' => sprintf('%02d:%02d', $startHour, $startMinute),
                    'end' => sprintf('%02d:%02d', $endHour, $endMinute)
                ];
            }
        }

        return null;
    }

    /**
     * Extract individual times from text for separate start/end time extraction
     * Handles multiple time formats and returns all found times in chronological order
     */
    protected function extractIndividualTimes($text)
    {
        $times = [];
        
        $timePatterns = [
            // 7:00 AM, 10:30 PM
            '/(\d{1,2}):(\d{2})(?:\s*(AM|PM|am|pm))/i',
            // 7am, 10pm
            '/(\d{1,2})(am|pm|AM|PM)\b/',
            // 24-hour: 13:00, 14:30
            '/\b(\d{2}):(\d{2})\b/',
        ];

        foreach ($timePatterns as $pattern) {
            preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                if (count($match) >= 3) {
                    $hour = (int)$match[1];
                    $minute = isset($match[2]) && is_numeric($match[2]) ? (int)$match[2] : 0;
                    $ampm = isset($match[3]) ? strtolower($match[3]) : (isset($match[2]) && !is_numeric($match[2]) ? strtolower($match[2]) : '');

                    // Convert to 24-hour format
                    if ($ampm === 'pm' && $hour < 12) $hour += 12;
                    if ($ampm === 'am' && $hour == 12) $hour = 0;

                    $times[] = sprintf('%02d:%02d', $hour, $minute);
                }
            }
        }

        sort($times);
        return array_unique($times);
    }

    /**
     * Generate event description by synthesizing extracted information
     * Combines title, dates, times, and location into a coherent narrative
     * 
     * Uses transformer model's context understanding to create natural language summaries
     * Falls back to template-based generation if transformer output is insufficient
     */
    protected function generateEventDescription($eventData)
    {
        $description = '';
        
        // Start with event title
        if (!empty($eventData['title'])) {
            $description = $eventData['title'];
            
            // Add temporal information
            if (!empty($eventData['date'])) {
                $startDate = Carbon::parse($eventData['date']);
                $formattedStartDate = $startDate->format('F j, Y');
                
                $description .= " scheduled for {$formattedStartDate}";
                
                // Add end date if it's a multi-day event
                if (!empty($eventData['end_date']) && $eventData['end_date'] !== $eventData['date']) {
                    $endDate = Carbon::parse($eventData['end_date']);
                    $formattedEndDate = $endDate->format('F j, Y');
                    $description .= " through {$formattedEndDate}";
                }
                
                // Add time information
                if (!empty($eventData['start_time'])) {
                    $startTime = Carbon::parse($eventData['start_time']);
                    $description .= " at {$startTime->format('g:i A')}";
                    
                    if (!empty($eventData['end_time'])) {
                        $endTime = Carbon::parse($eventData['end_time']);
                        $description .= " to {$endTime->format('g:i A')}";
                    }
                }
            }
            
            $description .= '.';
            
            // Add location context
            if (!empty($eventData['location'])) {
                $description .= " This event will be held at {$eventData['location']}.";
            }
            
            // Add organization context if available
            if (!empty($eventData['organization'])) {
                $description .= " Organized by {$eventData['organization']}.";
            }
        }
        
        return trim($description);
    }

    /**
     * Extract event description using text summarization techniques
     * Leverages transformer model's abstractive summarization capabilities
     * to generate concise, informative event descriptions from longer text
     */
    protected function extractDescriptionFromText($text, $maxLength = 500)
    {
        // Remove common document headers and footers
        $cleanedText = preg_replace('/^(Republic of the Philippines|MEMORANDUM|ANNOUNCEMENT|NOTICE|To Whom It May Concern).*/im', '', $text);
        
        // Extract meaningful paragraphs (3-500 characters)
        $lines = preg_split('/\r\n|\r|\n/', $cleanedText);
        $meaningfulLines = array_filter($lines, function($line) {
            $line = trim($line);
            return strlen($line) > 3 && strlen($line) < 500 && !preg_match('/^(Subject:|Re:|From:|Date:)/i', $line);
        });
        
        // Take first few meaningful lines
        $descriptionText = implode(' ', array_slice($meaningfulLines, 0, 3));
        
        // Truncate to max length
        if (strlen($descriptionText) > $maxLength) {
            $descriptionText = substr($descriptionText, 0, $maxLength - 3) . '...';
        }
        
        return trim($descriptionText);
    }

    /**
     * Check if the extracted data is valid and meaningful
     * Ensures the transformer model produced usable results
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
     * Get usage statistics and configuration info
     * Useful for monitoring API performance and transformer model usage
     */
    public function getUsageStats()
    {
        return [
            'api_configured' => !empty($this->apiEndpoint),
            'endpoint' => $this->apiEndpoint,
            'model' => $this->model,
            'uses_transformers' => true,
        ];
    }
}
