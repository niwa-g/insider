<?php

namespace App\Domains\Finance\Models;

use App\Domains\Crm\Models\Client;
use App\Domains\Finance\Enums\RenewalStatus;
use App\Models\User;
use App\Support\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Renewal extends Model
{
    use BelongsToOrganization;
    use HasUlids;

    protected $fillable = [
        'organization_id',
        'client_id',
        'renewable_type',
        'renewable_id',
        'title',
        'renewal_type',
        'renewal_date',
        'cost',
        'selling_price',
        'status',
        'auto_renew',
        'owner_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'renewal_date' => 'date',
            'cost' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'status' => RenewalStatus::class,
            'auto_renew' => 'boolean',
        ];
    }

    public function renewable(): MorphTo
    {
        return $this->morphTo();
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(RenewalEvent::class);
    }
}
