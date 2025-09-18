<?php

namespace App\Services;

use Carbon\Carbon;

class EventExtractionService
{
    /**
     * Parse text to extract event information with improved date range detection
     */
    public function parseEventInformation($text)
    {
        $result = [
            'title' => null,
            'date' => null,
            'end_date' => null,
            'start_time' => null,
            'end_time' => null,
            'description' => null,
            'location' => null,
            'organization' => null,
        ];

        // List of phrases to exclude from title extraction
        $excludedPhrases = [
            'Bagong Pilipinas',
            'Republic of the Philippines',
            'Province of Laguna',
            'Laguna State Polytechnic University',
            'Laguna State',
            'Office of the President',
            'Department of Education',
            'MEMORANDUM',
            'ANNOUNCEMENT',
            'NOTICE',
            'INVITATION',
            'ADVISORY',
            'To Whom It May Concern',
            'To: All Concerned',
            'Subject:',
            'Re:',
            'Attention:',
            'Sir:',
            'BACONCG PILIPINAS',
            'OFFICE OF THE LOCAL COMMISION ON ELECTIONS',
            ';',
            'JOEL M. BAWICA',
            'Campus Director, San Pablo Campus',
            'Greetings of peace from the Office of the Local Commission on Elections of LSPU — SPCC!',
            '|',
            'elections, scheduled on Friday, April 25, 2025, from 8:00 A.M. to 12:00 P.M. at the LSPU - San Pablo City',
            'Campus gymnasium, we respectfully request the excusal of all students from their classes during the said',
            'BACONG PILIPINAS',
            'COLLEGE OF COMPUTER STUDIES',
            'period to participate in the event.'
        ];

        // Extract dates (looking for common formats)
        $datePatterns = [
            // MM/DD/YYYY
            '/(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})/',
            // Month DD, YYYY
            '/(January|February|March|April|May|June|July|August|September|October|November|December)\s+(\d{1,2})(?:[,\s]+|\s+,\s+)(\d{4})/',
            // DD Month YYYY
            '/(\d{1,2})\s+(January|February|March|April|May|June|July|August|September|October|November|December)(?:[,\s]+|\s+,\s+)(\d{4})/',
            // YYYY-MM-DD
            '/(\d{4})[\/\-\.](\d{1,2})[\/\-\.](\d{1,2})/',
            // Abbreviated months: Jan, Feb, etc.
            '/(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[\.]*\s+(\d{1,2})(?:[,\s]+|\s+,\s+)(\d{4})/',
            // DD Abbreviated Month YYYY
            '/(\d{1,2})\s+(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[\.]*(?:[,\s]+|\s+,\s+)(\d{4})/',
        ];

        // IMPROVED DATE RANGE PATTERNS
        $dateRangePatterns = [
            // "Month DD, YYYY to Month DD, YYYY" - Full format with different months/years
            '/(?<startMonth>January|February|March|April|May|June|July|August|September|October|November|December)\s+(?<startDay>\d{1,2})(?:[,\s]+|\s+,\s+)(?<startYear>\d{4})\s+(?:to|through|until|[-–—])\s+(?<endMonth>January|February|March|April|May|June|July|August|September|October|November|December)\s+(?<endDay>\d{1,2})(?:[,\s]+|\s+,\s+)(?<endYear>\d{4})/',
            
            // "Month DD to Month DD, YYYY" - Same year, different months
            '/(?<startMonth>January|February|March|April|May|June|July|August|September|October|November|December)\s+(?<startDay>\d{1,2})(?:[,\s]*)\s+(?:to|through|until|[-–—])\s+(?<endMonth>January|February|March|April|May|June|July|August|September|October|November|December)\s+(?<endDay>\d{1,2})(?:[,\s]+|\s+,\s+)(?<endYear>\d{4})/',
            
            // "Month DD-DD, YYYY" - Same month and year, different days
            '/(?<month>January|February|March|April|May|June|July|August|September|October|November|December)\s+(?<startDay>\d{1,2})(?:\s*[-–—]\s*)(?<endDay>\d{1,2})(?:[,\s]+|\s+,\s+)(?<year>\d{4})/',
            
            // Abbreviated versions
            
            // "Mon DD, YYYY to Mon DD, YYYY" - Abbreviated month names
            '/(?<startMonth>Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[\.]*\s+(?<startDay>\d{1,2})(?:[,\s]+|\s+,\s+)(?<startYear>\d{4})\s+(?:to|through|until|[-–—])\s+(?<endMonth>Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[\.]*\s+(?<endDay>\d{1,2})(?:[,\s]+|\s+,\s+)(?<endYear>\d{4})/',
            
            // "Mon DD to Mon DD, YYYY" - Same year, different months (abbreviated)
            '/(?<startMonth>Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[\.]*\s+(?<startDay>\d{1,2})(?:[,\s]*)\s+(?:to|through|until|[-–—])\s+(?<endMonth>Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[\.]*\s+(?<endDay>\d{1,2})(?:[,\s]+|\s+,\s+)(?<endYear>\d{4})/',
            
            // "Mon DD-DD, YYYY" - Same month and year, different days (abbreviated)
            '/(?<month>Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[\.]*\s+(?<startDay>\d{1,2})(?:\s*[-–—]\s*)(?<endDay>\d{1,2})(?:[,\s]+|\s+,\s+)(?<year>\d{4})/',
            
            // Common numeric formats
            
            // "MM/DD/YYYY to MM/DD/YYYY"
            '/(?<startMonth>\d{1,2})[\/\-\.](?<startDay>\d{1,2})[\/\-\.](?<startYear>\d{4})\s+(?:to|through|until|[-–—])\s+(?<endMonth>\d{1,2})[\/\-\.](?<endDay>\d{1,2})[\/\-\.](?<endYear>\d{4})/',
            
            // Generic "from DATE to DATE" pattern as fallback
            '/from\s+(.{10,25})\s+(?:to|through|until|[-–—])\s+(.{10,25})/',
        ];

        // First, try to find date ranges with named capturing groups
        $foundDateRange = false;
        
        foreach ($dateRangePatterns as $patternIndex => $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $foundDateRange = true;
                
                // Process matches using named capturing groups when available
                if (isset($matches['startMonth']) && isset($matches['startDay'])) {
                    // Pattern with named groups
                    if (isset($matches['startYear'])) {
                        // Complete date range pattern with different years
                        $startMonth = date('m', strtotime($matches['startMonth']));
                        $startDay = $matches['startDay'];
                        $startYear = $matches['startYear'];
                        
                        $endMonth = date('m', strtotime($matches['endMonth']));
                        $endDay = $matches['endDay'];
                        $endYear = isset($matches['endYear']) ? $matches['endYear'] : $startYear;
                        
                        $result['date'] = "$startYear-$startMonth-$startDay";
                        $result['end_date'] = "$endYear-$endMonth-$endDay";
                    } else if (isset($matches['year'])) {
                        // Same year pattern
                        $year = $matches['year'];
                        
                        if (isset($matches['endMonth'])) {
                            // Different months, same year
                            $startMonth = date('m', strtotime($matches['startMonth']));
                            $startDay = $matches['startDay'];
                            $endMonth = date('m', strtotime($matches['endMonth']));
                            $endDay = $matches['endDay'];
                        } else if (isset($matches['month'])) {
                            // Same month and year
                            $month = date('m', strtotime($matches['month']));
                            $startDay = $matches['startDay'];
                            $endDay = $matches['endDay'];
                            $startMonth = $endMonth = $month;
                        }
                        
                        $result['date'] = "$year-$startMonth-$startDay";
                        $result['end_date'] = "$year-$endMonth-$endDay";
                    }
                } 
                // Generic "from DATE to DATE" fallback pattern
                else if ($patternIndex === 7 && count($matches) >= 3) {
                    // Extract start date
                    foreach ($datePatterns as $datePattern) {
                        if (preg_match($datePattern, $matches[1], $startMatches)) {
                            $result['date'] = $this->formatExtractedDate($startMatches);
                            break;
                        }
                    }
                    
                    // Extract end date
                    foreach ($datePatterns as $datePattern) {
                        if (preg_match($datePattern, $matches[2], $endMatches)) {
                            $result['end_date'] = $this->formatExtractedDate($endMatches);
                            break;
                        }
                    }
                }
                
                break;
            }
        }

