<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Models\DiscountReason;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpsertDiscountReason
{
    public function __construct(private AuditLogger $auditLogger) {}

    /** @param  array<string, mixed>  $data */
    public function handle(?DiscountReason $reason, array $data): DiscountReason
    {
        return DB::transaction(function () use ($reason, $data) {
            if (empty($data['code'])) {
                $data['code'] = Str::slug((string) $data['name'], '_');
            }

            if ($reason === null) {
                $data['is_system'] = false;
                $reason = DiscountReason::query()->create($data);
                $this->auditLogger->log('discount_reason.created', $reason, null, $reason->toArray());
                ActivityHelper::log('Discount reason created', $reason);

                return $reason;
            }

            $old = $reason->toArray();
            if ($reason->is_system) {
                unset($data['code'], $data['is_system']);
            }

            $reason->update($data);
            $this->auditLogger->log('discount_reason.updated', $reason, $old, $reason->fresh()->toArray());
            ActivityHelper::log('Discount reason updated', $reason);

            return $reason->fresh();
        });
    }
}
