<?php

namespace App\Domains\Operations\Models;

use App\Domains\Operations\Enums\KnowledgeArticleStatus;
use App\Models\User;
use App\Support\Concerns\BelongsToOrganization;
use Database\Factories\Operations\KnowledgeArticleFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KnowledgeArticle extends Model
{
    use BelongsToOrganization;

    /** @use HasFactory<KnowledgeArticleFactory> */
    use HasFactory;

    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'knowledge_category_id',
        'title',
        'slug',
        'summary',
        'body',
        'status',
        'is_internal',
        'author_id',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => KnowledgeArticleStatus::class,
            'is_internal' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function newFactory(): KnowledgeArticleFactory
    {
        return KnowledgeArticleFactory::new();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(KnowledgeCategory::class, 'knowledge_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function links(): HasMany
    {
        return $this->hasMany(ArticleLink::class);
    }
}
