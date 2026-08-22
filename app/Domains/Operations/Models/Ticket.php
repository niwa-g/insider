<?php

namespace App\Domains\Operations\Models;

use App\Domains\Assets\Models\Application;
use App\Domains\Delivery\Models\Project;
use App\Domains\Operations\Enums\TicketPriority;
use App\Domains\Operations\Enums\TicketStatus;
use App\Domains\Operations\Enums\TicketType;
use App\Models\User;
use App\Support\Concerns\BelongsToClient;
use App\Support\Concerns\BelongsToOrganization;
use Database\Factories\Operations\TicketFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use BelongsToClient;
    use BelongsToOrganization;

    /** @use HasFactory<TicketFactory> */
    use HasFactory;

    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'client_id',
        'project_id',
        'application_id',
        'sla_policy_id',
        'number',
        'subject',
        'description',
        'status',
        'priority',
        'type',
        'requester_id',
        'assignee_id',
        'respond_by',
        'resolve_by',
        'first_responded_at',
        'resolved_at',
        'closed_at',
        'sla_breached',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'priority' => TicketPriority::class,
            'type' => TicketType::class,
            'respond_by' => 'datetime',
            'resolve_by' => 'datetime',
            'first_responded_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'sla_breached' => 'boolean',
        ];
    }

    protected static function newFactory(): TicketFactory
    {
        return TicketFactory::new();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function slaPolicy(): BelongsTo
    {
        return $this->belongsTo(SlaPolicy::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class)->latest();
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
