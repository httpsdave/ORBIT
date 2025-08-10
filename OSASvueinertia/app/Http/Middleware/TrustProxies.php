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
    protected $proxies = '*'; // Trust all proxies for Railway

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
     * Handle Railway SSL termination
     */
    public function handle($request, \Closure $next)
    {
        // Railway-specific SSL handling
        if ($request->header('X-Forwarded-Proto') === 'https' || 
            $request->header('X-Forwarded-SSL') === 'on' ||
            $request->server('HTTPS') === 'on') {
            $request->server->set('HTTPS', 'on');
            $request->server->set('SERVER_PORT', 443);
        }
        
        // Force HTTPS scheme for Railway
        if (app()->environment('production')) {
            \URL::forceScheme('https');
        }
        
        return parent::handle($request, $next);
    }
}
