<?php

namespace App\Domains\Automation\Models;

use App\Domains\Automation\Enums\AutomationRunStatus;
use App\Domains\Automation\Enums\AutomationTrigger;
use App\Models\User;
use App\Support\Concerns\BelongsToOrganization;
use Database\Factories\Automation\AutomationRunFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationRun extends Model
{
    use BelongsToOrganization;

    /** @use HasFactory<AutomationRunFactory> */
    use HasFactory;

    use HasUlids;

    protected $fillable = [
        'organization_id',
        'automation_rule_id',
        'automation_schedule_id',
        'trigger',
        'status',
        'message',
        'meta',
        'started_at',
        'finished_at',
        'triggered_by',
    ];

    protected function casts(): array
    {
        return [
            'trigger' => AutomationTrigger::class,
            'status' => AutomationRunStatus::class,
            'meta' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    protected static function newFactory(): AutomationRunFactory
    {
        return AutomationRunFactory::new();
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(AutomationRule::class, 'automation_rule_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(AutomationSchedule::class, 'automation_schedule_id');
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
