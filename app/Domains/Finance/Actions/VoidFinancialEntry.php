<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Enums\FinancialEntryStatus;
use App\Domains\Finance\Models\FinancialEntry;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class VoidFinancialEntry
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(FinancialEntry $entry): FinancialEntry
    {
        if ($entry->status !== FinancialEntryStatus::Posted) {
            throw new InvalidArgumentException('Only posted entries can be voided.');
        }

        return DB::transaction(function () use ($entry) {
            $old = $entry->toArray();

            $reversing = FinancialEntry::query()->create([
                'organization_id' => $entry->organization_id,
                'client_id' => $entry->client_id,
                'project_id' => $entry->project_id,
                'client_service_id' => $entry->client_service_id,
                'entry_type' => $entry->entry_type->opposite(),
                'category' => $entry->category,
                'description' => "Reversal: {$entry->description}",
                'amount' => $entry->amount,
                'currency' => $entry->currency,
                'occurred_on' => now()->toDateString(),
                'status' => FinancialEntryStatus::Posted,
                'void_of_id' => $entry->id,
                'invoice_id' => $entry->invoice_id,
                'created_by' => Auth::id(),
                'notes' => $entry->notes,
            ]);

            $entry->update([
                'status' => FinancialEntryStatus::Void,
                'void_of_id' => $reversing->id,
            ]);

            $fresh = $entry->fresh();
            $this->auditLogger->log('financial_entry.voided', $entry, $old, $fresh?->toArray());
            ActivityHelper::log('Financial entry voided', $entry, ['reversing_id' => $reversing->id]);

            return $fresh ?? $entry;
        });
    }
}
