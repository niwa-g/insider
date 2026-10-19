<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Models\DiscountReason;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DeleteDiscountReason
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(DiscountReason $reason): void
    {
        if ($reason->is_system) {
            throw new InvalidArgumentException('Standard discount reasons cannot be deleted.');
        }

        if ($reason->invoices()->exists()) {
            throw new InvalidArgumentException('This reason is used on invoices and cannot be deleted. Deactivate it instead.');
        }

        DB::transaction(function () use ($reason) {
            $snapshot = $reason->toArray();
            $reason->delete();
            $this->auditLogger->log('discount_reason.deleted', $reason, $snapshot, null);
            ActivityHelper::log('Discount reason deleted', $reason);
        });
    }
}
