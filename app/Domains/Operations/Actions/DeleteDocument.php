<?php

namespace App\Domains\Operations\Actions;

use App\Domains\Operations\Models\Document;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteDocument
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(Document $model): void
    {
        DB::transaction(function () use ($model) {
            $snapshot = $model->toArray();
            if ($model->path && Storage::disk($model->disk)->exists($model->path)) {
                Storage::disk($model->disk)->delete($model->path);
            }
            $model->delete();
            $this->auditLogger->log('document.deleted', $model, $snapshot, null);
            ActivityHelper::log('Document deleted', $model);
        });
    }
}
