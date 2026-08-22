<?php

namespace App\Domains\Operations\Models;

use App\Domains\Delivery\Models\Project;
use App\Domains\Operations\Enums\DocumentStatus;
use App\Domains\Operations\Enums\DocumentVisibility;
use App\Models\User;
use App\Support\Concerns\BelongsToClient;
use App\Support\Concerns\BelongsToOrganization;
use Database\Factories\Operations\DocumentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use BelongsToClient;
    use BelongsToOrganization;

    /** @use HasFactory<DocumentFactory> */
    use HasFactory;

    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'client_id',
        'project_id',
        'ticket_id',
        'title',
        'description',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size_bytes',
        'visibility',
        'uploaded_by',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'visibility' => DocumentVisibility::class,
            'status' => DocumentStatus::class,
            'size_bytes' => 'integer',
        ];
    }

    protected static function newFactory(): DocumentFactory
    {
        return DocumentFactory::new();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
