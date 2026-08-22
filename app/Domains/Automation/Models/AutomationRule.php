<?php

namespace App\Domains\Automation\Models;

use App\Domains\Automation\Enums\AutomationRunStatus;
use App\Domains\Automation\Enums\AutomationTrigger;
use App\Models\User;
use App\Support\Concerns\BelongsToOrganization;
use Database\Factories\Automation\AutomationRuleFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AutomationRule extends Model
{
    use BelongsToOrganization;

    /** @use HasFactory<AutomationRuleFactory> */
    use HasFactory;

    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'trigger',
        'conditions',
        'actions',
        'notify_in_app',
        'notify_email',
        'is_enabled',
        'last_run_at',
        'last_status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'trigger' => AutomationTrigger::class,
            'conditions' => 'array',
            'actions' => 'array',
            'notify_in_app' => 'boolean',
            'notify_email' => 'boolean',
            'is_enabled' => 'boolean',
            'last_run_at' => 'datetime',
            'last_status' => AutomationRunStatus::class,
        ];
    }

    protected static function newFactory(): AutomationRuleFactory
    {
        return AutomationRuleFactory::new();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(AutomationRun::class)->latest('created_at');
    }
}
