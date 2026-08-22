<?php

namespace App\Domains\Automation\Models;

use App\Domains\Automation\Enums\AutomationRunStatus;
use App\Domains\Automation\Enums\AutomationScheduleJobType;
use App\Domains\Automation\Enums\ScheduleFrequency;
use App\Support\Concerns\BelongsToOrganization;
use Database\Factories\Automation\AutomationScheduleFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AutomationSchedule extends Model
{
    use BelongsToOrganization;

    /** @use HasFactory<AutomationScheduleFactory> */
    use HasFactory;

    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'job_type',
        'frequency',
        'is_enabled',
        'config',
        'last_run_at',
        'last_status',
        'last_message',
    ];

    protected function casts(): array
    {
        return [
            'job_type' => AutomationScheduleJobType::class,
            'frequency' => ScheduleFrequency::class,
            'is_enabled' => 'boolean',
            'config' => 'array',
            'last_run_at' => 'datetime',
            'last_status' => AutomationRunStatus::class,
        ];
    }

    protected static function newFactory(): AutomationScheduleFactory
    {
        return AutomationScheduleFactory::new();
    }

    public function runs(): HasMany
    {
        return $this->hasMany(AutomationRun::class)->latest('created_at');
    }
}
