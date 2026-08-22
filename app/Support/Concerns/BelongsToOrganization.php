<?php

namespace App\Support\Concerns;

use App\Domains\Identity\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToOrganization
{
    public static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope('organization', function (Builder $builder): void {
            $organizationId = app()->bound('currentOrganizationId')
                ? app('currentOrganizationId')
                : null;

            if ($organizationId) {
                $builder->where(
                    $builder->getModel()->getTable().'.organization_id',
                    $organizationId
                );
            }
        });

        static::creating(function ($model): void {
            if (! $model->organization_id && app()->bound('currentOrganizationId')) {
                $model->organization_id = app('currentOrganizationId');
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
