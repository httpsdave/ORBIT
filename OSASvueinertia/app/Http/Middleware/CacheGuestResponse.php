<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheGuestResponse
{
    /**
     * Add short-term caching headers for guest GET responses to improve TTFB/LCP.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        $contentType = (string) $response->headers->get('Content-Type');

        if (
            $request->isMethod('GET') &&
            !$request->user() &&
            $response->isSuccessful() &&
            str_contains($contentType, 'text/html')
        ) {
            $cacheControl = [
                'public',
                'max-age=120',
                's-maxage=600',
                'stale-while-revalidate=300',
            ];

            $response->headers->set('Cache-Control', implode(', ', $cacheControl));

            $varyValues = array_filter(array_map('trim', explode(',', (string) $response->headers->get('Vary'))));
            $varyValues[] = 'Cookie';
            $response->headers->set('Vary', implode(', ', array_unique($varyValues)));
        }

        return $response;
    }
}
