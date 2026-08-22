<?php

namespace App\Domains\Integrations\Models;

use App\Domains\Integrations\Enums\SyncLogAction;
use App\Domains\Integrations\Enums\SyncLogStatus;
use App\Models\User;
use App\Support\Concerns\BelongsToOrganization;
use Database\Factories\Integrations\IntegrationSyncLogFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationSyncLog extends Model
{
    use BelongsToOrganization;

    /** @use HasFactory<IntegrationSyncLogFactory> */
    use HasFactory;

    use HasUlids;

    public const UPDATED_AT = null;

    protected $fillable = [
        'organization_id',
        'integration_id',
        'action',
        'status',
        'message',
        'meta',
        'started_at',
        'finished_at',
        'triggered_by',
    ];

    protected function casts(): array
    {
        return [
            'action' => SyncLogAction::class,
            'status' => SyncLogStatus::class,
            'meta' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    protected static function newFactory(): IntegrationSyncLogFactory
    {
        return IntegrationSyncLogFactory::new();
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class);
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
