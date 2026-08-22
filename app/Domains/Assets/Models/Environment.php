<?php

namespace App\Domains\Assets\Models;

use App\Domains\Assets\Enums\EnvironmentSlug;
use App\Domains\Assets\Enums\EnvironmentStatus;
use App\Support\Concerns\BelongsToOrganization;
use Database\Factories\Assets\EnvironmentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Environment extends Model
{
    use BelongsToOrganization;

    /** @use HasFactory<EnvironmentFactory> */
    use HasFactory;

    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'application_id',
        'name',
        'slug',
        'url',
        'status',
        'branch',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'slug' => EnvironmentSlug::class,
            'status' => EnvironmentStatus::class,
        ];
    }

    protected static function newFactory(): EnvironmentFactory
    {
        return EnvironmentFactory::new();
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function deployments(): HasMany
    {
        return $this->hasMany(Deployment::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(EnvironmentResource::class);
    }
}
