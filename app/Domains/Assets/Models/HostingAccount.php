<?php

namespace App\Domains\Assets\Models;

use App\Domains\Assets\Enums\HostingAccountStatus;
use App\Support\Concerns\BelongsToClient;
use App\Support\Concerns\BelongsToOrganization;
use Database\Factories\Assets\HostingAccountFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HostingAccount extends Model
{
    use BelongsToClient;
    use BelongsToOrganization;

    /** @use HasFactory<HostingAccountFactory> */
    use HasFactory;

    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'client_id',
        'provider_id',
        'name',
        'plan',
        'primary_domain',
        'username',
        'expires_at',
        'cost',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'date',
            'cost' => 'decimal:2',
            'status' => HostingAccountStatus::class,
        ];
    }

    protected static function newFactory(): HostingAccountFactory
    {
        return HostingAccountFactory::new();
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
