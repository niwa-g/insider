<?php

namespace App\Domains\Assets\Models;

use App\Domains\Assets\Enums\CloudResourceStatus;
use App\Domains\Assets\Enums\CloudResourceType;
use App\Support\Concerns\BelongsToClient;
use App\Support\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CloudResource extends Model
{
    use BelongsToClient;
    use BelongsToOrganization;
    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'client_id',
        'provider_id',
        'name',
        'resource_type',
        'region',
        'external_id',
        'monthly_cost',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'resource_type' => CloudResourceType::class,
            'monthly_cost' => 'decimal:2',
            'status' => CloudResourceStatus::class,
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
