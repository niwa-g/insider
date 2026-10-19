<?php

namespace App\Domains\Finance\Models;

use App\Domains\Finance\Enums\DiscountType;
use App\Domains\Finance\Enums\InvoiceStatus;
use App\Support\Concerns\BelongsToClient;
use App\Support\Concerns\BelongsToOrganization;
use Database\Factories\Finance\InvoiceFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use BelongsToClient;
    use BelongsToOrganization;

    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'client_id',
        'number',
        'status',
        'issue_date',
        'due_date',
        'paid_at',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'discount_reason_id',
        'tax_amount',
        'total',
        'currency',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount_type' => DiscountType::class,
            'discount_value' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'status' => InvoiceStatus::class,
        ];
    }

    public function discountReason(): BelongsTo
    {
        return $this->belongsTo(DiscountReason::class);
    }

    protected static function newFactory(): InvoiceFactory
    {
        return InvoiceFactory::new();
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class)->orderBy('sort_order');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(FinancialEntry::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class)->latest('paid_on')->latest('created_at');
    }

    public function amountPaid(): float
    {
        return round((float) $this->payments()->sum('amount'), 2);
    }

    public function balanceDue(): float
    {
        return max(0, round((float) $this->total - $this->amountPaid(), 2));
    }
}
