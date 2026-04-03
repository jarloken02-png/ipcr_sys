<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

$backupSchedulerEnabled = filter_var(env('SYSTEM_BACKUP_SCHEDULER_ENABLED', false), FILTER_VALIDATE_BOOL);

if ($backupSchedulerEnabled) {
    Schedule::command('backup:run-system')->everyMinute();
}
