<?php

namespace App\Domains\Operations\Models;

use App\Domains\Operations\Enums\SlaPolicyStatus;
use App\Support\Concerns\BelongsToOrganization;
use Database\Factories\Operations\SlaPolicyFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SlaPolicy extends Model
{
    use BelongsToOrganization;

    /** @use HasFactory<SlaPolicyFactory> */
    use HasFactory;

    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'code',
        'description',
        'is_default',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'status' => SlaPolicyStatus::class,
        ];
    }

    protected static function newFactory(): SlaPolicyFactory
    {
        return SlaPolicyFactory::new();
    }

    public function targets(): HasMany
    {
        return $this->hasMany(SlaTarget::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
