<?php

namespace App\Domains\Finance\Models;

use App\Domains\Catalog\Models\ClientService;
use App\Domains\Finance\Enums\ExpenseCategory;
use App\Domains\Finance\Enums\ExpenseClaimStatus;
use App\Domains\Finance\Enums\PaymentMethod;
use App\Models\User;
use App\Support\Concerns\BelongsToClient;
use App\Support\Concerns\BelongsToOrganization;
use Database\Factories\Finance\ExpenseClaimFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ExpenseClaim extends Model
{
    /** @use HasFactory<ExpenseClaimFactory> */
    use HasFactory;

    use BelongsToClient;
    use BelongsToOrganization;
    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'claimant_id',
        'approver_id',
        'client_id',
        'client_service_id',
        'financial_entry_id',
        'title',
        'category',
        'description',
        'amount',
        'currency',
        'incurred_on',
        'receipt_path',
        'receipt_name',
        'status',
        'approved_at',
        'rejection_reason',
        'payment_method',
        'paid_on',
    ];

    protected function casts(): array
    {
        return [
            'status' => ExpenseClaimStatus::class,
            'category' => ExpenseCategory::class,
            'amount' => 'decimal:2',
            'incurred_on' => 'date',
            'approved_at' => 'datetime',
            'paid_on' => 'date',
            'payment_method' => PaymentMethod::class,
        ];
    }

    protected static function newFactory(): ExpenseClaimFactory
    {
        return ExpenseClaimFactory::new();
    }

    public function receiptUrl(): ?string
    {
        return $this->receipt_path
            ? Storage::url($this->receipt_path)
            : null;
    }

    public function claimant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimant_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function clientService(): BelongsTo
    {
        return $this->belongsTo(ClientService::class);
    }

    public function financialEntry(): BelongsTo
    {
        return $this->belongsTo(FinancialEntry::class);
    }
}
