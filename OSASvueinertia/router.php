<?php

/**
 * PHP Router for adding cache headers to static assets
 * Used with php -S for development/Railway deployment
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

$compressibleTypes = [
    'css' => 'text/css',
    'js' => 'application/javascript',
    'json' => 'application/json',
    'xml' => 'application/xml',
    'txt' => 'text/plain',
    'html' => 'text/html',
    'svg' => 'image/svg+xml',
    'map' => 'application/json'
];

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

    $lastModified = filemtime($path);
    if ($lastModified !== false) {
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');

        if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $ifModifiedSince = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
            if ($ifModifiedSince !== false && $ifModifiedSince >= $lastModified) {
                header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
                exit;
            }
        }
    }

    $acceptEncoding = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';
    $supportsGzip = stripos($acceptEncoding, 'gzip') !== false;

    if ($supportsGzip && isset($compressibleTypes[$extension])) {
        $contents = file_get_contents($path);

        if ($contents !== false) {
            $compressed = gzencode($contents, 6);

            if ($compressed !== false) {
                header('Content-Encoding: gzip');
                header('Vary: Accept-Encoding');
                header('Content-Type: ' . $compressibleTypes[$extension]);
                header('Content-Length: ' . strlen($compressed));
                echo $compressed;
                exit;
            }
        }
    }

    $mime = mime_content_type($path) ?: 'application/octet-stream';
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($path));
    if (isset($compressibleTypes[$extension])) {
        header('Vary: Accept-Encoding');
    }
    readfile($path);
    exit;
}

// Forward all other requests to Laravel's front controller
chdir(__DIR__ . '/public');
require_once __DIR__ . '/public/index.php';
