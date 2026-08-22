<?php

namespace App\Domains\Operations\Models;

use App\Models\User;
use App\Support\Concerns\BelongsToOrganization;
use Database\Factories\Operations\OpsNotificationFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OpsNotification extends Model
{
    use BelongsToOrganization;

    /** @use HasFactory<OpsNotificationFactory> */
    use HasFactory;

    use HasUlids;

    protected $table = 'ops_notifications';

    protected $fillable = [
        'organization_id',
        'user_id',
        'type',
        'title',
        'body',
        'data',
        'read_at',
        'notifiable_type',
        'notifiable_id',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    protected static function newFactory(): OpsNotificationFactory
    {
        return OpsNotificationFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }
}
