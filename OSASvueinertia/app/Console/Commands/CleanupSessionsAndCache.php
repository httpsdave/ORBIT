<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupSessionsAndCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:sessions-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired sessions and cache entries to save memory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of expired sessions and cache...');
        
        // Clean up expired sessions
        $this->info('Cleaning expired sessions...');
        $deletedSessions = DB::table('sessions')
            ->where('last_activity', '<', now()->subHours(24)->timestamp)
            ->delete();
        $this->info("Deleted {$deletedSessions} expired sessions");
        
        // Clean up expired cache entries
        $this->info('Cleaning expired cache entries...');
        $deletedCache = DB::table('cache')
            ->where('expiration', '<', now()->timestamp)
            ->delete();
        $this->info("Deleted {$deletedCache} expired cache entries");
        
        // Clean up old cache locks
        if (DB::getSchemaBuilder()->hasTable('cache_locks')) {
            $deletedLocks = DB::table('cache_locks')
                ->where('expiration', '<', now()->timestamp)
                ->delete();
            $this->info("Deleted {$deletedLocks} expired cache locks");
        }
        
        $this->info('Cleanup completed successfully!');
        
        return Command::SUCCESS;
    }
}
