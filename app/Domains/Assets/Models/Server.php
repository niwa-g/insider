<?php

namespace App\Domains\Assets\Models;

use App\Domains\Assets\Enums\ServerRole;
use App\Domains\Assets\Enums\ServerStatus;
use App\Domains\Crm\Models\Client;
use App\Support\Concerns\BelongsToOrganization;
use Database\Factories\Assets\ServerFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Server extends Model
{
    use BelongsToOrganization;

    /** @use HasFactory<ServerFactory> */
    use HasFactory;

    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'client_id',
        'provider_id',
        'name',
        'hostname',
        'ip_address',
        'role',
        'os',
        'specs',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'role' => ServerRole::class,
            'status' => ServerStatus::class,
            'specs' => 'array',
        ];
    }

    protected static function newFactory(): ServerFactory
    {
        return ServerFactory::new();
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
