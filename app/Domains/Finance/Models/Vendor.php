<?php

namespace App\Domains\Finance\Models;

use App\Domains\Catalog\Models\ClientService;
use App\Domains\Finance\Enums\VendorStatus;
use App\Support\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use BelongsToOrganization;
    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'code',
        'email',
        'phone',
        'website',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => VendorStatus::class,
        ];
    }

    public function bills(): HasMany
    {
        return $this->hasMany(VendorBill::class);
    }

    public function clientServices(): HasMany
    {
        return $this->hasMany(ClientService::class);
    }
}
