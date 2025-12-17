<?php

// app/Http/Middleware/CheckRole.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user()) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to access this area');
        }

        // Super admins have access to everything
        if ($request->user()->isSuperAdmin()) {
            return $next($request);
        }

        // Check if user has the required role
        if (!$request->user()->hasRole($role)) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to access this area');
        }

        return $next($request);
    }
}
