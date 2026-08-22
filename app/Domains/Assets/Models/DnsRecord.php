<?php

namespace App\Domains\Assets\Models;

use App\Domains\Assets\Enums\DnsRecordStatus;
use App\Domains\Assets\Enums\DnsRecordType;
use App\Support\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DnsRecord extends Model
{
    use BelongsToOrganization;
    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'domain_id',
        'type',
        'name',
        'value',
        'ttl',
        'priority',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'type' => DnsRecordType::class,
            'status' => DnsRecordStatus::class,
            'ttl' => 'integer',
            'priority' => 'integer',
        ];
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }
}
