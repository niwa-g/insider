<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Enums\FinancialEntryStatus;
use App\Domains\Finance\Models\FinancialEntry;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UpsertFinancialEntry
{
    public function __construct(private AuditLogger $auditLogger) {}

    /** @param  array<string, mixed>  $data */
    public function handle(?FinancialEntry $model, array $data): FinancialEntry
    {
        if ($model !== null && $model->status !== FinancialEntryStatus::Draft) {
            throw new InvalidArgumentException('Only draft entries can be updated.');
        }

        return DB::transaction(function () use ($model, $data) {
            if ($model === null) {
                $data['status'] = FinancialEntryStatus::Draft;
                $model = FinancialEntry::query()->create($data);
                $this->auditLogger->log('financial_entry.created', $model, null, $model->toArray());
                ActivityHelper::log('Financial entry created', $model);
            } else {
                $old = $model->toArray();
                $model->update($data);
                $this->auditLogger->log('financial_entry.updated', $model, $old, $model->fresh()->toArray());
                ActivityHelper::log('Financial entry updated', $model);
            }

            return $model->fresh(['client:id,company_name', 'clientService:id,name']);
        });
    }
}
