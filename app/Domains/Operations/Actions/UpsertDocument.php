<?php

namespace App\Domains\Operations\Actions;

use App\Domains\Operations\Models\Document;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UpsertDocument
{
    public function __construct(private AuditLogger $auditLogger) {}

    /** @param  array<string, mixed>  $data */
    public function handle(?Document $model, array $data): Document
    {
        return DB::transaction(function () use ($model, $data) {
            if ($model === null) {
                $model = Document::query()->create($data);
                $this->auditLogger->log('document.created', $model, null, $model->toArray());
                ActivityHelper::log('Document created', $model);
            } else {
                $old = $model->toArray();
                $model->update($data);
                $this->auditLogger->log('document.updated', $model, $old, $model->fresh()->toArray());
                ActivityHelper::log('Document updated', $model);
            }

            return $model->fresh();
        });
    }
}
