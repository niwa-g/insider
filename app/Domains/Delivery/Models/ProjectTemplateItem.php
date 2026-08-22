<?php

namespace App\Domains\Delivery\Models;

use App\Domains\Delivery\Enums\TaskPriority;
use App\Domains\Delivery\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTemplateItem extends Model
{
    use HasUlids;

    protected $fillable = [
        'project_template_id',
        'title',
        'description',
        'status',
        'priority',
        'estimate_hours',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'priority' => TaskPriority::class,
            'estimate_hours' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ProjectTemplate::class, 'project_template_id');
    }
}
