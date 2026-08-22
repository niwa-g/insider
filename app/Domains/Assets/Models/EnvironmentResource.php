<?php

namespace App\Domains\Assets\Models;

use App\Domains\Assets\Enums\EnvironmentResourceRole;
use App\Support\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EnvironmentResource extends Model
{
    use BelongsToOrganization;
    use HasUlids;

    protected $fillable = [
        'organization_id',
        'environment_id',
        'resourceable_type',
        'resourceable_id',
        'role',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'role' => EnvironmentResourceRole::class,
        ];
    }

    public function environment(): BelongsTo
    {
        return $this->belongsTo(Environment::class);
    }

    public function resourceable(): MorphTo
    {
        return $this->morphTo();
    }
}
