<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Activity Log Retention
    |--------------------------------------------------------------------------
    |
    | This value determines how many days activity logs should be retained
    | before being automatically pruned from the database.
    |
    */

    'retention_days' => env('ACTIVITY_RETENTION_DAYS', 5),

    /*
    |--------------------------------------------------------------------------
    | Cleanup Schedule
    |--------------------------------------------------------------------------
    |
    | Determines if automatic cleanup should be enabled via scheduled tasks.
    |
    */

    'auto_cleanup' => env('ACTIVITY_AUTO_CLEANUP', true),
];