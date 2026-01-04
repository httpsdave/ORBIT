<?php

namespace App\Providers;

use App\Models\Role;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDriveService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use Masbug\Flysystem\GoogleDriveAdapter;

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

        $this->registerGoogleDriveDisk();
        
        // Railway SSL detection and forcing
        $this->handleRailwaySSL();
    }

    private function registerGoogleDriveDisk(): void
    {
        Storage::extend('google', function ($app, $config) {
            $client = new GoogleClient();
            $client->setClientId($config['clientId']);
            $client->setClientSecret($config['clientSecret']);
            $client->refreshToken($config['refreshToken']);

            $service = new GoogleDriveService($client);
            $adapter = new GoogleDriveAdapter($service, $config['folder'] ?? '/');

            return new FilesystemAdapter(
                new Filesystem($adapter, ['case_sensitive' => false]),
                $adapter,
                $config
            );
        });
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
