<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        // Force clear any corrupted session data to fix 419 CSRF errors
        if (request()->session()->isStarted()) {
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }
        
        return Inertia::render('Auth/Logz', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Clear any existing session before authentication to fix 419 CSRF errors
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        $request->authenticate();

        $user = Auth::user();

        // Check if user has 2FA enabled
        if ($user->two_factor_enabled && $user->two_factor_secret) {
            // Don't log the user in yet, store their ID in session
            Auth::logout();
            
            $request->session()->put('2fa:user:id', $user->id);
            $request->session()->put('2fa:remember', $request->boolean('remember'));
            
            return redirect()->route('two-factor.login');
        }

        $request->session()->regenerate();

        // Redirect based on user role
        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}