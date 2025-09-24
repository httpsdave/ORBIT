<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        //
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
        // Prune activity logs daily at 2:00 AM if auto cleanup is enabled
        if (config('activity.auto_cleanup', true)) {
            $schedule->command('activity:prune')
                ->dailyAt('02:00')
                ->description('Prune old activity logs')
                ->onFailure(function () {
                    // Log failure if needed
                    \Log::error('Activity log pruning failed');
                });
        }
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
