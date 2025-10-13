<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\EventExtractionService;
use App\Services\GeminiEventExtractionService;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the base EventExtractionService
        $this->app->singleton(EventExtractionService::class, function ($app) {
            return new EventExtractionService();
        });

        // Register GeminiEventExtractionService with its dependency
        $this->app->singleton(GeminiEventExtractionService::class, function ($app) {
            return new GeminiEventExtractionService(
                $app->make(EventExtractionService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
