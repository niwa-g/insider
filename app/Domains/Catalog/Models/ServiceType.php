<?php

namespace App\Domains\Catalog\Models;

use App\Domains\Catalog\Enums\BillingCycle;
use App\Domains\Catalog\Enums\BillingType;
use App\Domains\Catalog\Enums\ServiceStatus;
use App\Models\User;
use App\Support\Concerns\BelongsToOrganization;
use Database\Factories\Catalog\ServiceTypeFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceType extends Model
{
    use BelongsToOrganization;

    /** @use HasFactory<ServiceTypeFactory> */
    use HasFactory;

    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'service_category_id',
        'name',
        'code',
        'description',
        'billing_type',
        'billing_cycle',
        'default_cost',
        'default_selling_price',
        'sla',
        'owner_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'billing_type' => BillingType::class,
            'billing_cycle' => BillingCycle::class,
            'default_cost' => 'decimal:2',
            'default_selling_price' => 'decimal:2',
            'status' => ServiceStatus::class,
        ];
    }

    protected static function newFactory(): ServiceTypeFactory
    {
        return ServiceTypeFactory::new();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function clientServices(): HasMany
    {
        return $this->hasMany(ClientService::class);
    }
}
