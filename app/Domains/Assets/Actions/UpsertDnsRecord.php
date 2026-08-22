<?php

namespace App\Domains\Assets\Actions;

use App\Domains\Assets\Models\DnsRecord;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;

use Illuminate\Support\Facades\DB;

class UpsertDnsRecord
{
    public function __construct(
        private AuditLogger $auditLogger
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(?DnsRecord $model, array $data): DnsRecord
    {
        return DB::transaction(function () use ($model, $data) {
            if ($model === null) {
                $model = DnsRecord::query()->create($data);
                $this->auditLogger->log('dns_record.created', $model, null, $model->toArray());
                ActivityHelper::log('DnsRecord created', $model);
            } else {
                $old = $model->toArray();
                $model->update($data);
                $this->auditLogger->log('dns_record.updated', $model, $old, $model->fresh()->toArray());
                ActivityHelper::log('DnsRecord updated', $model);
            }

            return $model->fresh();
        });
    }
}
