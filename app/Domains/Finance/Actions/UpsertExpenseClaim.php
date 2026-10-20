<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Enums\ExpenseClaimStatus;
use App\Domains\Finance\Models\ExpenseClaim;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UpsertExpenseClaim
{
    public function __construct(private AuditLogger $auditLogger) {}

    /** @param  array<string, mixed>  $data */
    public function handle(array $data, ?UploadedFile $receipt = null): ExpenseClaim
    {
        return DB::transaction(function () use ($data, $receipt) {
            if ($receipt) {
                $data['receipt_path'] = $receipt->store(
                    "expense-claims/{$data['organization_id']}",
                    'public'
                );
                $data['receipt_name'] = $receipt->getClientOriginalName();
            }

            $data['status'] = ExpenseClaimStatus::Pending;
            $claim = ExpenseClaim::query()->create($data);
            $this->auditLogger->log('expense_claim.created', $claim, null, $claim->toArray());
            ActivityHelper::log('Expense claim submitted', $claim, ['title' => $claim->title]);

            return $claim->fresh(['claimant:id,name', 'client:id,company_name']);
        });
    }
}
