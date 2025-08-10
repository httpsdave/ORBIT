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
        
        // Force HTTPS in production environments
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
        
        // Handle Railway's proxy headers for HTTPS detection
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            URL::forceScheme('https');
        }
        
        // Additional Railway proxy handling
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            URL::forceScheme('https');
        }
    }
}
