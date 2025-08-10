<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers = 
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    /**
     * Handle Railway's proxy setup and clear corrupted cookies
     */
    public function handle($request, \Closure $next)
    {
        // Set Railway-specific server variables for proper HTTPS detection
        if ($request->header('X-Forwarded-Proto') === 'https') {
            $request->server->set('HTTPS', 'on');
            $request->server->set('SERVER_PORT', 443);
        }

        $response = parent::handle($request, $next);

        // Clear corrupted session cookies that cause 419 errors
        // This happens when browsers cache old cookies from misconfigured deployments
        if ($response->getStatusCode() === 419 || 
            $request->hasCookie('laravel_session') && !$request->session()->isStarted()) {
            
            // Force expire old session cookies
            $response->withCookie(cookie('laravel_session', '', -1, '/', config('session.domain'), true, true));
            $response->withCookie(cookie('orbit_session', '', -1, '/', config('session.domain'), true, true));
            $response->withCookie(cookie('XSRF-TOKEN', '', -1, '/', config('session.domain'), false, false));
        }

        return $response;
    }
}
