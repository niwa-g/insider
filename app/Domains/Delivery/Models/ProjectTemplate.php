<?php

namespace App\Domains\Delivery\Models;

use App\Domains\Delivery\Enums\ProjectStatus;
use App\Support\Concerns\BelongsToOrganization;
use Database\Factories\Delivery\ProjectTemplateFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTemplate extends Model
{
    use BelongsToOrganization;

    /** @use HasFactory<ProjectTemplateFactory> */
    use HasFactory;

    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'code',
        'description',
        'default_status',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_status' => ProjectStatus::class,
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): ProjectTemplateFactory
    {
        return ProjectTemplateFactory::new();
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProjectTemplateItem::class)->orderBy('sort_order');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
