<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Enums\FinancialEntryStatus;
use App\Domains\Finance\Models\FinancialEntry;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PostFinancialEntry
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(FinancialEntry $entry): FinancialEntry
    {
        if ($entry->status !== FinancialEntryStatus::Draft) {
            throw new InvalidArgumentException('Only draft entries can be posted.');
        }

        return DB::transaction(function () use ($entry) {
            $old = $entry->toArray();
            $entry->update(['status' => FinancialEntryStatus::Posted]);
            $fresh = $entry->fresh();
            $this->auditLogger->log('financial_entry.posted', $entry, $old, $fresh?->toArray());
            ActivityHelper::log('Financial entry posted', $entry);

            return $fresh ?? $entry;
        });
    }
}
