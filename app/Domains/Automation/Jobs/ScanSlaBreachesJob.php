<?php

namespace App\Domains\Automation\Jobs;

use App\Domains\Automation\Services\SlaScanService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ScanSlaBreachesJob implements ShouldQueue
{
    use Queueable;

    public function handle(SlaScanService $service): void
    {
        $service->scanAll();
    }
}