        // If no date range found, look for a single date
        if (!$foundDateRange) {
            foreach ($datePatterns as $pattern) {
                if (preg_match($pattern, $text, $matches)) {
                    $result['date'] = $this->formatExtractedDate($matches);
                    break;
                }
            }
        }

        // Default end_date to start_date if only a start_date was found
        if ($result['date'] && !$result['end_date']) {
            $result['end_date'] = $result['date'];
        }

        // Enhanced time extraction to support formats like "7:00 am", "7:00am", and "7am"
        $timePatterns = [
            // Standard format with colon: 7:00 AM
            '/(\d{1,2}):(\d{2})(?:\s*(AM|PM|am|pm))?/',
            // Format without colon: 7am, 10pm
            '/(\d{1,2})(am|pm|AM|PM)/',
            // Format with time range: 7:00-9:00 AM, 7-9 PM
            '/(\d{1,2})(?::(\d{2}))?(?:\s*-\s*|\s+to\s+)(\d{1,2})(?::(\d{2}))?(?:\s*(AM|PM|am|pm))/',
            // Format for 24-hour time: 13:00, 14:30
            '/(\d{2}):(\d{2})(?:\s*hrs|\s*hours)?/'
        ];
        
        $foundTimes = [];
        
        // Loop through each pattern to find times
        foreach ($timePatterns as $patternIndex => $pattern) {
            preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                // Standard time format (7:00 AM)
                if ($patternIndex === 0 && count($match) >= 3) {
                    $hour = $match[1];
                    $minute = $match[2];
                    $ampm = isset($match[3]) ? $match[3] : '';
                    
                    // Convert to 24-hour format if AM/PM is provided
                    if (strtolower($ampm) === 'pm' && $hour < 12) {
                        $hour += 12;
                    } elseif (strtolower($ampm) === 'am' && $hour == 12) {
                        $hour = 0;
                    }
                    
                    $foundTimes[] = sprintf('%02d:%02d', $hour, $minute);
                }
                // Format without colon (7am)
                else if ($patternIndex === 1 && count($match) >= 3) {
                    $hour = $match[1];
                    $ampm = $match[2];
                    
                    // Convert to 24-hour format
                    if (strtolower($ampm) === 'pm' && $hour < 12) {
                        $hour += 12;
                    } elseif (strtolower($ampm) === 'am' && $hour == 12) {
                        $hour = 0;
                    }
                    
                    $foundTimes[] = sprintf('%02d:00', $hour);
                }
                // Time range format (7:00-9:00 AM)
                else if ($patternIndex === 2 && count($match) >= 6) {
                    $startHour = $match[1];
                    $startMinute = isset($match[2]) && $match[2] ? $match[2] : '00';
                    $endHour = $match[3];
                    $endMinute = isset($match[4]) && $match[4] ? $match[4] : '00';
                    $ampm = isset($match[5]) ? $match[5] : '';
                    
                    // Convert to 24-hour format if AM/PM is provided
                    if (strtolower($ampm) === 'pm') {
                        if ($startHour < 12) $startHour += 12;
                        if ($endHour < 12) $endHour += 12;
                    } elseif (strtolower($ampm) === 'am') {
                        if ($startHour == 12) $startHour = 0;
                        if ($endHour == 12) $endHour = 0;
                    }
                    
                    $foundTimes[] = sprintf('%02d:%02d', $startHour, $startMinute);
                    $foundTimes[] = sprintf('%02d:%02d', $endHour, $endMinute);
                }
                // 24-hour time format (13:00)
                else if ($patternIndex === 3 && count($match) >= 3) {
                    $hour = $match[1];
                    $minute = $match[2];
                    $foundTimes[] = sprintf('%02d:%02d', $hour, $minute);
                }
            }
        }
        
        // Sort found times chronologically
        sort($foundTimes);
        
        // Assign times
        if (count($foundTimes) >= 1) {
            $result['start_time'] = $foundTimes[0];
            
            // If we found a second time, use it as end time
            if (count($foundTimes) >= 2) {
                $result['end_time'] = $foundTimes[1];
            }
        }

        // Extract title (considering exclusion list)
        $titleCandidates = [];
        
        // Extract title candidates (assuming it might be preceded by "Event:" or similar)
        $titlePatterns = [
            '/Event[:\s]+([^\n.]+)/',
            '/Title[:\s]+([^\n.]+)/',
            '/Meeting[:\s]+([^\n.]+)/',
            '/Appointment[:\s]+([^\n.]+)/',
            '/Workshop[:\s]+([^\n.]+)/',
            '/Seminar[:\s]+([^\n.]+)/',
            '/Conference[:\s]+([^\n.]+)/',
            '/Subject[:\s]+([^\n.]+)/',
            '/Re[:\s]+([^\n.]+)/',
        ];
        
        foreach ($titlePatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $titleCandidates[] = trim($matches[1]);
            }
        }
        
        // Also try the first line as a candidate
        $lines = preg_split('/\r\n|\r|\n/', $text);
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line) && strlen($line) > 5 && strlen($line) < 100) {
                $titleCandidates[] = $line;
                // Only consider the first few non-empty lines
                if (count($titleCandidates) >= 5) {
                    break;
                }
            }
        }
        
        // Process each candidate, filtering out excluded phrases
        foreach ($titleCandidates as $candidate) {
            $isValid = true;
            
            // Check if the candidate contains any excluded phrases
            foreach ($excludedPhrases as $phrase) {
                if (stripos($candidate, $phrase) !== false) {
                    $isValid = false;
                    break;
                }
            }
            
            // If it's valid, use it as the title
            if ($isValid) {
                $result['title'] = $candidate;
                break;
            }
        }
        
        // If still no title, use the first line or sentence that doesn't contain excluded phrases
        if (!$result['title']) {
            $lines = explode("\n", $text);
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $isValid = true;
                    foreach ($excludedPhrases as $phrase) {
                        if (stripos($line, $phrase) !== false) {
                            $isValid = false;
                            break;
                        }
                    }
                    
                    if ($isValid) {
                        $result['title'] = $line;
                        break;
                    }
                }
            }
            
            // If still no title, use the first sentence as a last resort
            if (!$result['title']) {
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

        // Extract location information
        $locationPatterns = [
            '/Location[:\s]+([^\n.]+)/',
            '/Venue[:\s]+([^\n.]+)/',
            '/Place[:\s]+([^\n.]+)/',
            '/Address[:\s]+([^\n.]+)/',
            '/at\s+([A-Z][^\n.,]+)/',  // "at Location Name"
            '/in\s+([A-Z][^\n.,]+)/',  // "in Location Name"
            '/held\s+(?:at|in)\s+([A-Z][^\n.,]+)/', // "held at/in Location"
        ];
        
        foreach ($locationPatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $result['location'] = trim($matches[1]);
                break;
            }
        }

        // Extract organization information
        $organizationPatterns = [
            // Direct organization patterns
            '/Organization[:\s]+([^\n.]+)/',
            '/Organizer[:\s]+([^\n.]+)/',
            '/Organized\s+by[:\s]*([^\n.]+)/',
            '/Sponsored\s+by[:\s]*([^\n.]+)/',
            '/Hosted\s+by[:\s]*([^\n.]+)/',
            '/Presented\s+by[:\s]*([^\n.]+)/',
            '/From[:\s]+([^\n.]+)/',
            // University and department patterns
            '/([A-Z][a-z]+\s+(?:State\s+)?(?:Polytechnic\s+)?University(?:\s+[-–—]\s*[A-Za-z\s]+)?(?:\s+Campus)?)/',
            '/([A-Z][a-z]+\s+(?:College|University)(?:\s+of\s+[A-Za-z\s]+)?)/',
            '/(College\s+of\s+[A-Za-z\s]+)/',
            '/(Department\s+of\s+[A-Za-z\s]+)/',
            '/([A-Z][A-Z]+(?:\s+[-–—]\s*[A-Z][A-Za-z\s]+)?)/', // Acronyms like LSPU - SPCC
            // Office patterns
            '/(Office\s+of\s+(?:the\s+)?[A-Za-z\s]+)/',
            '/((?:Student\s+)?(?:Government|Council|Organization)(?:\s+[A-Za-z\s]+)?)/',
            // Commission patterns  
            '/((?:Local\s+)?Commission\s+on\s+[A-Za-z\s]+)/',
            '/(Student\s+(?:Organization|Council|Government)(?:\s+[A-Za-z\s]+)?)/',
        ];
        
        $organizationCandidates = [];
        
        foreach ($organizationPatterns as $pattern) {
            preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                if (isset($match[1])) {
                    $candidate = trim($match[1]);
                    // Clean up common artifacts
                    $candidate = preg_replace('/[,;.!]+$/', '', $candidate);
                    $candidate = trim($candidate);
                    
                    // Skip if too short or contains excluded phrases
                    if (strlen($candidate) > 5 && strlen($candidate) <= 100) {
                        $isValidOrg = true;
                        
                        // Check against excluded phrases from title extraction
                        $excludedOrgPhrases = [
                            'To Whom It May Concern',
                            'To: All Concerned',
                            'Subject:',
                            'Re:',
                            'Attention:',
                            'Sir:',
                            'Greetings of peace',
                            'Campus Director',
                            'period to participate',
                        ];
                        
                        foreach ($excludedOrgPhrases as $excluded) {
                            if (stripos($candidate, $excluded) !== false) {
                                $isValidOrg = false;
                                break;
                            }
                        }
                        
                        if ($isValidOrg) {
                            $organizationCandidates[] = $candidate;
                        }
                    }
                }
            }
        }
        
        // Select the best organization candidate
        if (!empty($organizationCandidates)) {
            // Prefer university/college names, then departments, then offices
            $priorities = [
                'University' => 3,
                'College' => 3,
                'Polytechnic' => 3,
                'Department' => 2,
                'Office' => 2,
                'Commission' => 2,
                'Student' => 1,
            ];
            
            $bestCandidate = '';
            $bestScore = 0;
            
            foreach ($organizationCandidates as $candidate) {
                $score = 0;
                foreach ($priorities as $keyword => $weight) {
                    if (stripos($candidate, $keyword) !== false) {
                        $score += $weight;
                    }
                }
                
                // Prefer longer, more descriptive names if scores are equal
                if ($score > $bestScore || ($score === $bestScore && strlen($candidate) > strlen($bestCandidate))) {
                    $bestScore = $score;
                    $bestCandidate = $candidate;
                }
            }
            
            $result['organization'] = $bestCandidate ?: $organizationCandidates[0];
        }

        // Generate a concise description of the event
        $result['description'] = $this->generateConciseDescription($result);

        return $result;
    }

    /**
     * Generate a concise description of the event using extracted information
     */
    private function generateConciseDescription($eventData)
    {
        // Format date for display
        $formattedStartDate = null;
        if ($eventData['date']) {
            $startDate = Carbon::parse($eventData['date']);
            $formattedStartDate = $startDate->format('F j, Y');
        }
        
        $formattedEndDate = null;
        if ($eventData['end_date'] && $eventData['end_date'] !== $eventData['date']) {
            $endDate = Carbon::parse($eventData['end_date']);
            $formattedEndDate = $endDate->format('F j, Y');
        }
        
        // Format times for display
        $timeInfo = '';
        if ($eventData['start_time']) {
            $startTime = Carbon::parse($eventData['start_time']);
            $timeInfo = ' at ' . $startTime->format('g:i A');
            
            if ($eventData['end_time']) {
                $endTime = Carbon::parse($eventData['end_time']);
                $timeInfo .= ' to ' . $endTime->format('g:i A');
            }
        }
        
        // Build the description
        $description = '';
        
        // First sentence: Event with date and time
        if ($eventData['title']) {
            $description = $eventData['title'];
            
            if ($formattedStartDate) {
                $description .= " scheduled for $formattedStartDate";
                
                if ($formattedEndDate) {
                    $description .= " through $formattedEndDate";
                }
                
                $description .= $timeInfo;
            }
            
            $description .= '.';
        }
        
        // Second sentence: Location if available
        if ($eventData['location']) {
            $description .= " This event will be held at {$eventData['location']}.";
        }
        
        return trim($description);
    }

    /**
     * Helper method to format extracted date matches into YYYY-MM-DD
     */
    private function formatExtractedDate($matches)
    {
        if (count($matches) >= 4) {
            // Check for full month names (January, February, etc.)
            if (preg_match('/January|February|March|April|May|June|July|August|September|October|November|December/', $matches[1])) {
                // Month DD, YYYY format
                $month = date('m', strtotime($matches[1]));
                $day = $matches[2];
                $year = $matches[3];
                return "$year-$month-$day";
            } 
            // Check for abbreviated month names (Jan, Feb, etc.)
            elseif (preg_match('/Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec/', $matches[1])) {
                // Mon DD, YYYY format
                $month = date('m', strtotime($matches[1]));
                $day = $matches[2];
                $year = $matches[3];
                return "$year-$month-$day";
            }
            // Check for full month names in second position
            elseif (preg_match('/January|February|March|April|May|June|July|August|September|October|November|December/', $matches[2])) {
                // DD Month YYYY format
                $day = $matches[1];
                $month = date('m', strtotime($matches[2]));
                $year = $matches[3];
                return "$year-$month-$day";
            } 
            // Check for abbreviated month names in second position
            elseif (preg_match('/Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec/', $matches[2])) {
                // DD Mon YYYY format
                $day = $matches[1];
                $month = date('m', strtotime($matches[2]));
                $year = $matches[3];
                return "$year-$month-$day";
            }
            // YYYY-MM-DD format
            elseif (strlen($matches[1]) == 4) {
                $year = $matches[1];
                $month = $matches[2];
                $day = $matches[3];
                return "$year-$month-$day";
            } 
            // MM/DD/YYYY format (default assumption for numeric dates)
            else {
                $month = $matches[1];
                $day = $matches[2];
                $year = $matches[3];
                return "$year-$month-$day";
            }
        }
        return null;
    }
} 