<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Enums\ExpenseClaimStatus;
use App\Domains\Finance\Enums\FinancialEntryStatus;
use App\Domains\Finance\Enums\FinancialEntryType;
use App\Domains\Finance\Models\ExpenseClaim;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PayExpenseClaim
{
    public function __construct(private AuditLogger $auditLogger) {}

    /** @param  array{method: string, paid_on: string}  $data */
    public function handle(ExpenseClaim $claim, array $data): ExpenseClaim
    {
        if ($claim->status !== ExpenseClaimStatus::Approved) {
            throw new InvalidArgumentException('Only approved claims can be paid.');
        }

        return DB::transaction(function () use ($claim, $data) {
            $category = \App\Domains\Finance\Enums\ExpenseCategory::from($claim->getRawOriginal('category'));

            $entry = \App\Domains\Finance\Models\FinancialEntry::query()->create([
                'organization_id' => $claim->organization_id,
                'client_id' => $claim->client_id,
                'client_service_id' => $claim->client_service_id,
                'entry_type' => FinancialEntryType::Cost,
                'category' => $category->costCategory(),
                'description' => "Reimbursement: {$claim->title}",
                'amount' => $claim->amount,
                'currency' => $claim->currency ?? 'USD',
                'occurred_on' => $data['paid_on'],
                'status' => FinancialEntryStatus::Posted,
                'created_by' => Auth::id(),
                'notes' => $claim->description
                    ? "Expense claim: {$claim->description}"
                    : 'Paid via expense claim.',
            ]);

            $old = $claim->toArray();
            $claim->update([
                'status' => ExpenseClaimStatus::Paid,
                'payment_method' => $data['method'],
                'paid_on' => $data['paid_on'],
                'financial_entry_id' => $entry->id,
            ]);

            $fresh = $claim->fresh();
            $this->auditLogger->log('expense_claim.paid', $claim, $old, $fresh?->toArray());
            ActivityHelper::log('Expense claim paid', $claim, ['financial_entry_id' => $entry->id]);

            return $fresh ?? $claim;
        });
    }
}
