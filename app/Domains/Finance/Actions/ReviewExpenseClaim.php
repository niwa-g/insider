<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Enums\ExpenseClaimStatus;
use App\Domains\Finance\Models\ExpenseClaim;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReviewExpenseClaim
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function approve(ExpenseClaim $claim): ExpenseClaim
    {
        if ($claim->status !== ExpenseClaimStatus::Pending) {
            throw new InvalidArgumentException('Only pending claims can be approved.');
        }

        return DB::transaction(function () use ($claim) {
            $old = $claim->toArray();
            $claim->update([
                'status' => ExpenseClaimStatus::Approved,
                'approver_id' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            $fresh = $claim->fresh();
            $this->auditLogger->log('expense_claim.approved', $claim, $old, $fresh?->toArray());
            ActivityHelper::log('Expense claim approved', $claim);

            return $fresh ?? $claim;
        });
    }

    public function reject(ExpenseClaim $claim, string $reason): ExpenseClaim
    {
        if ($claim->status !== ExpenseClaimStatus::Pending) {
            throw new InvalidArgumentException('Only pending claims can be rejected.');
        }

        return DB::transaction(function () use ($claim, $reason) {
            $old = $claim->toArray();
            $claim->update([
                'status' => ExpenseClaimStatus::Rejected,
                'approver_id' => Auth::id(),
                'approved_at' => null,
                'rejection_reason' => $reason,
            ]);

            $fresh = $claim->fresh();
            $this->auditLogger->log('expense_claim.rejected', $claim, $old, $fresh?->toArray());
            ActivityHelper::log('Expense claim rejected', $claim);

            return $fresh ?? $claim;
        });
    }

    public function cancel(ExpenseClaim $claim): ExpenseClaim
    {
        if ($claim->status !== ExpenseClaimStatus::Pending) {
            throw new InvalidArgumentException('Only pending claims can be cancelled.');
        }

        return DB::transaction(function () use ($claim) {
            $old = $claim->toArray();
            $claim->update(['status' => ExpenseClaimStatus::Cancelled]);

            $fresh = $claim->fresh();
            $this->auditLogger->log('expense_claim.cancelled', $claim, $old, $fresh?->toArray());
            ActivityHelper::log('Expense claim cancelled', $claim);

            return $fresh ?? $claim;
        });
    }
}
