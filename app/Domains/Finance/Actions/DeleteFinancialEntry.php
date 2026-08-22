<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Enums\FinancialEntryStatus;
use App\Domains\Finance\Models\FinancialEntry;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DeleteFinancialEntry
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(FinancialEntry $entry): void
    {
        if ($entry->status !== FinancialEntryStatus::Draft) {
            throw new InvalidArgumentException('Only draft entries can be deleted.');
        }

        DB::transaction(function () use ($entry) {
            $snapshot = $entry->toArray();
            $entry->delete();
            $this->auditLogger->log('financial_entry.deleted', $entry, $snapshot, null);
            ActivityHelper::log('Financial entry deleted', $entry);
        });
    }
}
