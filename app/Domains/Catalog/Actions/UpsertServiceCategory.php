<?php

namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Models\ServiceCategory;
use App\Domains\Platform\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class UpsertServiceCategory
{
    public function __construct(private AuditLogger $auditLogger) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(?ServiceCategory $category, array $data): ServiceCategory
    {
        return DB::transaction(function () use ($category, $data) {
            if ($category === null) {
                $category = ServiceCategory::query()->create($data);
                $this->auditLogger->log('service_category.created', $category, null, $category->toArray());
            } else {
                $old = $category->toArray();
                $category->update($data);
                $this->auditLogger->log('service_category.updated', $category, $old, $category->fresh()->toArray());
            }

            return $category->fresh();
        });
    }
}
