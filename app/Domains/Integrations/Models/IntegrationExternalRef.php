<?php

namespace App\Domains\Integrations\Models;

use App\Support\Concerns\BelongsToOrganization;
use Database\Factories\Integrations\IntegrationExternalRefFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class IntegrationExternalRef extends Model
{
    use BelongsToOrganization;

    /** @use HasFactory<IntegrationExternalRefFactory> */
    use HasFactory;

    use HasUlids;

    protected $fillable = [
        'organization_id',
        'integration_id',
        'external_type',
        'external_id',
        'external_name',
        'local_type',
        'local_id',
        'payload',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'last_seen_at' => 'datetime',
        ];
    }

    protected static function newFactory(): IntegrationExternalRefFactory
    {
        return IntegrationExternalRefFactory::new();
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class);
    }

    public function local(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'local_type', 'local_id');
    }
}
