<?php

namespace App\Domains\Assets\Models;

use App\Domains\Assets\Enums\ProviderStatus;
use App\Domains\Assets\Enums\ProviderType;
use App\Support\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Provider extends Model
{
    use BelongsToOrganization;
    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'code',
        'type',
        'website',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProviderType::class,
            'status' => ProviderStatus::class,
        ];
    }
}
