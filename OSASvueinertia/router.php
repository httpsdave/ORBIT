<?php

/**
 * PHP Router for adding cache headers to static assets
 * Used with php -S for development/Railway deployment
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve static files directly with cache headers
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    $path = __DIR__ . '/public' . $uri;
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    
    // Define cache durations (in seconds)
    $cachePatterns = [
        // 1 year cache with immutable
        'long' => [
            'extensions' => ['js', 'css', 'woff', 'woff2', 'ttf', 'otf', 'eot'],
            'max_age' => 31536000,
            'immutable' => true
        ],
        // 6 months cache for images
        'medium' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico'],
            'max_age' => 31536000,
            'immutable' => true
        ]
    ];
    
    // Apply cache headers based on file type
    foreach ($cachePatterns as $pattern) {
        if (in_array($extension, $pattern['extensions'])) {
            header('Cache-Control: public, max-age=' . $pattern['max_age'] . ($pattern['immutable'] ? ', immutable' : ''));
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $pattern['max_age']) . ' GMT');
            header('X-Content-Type-Options: nosniff');
            
            // Add CORS headers for fonts
            if (in_array($extension, ['woff', 'woff2', 'ttf', 'otf', 'eot'])) {
                header('Access-Control-Allow-Origin: *');
            }
            
            break;
        }
    }
    
    // Return the file
    return false;
}

// Forward all other requests to Laravel's front controller
chdir(__DIR__ . '/public');
require_once __DIR__ . '/public/index.php';
