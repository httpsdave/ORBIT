<?php

namespace App\Console\Commands;

use App\Models\UserActivity;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PruneActivityLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity:prune {--days= : Number of days to retain (defaults to config value)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune activity logs older than the specified number of days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $retentionDays = $this->option('days') ?? config('activity.retention_days', 5);
        
        $cutoffDate = Carbon::now()->subDays($retentionDays);
        
        $this->info("Pruning activity logs older than {$retentionDays} days (before {$cutoffDate->format('Y-m-d H:i:s')})...");
        
        $deletedCount = UserActivity::where('created_at', '<', $cutoffDate)->delete();
        
        if ($deletedCount > 0) {
            $this->info("✅ Successfully deleted {$deletedCount} old activity logs.");
        } else {
            $this->info("ℹ️  No old activity logs found to delete.");
        }
        
        return Command::SUCCESS;
    }
}
