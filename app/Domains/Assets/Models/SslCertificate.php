<?php

namespace App\Domains\Assets\Models;

use App\Domains\Assets\Enums\SslCertificateStatus;
use App\Support\Concerns\BelongsToClient;
use App\Support\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SslCertificate extends Model
{
    use BelongsToClient;
    use BelongsToOrganization;
    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'client_id',
        'domain_id',
        'common_name',
        'issuer',
        'issued_at',
        'expires_at',
        'status',
        'auto_renew',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'expires_at' => 'date',
            'auto_renew' => 'boolean',
            'status' => SslCertificateStatus::class,
        ];
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }
}
