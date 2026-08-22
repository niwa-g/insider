<?php

namespace App\Domains\Operations\Models;

use App\Models\User;
use App\Support\Concerns\BelongsToOrganization;
use Database\Factories\Operations\TicketCommentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketComment extends Model
{
    use BelongsToOrganization;

    /** @use HasFactory<TicketCommentFactory> */
    use HasFactory;

    use HasUlids;

    protected $fillable = [
        'organization_id',
        'ticket_id',
        'user_id',
        'body',
        'is_internal',
    ];

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
        ];
    }

    protected static function newFactory(): TicketCommentFactory
    {
        return TicketCommentFactory::new();
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
