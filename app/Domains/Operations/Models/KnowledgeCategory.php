<?php

namespace App\Domains\Operations\Models;

use App\Domains\Operations\Enums\KnowledgeCategoryStatus;
use App\Support\Concerns\BelongsToOrganization;
use Database\Factories\Operations\KnowledgeCategoryFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KnowledgeCategory extends Model
{
    use BelongsToOrganization;

    /** @use HasFactory<KnowledgeCategoryFactory> */
    use HasFactory;

    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'slug',
        'description',
        'parent_id',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => KnowledgeCategoryStatus::class,
            'sort_order' => 'integer',
        ];
    }

    protected static function newFactory(): KnowledgeCategoryFactory
    {
        return KnowledgeCategoryFactory::new();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(KnowledgeArticle::class);
    }
}
