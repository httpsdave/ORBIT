<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule activity log pruning to run daily at 2:00 AM
Schedule::command('activity:prune')->daily()->at('02:00');

// Schedule database backups to run daily at 3:00 AM
Schedule::command('backup:clean')->daily()->at('03:00');
Schedule::command('backup:run')->daily()->at('03:30');

