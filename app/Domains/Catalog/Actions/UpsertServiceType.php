<?php

namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Models\ServiceType;
use App\Domains\Platform\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class UpsertServiceType
{
    public function __construct(private AuditLogger $auditLogger) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(?ServiceType $type, array $data): ServiceType
    {
        return DB::transaction(function () use ($type, $data) {
            if ($type === null) {
                $type = ServiceType::query()->create($data);
                $this->auditLogger->log('service_type.created', $type, null, $type->toArray());
            } else {
                $old = $type->toArray();
                $type->update($data);
                $this->auditLogger->log('service_type.updated', $type, $old, $type->fresh()->toArray());
            }

            return $type->fresh('category');
        });
    }
}
