<?php

namespace App\Domains\Operations\Models;

use App\Domains\Operations\Enums\TicketPriority;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaTarget extends Model
{
    use HasUlids;

    protected $fillable = [
        'sla_policy_id',
        'priority',
        'respond_within_minutes',
        'resolve_within_minutes',
    ];

    protected function casts(): array
    {
        return [
            'priority' => TicketPriority::class,
        ];
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(SlaPolicy::class, 'sla_policy_id');
    }
}
