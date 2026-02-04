# Railway Cron Jobs Configuration

Since we removed `php artisan schedule:work` to save memory (50-100MB), you need to configure Railway cron jobs to run scheduled tasks.

## How to Set Up Railway Cron Jobs

1. Go to your Railway project dashboard
2. Click on your service
3. Go to "Settings" > "Cron"
4. Add the following cron jobs:

### Recommended Cron Jobs

#### 1. Laravel Scheduled Tasks (Every Minute)
**Schedule:** `* * * * *`
**Command:** `php artisan schedule:run`
**Description:** Runs all Laravel scheduled tasks

#### 2. Session Cleanup (Daily at 2 AM)
**Schedule:** `0 2 * * *`
**Command:** `php artisan session:gc`
**Description:** Clean up expired sessions to free database space

#### 3. Cache Cleanup (Daily at 3 AM)
**Schedule:** `0 3 * * *`
**Command:** `php artisan cache:prune-stale-tags`
**Description:** Remove stale cache entries

#### 4. Queue Work (If using queues)
**Important:** Only if you're using queues and not running a persistent queue worker
**Schedule:** `*/5 * * * *` (every 5 minutes)
**Command:** `php artisan queue:work --stop-when-empty --max-time=240`
**Description:** Process queued jobs

## Alternative: Use a Third-Party Cron Service

If Railway doesn't support cron jobs in your plan, you can use:

1. **EasyCron** (https://www.easycron.com/) - Free tier available
2. **cron-job.org** (https://cron-job.org/) - Free
3. **Uptime Robot** (https://uptimerobot.com/) - Monitor + HTTP cron

### HTTP Cron Endpoint Setup

Add this route to `routes/console.php` or `routes/web.php`:

```php
// In routes/console.php
Route::get('/cron/run/{token}', function ($token) {
    if ($token !== config('app.cron_token')) {
        abort(403);
    }
    
    Artisan::call('schedule:run');
    
    return response()->json([
        'status' => 'success',
        'time' => now()->toDateTimeString()
    ]);
});
```

Then set `CRON_TOKEN` in your Railway environment variables and call:
`https://your-app.railway.app/cron/run/{your-secret-token}` every minute

## What Tasks Are Scheduled?

Check `app/Console/Kernel.php` to see what tasks are scheduled. Common tasks:
- Backup operations
- Database cleanup
- Cache warming
- Report generation
- Notification sending

## Memory Savings

Removing `schedule:work` saves **50-100MB** of constant memory usage, reducing your Railway bill by approximately **$4.50-5.00/month**.
