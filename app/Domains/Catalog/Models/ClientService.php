<?php

namespace App\Domains\Catalog\Models;

use App\Domains\Catalog\Enums\BillingCycle;
use App\Domains\Catalog\Enums\BillingType;
use App\Domains\Catalog\Enums\ClientServiceStatus;
use App\Domains\Crm\Models\Client;
use App\Domains\Finance\Models\Renewal;
use App\Domains\Finance\Models\Vendor;
use App\Models\User;
use App\Support\Concerns\BelongsToClient;
use App\Support\Concerns\BelongsToOrganization;
use Database\Factories\Catalog\ClientServiceFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientService extends Model
{
    use BelongsToClient;
    use BelongsToOrganization;

    /** @use HasFactory<ClientServiceFactory> */
    use HasFactory;

    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'client_id',
        'service_type_id',
        'project_id',
        'name',
        'start_date',
        'end_date',
        'renewal_date',
        'billing_type',
        'billing_cycle',
        'cost',
        'selling_price',
        'recurring_revenue',
        'provider',
        'vendor_id',
        'account_manager_id',
        'technical_owner_id',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'renewal_date' => 'date',
            'billing_type' => BillingType::class,
            'billing_cycle' => BillingCycle::class,
            'cost' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'recurring_revenue' => 'decimal:2',
            'status' => ClientServiceStatus::class,
        ];
    }

    protected static function newFactory(): ClientServiceFactory
    {
        return ClientServiceFactory::new();
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function accountManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'account_manager_id');
    }

    public function technicalOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technical_owner_id');
    }

    public function renewal(): MorphOne
    {
        return $this->morphOne(Renewal::class, 'renewable');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function margin(): float
    {
        return (float) $this->selling_price - (float) $this->cost;
    }
}
