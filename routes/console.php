<?php

use App\Domains\Automation\Jobs\RunDueAutomationSchedulesJob;
use App\Domains\Automation\Jobs\ScanRenewalsJob;
use App\Domains\Automation\Jobs\ScanSlaBreachesJob;
use App\Domains\Automation\Jobs\SyncIntegrationsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new RunDueAutomationSchedulesJob)->everyFifteenMinutes();
Schedule::job(new ScanRenewalsJob)->dailyAt('08:00');
Schedule::job(new ScanSlaBreachesJob)->hourly();
Schedule::job(new SyncIntegrationsJob)->hourly();
