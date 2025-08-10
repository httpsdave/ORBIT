<?php

namespace App\Providers;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use App\Models\Role; // Add this import

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        
         // Explicitly register the Role model
         $this->app->bind('role', function ($app) {
            return new Role();
        });
        
        // Railway SSL detection and forcing
        $this->handleRailwaySSL();
    }
    
    /**
     * Handle Railway's SSL termination properly
     */
    private function handleRailwaySSL(): void
    {
        // Force HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
        
        // Railway-specific SSL detection
        if (request()) {
            $request = request();
            
            // Check various SSL indicators from Railway
            $isSSL = $request->header('X-Forwarded-Proto') === 'https' ||
                     $request->header('X-Forwarded-SSL') === 'on' ||
                     $request->header('X-Forwarded-Port') === '443' ||
                     $request->server('HTTPS') === 'on';
                     
            if ($isSSL) {
                URL::forceScheme('https');
                
                // Set server variables for proper SSL detection
                $_SERVER['HTTPS'] = 'on';
                $_SERVER['SERVER_PORT'] = 443;
                
                // Update request server bag
                $request->server->set('HTTPS', 'on');
                $request->server->set('SERVER_PORT', 443);
            }
        }
    }
}
