<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Enums\VendorStatus;
use App\Domains\Finance\Models\Vendor;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpsertVendor
{
    public function __construct(private AuditLogger $auditLogger) {}

    /** @param  array<string, mixed>  $data */
    public function handle(?Vendor $vendor, array $data): Vendor
    {
        return DB::transaction(function () use ($vendor, $data) {
            if (empty($data['code']) && ! empty($data['name'])) {
                $data['code'] = Str::slug((string) $data['name'], '_');
            }

            $data['status'] = $data['status'] ?? VendorStatus::Active->value;

            if ($vendor === null) {
                $vendor = Vendor::query()->create($data);
                $this->auditLogger->log('vendor.created', $vendor, null, $vendor->toArray());
                ActivityHelper::log('Vendor created', $vendor);

                return $vendor;
            }

            $old = $vendor->toArray();
            $vendor->update($data);
            $this->auditLogger->log('vendor.updated', $vendor, $old, $vendor->fresh()->toArray());
            ActivityHelper::log('Vendor updated', $vendor);

            return $vendor->fresh();
        });
    }
}
