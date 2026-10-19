<?php

namespace App\Domains\Finance\Models;

use App\Domains\Catalog\Models\ClientService;
use App\Domains\Finance\Enums\VendorBillStatus;
use App\Support\Concerns\BelongsToClient;
use App\Support\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorBill extends Model
{
    use BelongsToClient;
    use BelongsToOrganization;
    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'vendor_id',
        'client_id',
        'client_service_id',
        'renewal_id',
        'number',
        'status',
        'description',
        'amount',
        'currency',
        'issue_date',
        'due_date',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => VendorBillStatus::class,
            'amount' => 'decimal:2',
            'issue_date' => 'date',
            'due_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function clientService(): BelongsTo
    {
        return $this->belongsTo(ClientService::class);
    }

    public function renewal(): BelongsTo
    {
        return $this->belongsTo(Renewal::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(VendorBillPayment::class)->latest('paid_on')->latest('created_at');
    }

    public function financialEntries(): HasMany
    {
        return $this->hasMany(FinancialEntry::class);
    }

    public function amountPaid(): float
    {
        return round((float) $this->payments()->sum('amount'), 2);
    }

    public function balanceDue(): float
    {
        return max(0, round((float) $this->amount - $this->amountPaid(), 2));
    }
}
