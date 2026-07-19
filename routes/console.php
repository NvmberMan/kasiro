<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Permanently delete tenants archived beyond the retention window (default 30d).
Schedule::command('tenants:purge-archived')->dailyAt('03:00');
