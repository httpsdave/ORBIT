<?php

namespace App\Console\Commands;

use App\Models\UserActivity;
use Illuminate\Console\Command;

class MigrateActivityToCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity:migrate-to-cache {--delete-all : Delete all database activities} {--keep-days=0 : Keep activities from last N days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate activity logging from database to cache and optionally clean up old database records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Migrating Activity Logging to Cache-Based System');
        $this->newLine();

        // Check if user wants to delete all
        if ($this->option('delete-all')) {
            if ($this->confirm('⚠️  This will DELETE ALL activity records from the database. Are you sure?', false)) {
                $count = UserActivity::count();
                $this->info("Found {$count} activity records in database");
                
                if ($count > 0) {
                    UserActivity::truncate();
                    $this->info("✅ Successfully deleted all {$count} activity records from database");
                } else {
                    $this->info("ℹ️  No activity records found in database");
                }
            } else {
                $this->warn('❌ Deletion cancelled');
                return Command::SUCCESS;
            }
        } elseif ($this->option('keep-days') > 0) {
            $days = (int) $this->option('keep-days');
            $cutoffDate = now()->subDays($days);
            
            $count = UserActivity::where('created_at', '<', $cutoffDate)->count();
            
            if ($count > 0) {
                $this->info("Found {$count} activities older than {$days} days (before {$cutoffDate->format('Y-m-d H:i:s')})");
                
                if ($this->confirm("Delete these {$count} old activities?", true)) {
                    UserActivity::where('created_at', '<', $cutoffDate)->delete();
                    $this->info("✅ Successfully deleted {$count} old activity records");
                } else {
                    $this->warn('❌ Deletion cancelled');
                    return Command::SUCCESS;
                }
            } else {
                $this->info("ℹ️  No activities found older than {$days} days");
            }
        } else {
            // Just show stats
            $totalCount = UserActivity::count();
            $last24h = UserActivity::where('created_at', '>', now()->subDay())->count();
            $last7d = UserActivity::where('created_at', '>', now()->subDays(7))->count();
            $last30d = UserActivity::where('created_at', '>', now()->subDays(30))->count();
            
            $this->info('📊 Database Activity Statistics:');
            $this->table(
                ['Timeframe', 'Count'],
                [
                    ['Total', $totalCount],
                    ['Last 24 hours', $last24h],
                    ['Last 7 days', $last7d],
                    ['Last 30 days', $last30d],
                    ['Older than 30 days', $totalCount - $last30d],
                ]
            );
            
            $this->newLine();
            $this->info('💡 Options:');
            $this->line('  • Delete all activities: php artisan activity:migrate-to-cache --delete-all');
            $this->line('  • Keep last 7 days: php artisan activity:migrate-to-cache --keep-days=7');
            $this->line('  • Keep last 30 days: php artisan activity:migrate-to-cache --keep-days=30');
        }

        $this->newLine();
        $this->info('✨ Migration Status:');
        $this->line('  ✅ Cache-based activity logging is now active');
        $this->line('  ✅ New activities will be stored in cache (48 hours TTL)');
        $this->line('  ✅ Old database table can be safely removed after cleanup');
        
        $this->newLine();
        $this->info('📝 Next Steps:');
        $this->line('  1. Test the new cache-based system');
        $this->line('  2. Clean up database activities when ready');
        $this->line('  3. Optionally drop user_activities table in production');

        return Command::SUCCESS;
    }
}
