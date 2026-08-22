<?php

namespace App\Domains\Automation\Jobs;

use App\Domains\Automation\Services\RenewalScanService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ScanRenewalsJob implements ShouldQueue
{
    use Queueable;

    public function handle(RenewalScanService $service): void
    {
        $service->scanAll();
    }
}
