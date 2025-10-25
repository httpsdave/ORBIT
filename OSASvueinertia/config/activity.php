<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Activity Log Retention (Database - Legacy)
    |--------------------------------------------------------------------------
    |
    | This value determines how many days activity logs should be retained
    | in the database before being automatically pruned.
    | NOTE: This is for legacy database-based activity logs only.
    |
    */

    'retention_days' => env('ACTIVITY_RETENTION_DAYS', 5),

    /*
    |--------------------------------------------------------------------------
    | Activity Log Cache TTL
    |--------------------------------------------------------------------------
    |
    | Time-to-live for cached activity logs in seconds.
    | Default: 48 hours (2 days)
    | This is the recommended approach for short-lived activity tracking.
    |
    */

    'cache_ttl' => env('ACTIVITY_CACHE_TTL', 48 * 60 * 60), // 48 hours

    /*
    |--------------------------------------------------------------------------
    | Cleanup Schedule (Database - Legacy)
    |--------------------------------------------------------------------------
    |
    | Determines if automatic cleanup should be enabled via scheduled tasks
    | for database-based activity logs.
    |
    */

    'auto_cleanup' => env('ACTIVITY_AUTO_CLEANUP', true),
];