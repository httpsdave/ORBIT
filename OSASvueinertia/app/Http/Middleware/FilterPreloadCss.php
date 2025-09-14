<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to remove CSS entries from Link preload headers.
 *
 * Some browsers warn when CSS files are preloaded but not used shortly after load.
 * To avoid noisy console warnings while keeping JS preloads, this middleware
 * strips any Link header segments that reference .css files.
 */
class FilterPreloadCss
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only filter on Response objects that have headers
        if ($response instanceof Response || method_exists($response, 'headers')) {
            $linkHeader = $response->headers->get('Link');

            if ($linkHeader) {
                // Split on commas to get individual link segments.
                // Link header format: <url>; rel=preload; as=style, <url2>; rel=preload; as=script
                $segments = array_map('trim', explode(',', $linkHeader));

                $filtered = array_filter($segments, function ($seg) {
                    // Remove segments that reference .css files
                    return stripos($seg, '.css') === false;
                });

                if (count($filtered) > 0) {
                    // Rebuild Link header
                    $response->headers->set('Link', implode(', ', $filtered));
                } else {
                    // Remove header entirely if empty
                    $response->headers->remove('Link');
                }
            }
        }

        return $response;
    }
}
