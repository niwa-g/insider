<?php

namespace App\Domains\Delivery\Models;

use App\Domains\Delivery\Enums\ProjectHealthStatus;
use App\Domains\Delivery\Enums\ProjectPriority;
use App\Domains\Delivery\Enums\ProjectStatus;
use App\Models\User;
use App\Support\Concerns\BelongsToClient;
use App\Support\Concerns\BelongsToOrganization;
use Database\Factories\Delivery\ProjectFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use BelongsToClient;
    use BelongsToOrganization;

    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'client_id',
        'project_template_id',
        'name',
        'code',
        'description',
        'status',
        'priority',
        'health_status',
        'health_score',
        'start_date',
        'due_date',
        'completed_at',
        'budget',
        'project_manager_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'priority' => ProjectPriority::class,
            'health_status' => ProjectHealthStatus::class,
            'start_date' => 'date',
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'budget' => 'decimal:2',
            'health_score' => 'integer',
        ];
    }

    protected static function newFactory(): ProjectFactory
    {
        return ProjectFactory::new();
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ProjectTemplate::class, 'project_template_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(ProjectService::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(\App\Domains\Assets\Models\Application::class);
    }
}
