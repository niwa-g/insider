<?php

namespace App\Domains\Assets\Actions;

use App\Domains\Assets\Models\SslCertificate;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use App\Domains\Assets\Actions\SyncRenewalFromAsset;
use Illuminate\Support\Facades\DB;

class UpsertSslCertificate
{
    public function __construct(
        private AuditLogger $auditLogger,
        private SyncRenewalFromAsset $renewalSync,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(?SslCertificate $model, array $data): SslCertificate
    {
        return DB::transaction(function () use ($model, $data) {
            if ($model === null) {
                $model = SslCertificate::query()->create($data);
                $this->auditLogger->log('ssl_certificate.created', $model, null, $model->toArray());
                ActivityHelper::log('SslCertificate created', $model);
            } else {
                $old = $model->toArray();
                $model->update($data);
                $this->auditLogger->log('ssl_certificate.updated', $model, $old, $model->fresh()->toArray());
                ActivityHelper::log('SslCertificate updated', $model);
            }
            $this->renewalSync->handle($model->fresh());

            return $model->fresh();
        });
    }
}
