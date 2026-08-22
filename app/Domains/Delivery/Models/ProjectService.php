<?php

namespace App\Domains\Delivery\Models;

use App\Domains\Catalog\Models\ClientService;
use App\Support\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectService extends Model
{
    use BelongsToOrganization;
    use HasUlids;

    protected $fillable = [
        'organization_id',
        'project_id',
        'client_service_id',
        'notes',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function clientService(): BelongsTo
    {
        return $this->belongsTo(ClientService::class);
    }
}
