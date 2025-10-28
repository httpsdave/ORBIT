<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddCacheHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only add cache headers for static assets
        if ($this->isStaticAsset($request)) {
            $this->addCacheHeaders($response, $request);
        }

        return $response;
    }

    /**
     * Check if the request is for a static asset
     */
    private function isStaticAsset(Request $request): bool
    {
        $path = $request->path();
        
        // Check for build assets (Vite)
        if (str_starts_with($path, 'build/')) {
            return true;
        }
        
        // Check for image files
        if (preg_match('/\.(jpg|jpeg|png|gif|webp|svg|ico)$/i', $path)) {
            return true;
        }
        
        // Check for font files
        if (preg_match('/\.(woff|woff2|ttf|otf|eot)$/i', $path)) {
            return true;
        }
        
        return false;
    }

    /**
     * Add appropriate cache headers based on asset type
     */
    private function addCacheHeaders(Response $response, Request $request): void
    {
        $path = $request->path();
        
        // Build assets (Vite) - 1 year cache with immutable
        if (str_starts_with($path, 'build/')) {
            $response->header('Cache-Control', 'public, max-age=31536000, immutable');
            $response->header('Expires', gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        }
        // Images - 1 year cache with immutable
        elseif (preg_match('/\.(jpg|jpeg|png|gif|webp|svg|ico)$/i', $path)) {
            $response->header('Cache-Control', 'public, max-age=31536000, immutable');
            $response->header('Expires', gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        }
        // Fonts - 1 year cache with immutable
        elseif (preg_match('/\.(woff|woff2|ttf|otf|eot)$/i', $path)) {
            $response->header('Cache-Control', 'public, max-age=31536000, immutable');
            $response->header('Expires', gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
            $response->header('Access-Control-Allow-Origin', '*');
        }
        
        // Add security headers for all static assets
        $response->header('X-Content-Type-Options', 'nosniff');
    }
}
