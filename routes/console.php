<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
*/

// Clean up expired OTPs from cache (runs every hour)
Schedule::command('cache:prune-stale-tags')->hourly();

// Process pending withdrawals
Schedule::command('payments:process-withdrawals')->everyFiveMinutes();

// Update transaction statuses from payment providers
Schedule::command('payments:sync-status')->everyTenMinutes();

// Clean up abandoned rides (no driver after 15 minutes)
Schedule::command('rides:cleanup-abandoned')->everyFiveMinutes();

// Generate daily reports
Schedule::command('reports:daily')->dailyAt('23:55');

// Clean up old notifications
Schedule::command('notifications:cleanup --days=30')->daily();
