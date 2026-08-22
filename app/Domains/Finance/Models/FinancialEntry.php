<?php

namespace App\Domains\Finance\Models;

use App\Domains\Catalog\Models\ClientService;
use App\Domains\Finance\Enums\FinancialEntryCategory;
use App\Domains\Finance\Enums\FinancialEntryStatus;
use App\Domains\Finance\Enums\FinancialEntryType;
use App\Models\User;
use App\Support\Concerns\BelongsToClient;
use App\Support\Concerns\BelongsToOrganization;
use Database\Factories\Finance\FinancialEntryFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialEntry extends Model
{
    use BelongsToClient;
    use BelongsToOrganization;

    /** @use HasFactory<FinancialEntryFactory> */
    use HasFactory;

    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'client_id',
        'project_id',
        'client_service_id',
        'entry_type',
        'category',
        'description',
        'amount',
        'currency',
        'occurred_on',
        'status',
        'void_of_id',
        'invoice_id',
        'created_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'entry_type' => FinancialEntryType::class,
            'category' => FinancialEntryCategory::class,
            'amount' => 'decimal:2',
            'occurred_on' => 'date',
            'status' => FinancialEntryStatus::class,
        ];
    }

    protected static function newFactory(): FinancialEntryFactory
    {
        return FinancialEntryFactory::new();
    }

    public function clientService(): BelongsTo
    {
        return $this->belongsTo(ClientService::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function voidOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'void_of_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
