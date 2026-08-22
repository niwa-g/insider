<?php

namespace App\Domains\Integrations\Models;

use App\Domains\Integrations\Enums\IntegrationCategory;
use App\Domains\Integrations\Enums\IntegrationProvider;
use App\Domains\Integrations\Enums\IntegrationStatus;
use App\Models\User;
use App\Support\Concerns\BelongsToOrganization;
use Database\Factories\Integrations\IntegrationFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Integration extends Model
{
    use BelongsToOrganization;

    /** @use HasFactory<IntegrationFactory> */
    use HasFactory;

    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'provider',
        'category',
        'status',
        'config',
        'credentials_encrypted',
        'last_synced_at',
        'last_error',
        'connected_by',
        'is_enabled',
    ];

    protected $hidden = [
        'credentials_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'provider' => IntegrationProvider::class,
            'category' => IntegrationCategory::class,
            'status' => IntegrationStatus::class,
            'config' => 'array',
            'last_synced_at' => 'datetime',
            'is_enabled' => 'boolean',
        ];
    }

    protected static function newFactory(): IntegrationFactory
    {
        return IntegrationFactory::new();
    }

    public function connectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'connected_by');
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(IntegrationSyncLog::class)->latest('created_at');
    }

    public function externalRefs(): HasMany
    {
        return $this->hasMany(IntegrationExternalRef::class);
    }

    /** @return array<string, mixed> */
    public function toPublicArray(): array
    {
        $data = $this->toArray();
        unset($data['credentials_encrypted']);

        return $data;
    }
}
