<?php

namespace App\Domains\Assets\Actions;

use App\Domains\Assets\Models\DnsRecord;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class DeleteDnsRecord
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(DnsRecord $model): void
    {
        DB::transaction(function () use ($model) {
            $snapshot = $model->toArray();
            $model->delete();
            $this->auditLogger->log('dns_record.deleted', $model, $snapshot, null);
            ActivityHelper::log('DnsRecord deleted', $model);
        });
    }
}
