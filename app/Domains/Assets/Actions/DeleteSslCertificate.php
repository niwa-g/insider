<?php

namespace App\Domains\Assets\Actions;

use App\Domains\Assets\Models\SslCertificate;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class DeleteSslCertificate
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(SslCertificate $model): void
    {
        DB::transaction(function () use ($model) {
            $snapshot = $model->toArray();
            $model->delete();
            $this->auditLogger->log('ssl_certificate.deleted', $model, $snapshot, null);
            ActivityHelper::log('SslCertificate deleted', $model);
        });
    }
}
