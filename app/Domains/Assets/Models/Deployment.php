<?php

namespace App\Domains\Assets\Models;

use App\Domains\Assets\Enums\DeploymentStatus;
use App\Models\User;
use App\Support\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deployment extends Model
{
    use BelongsToOrganization;
    use HasUlids;

    protected $fillable = [
        'organization_id',
        'environment_id',
        'version',
        'status',
        'deployed_at',
        'deployed_by',
        'commit_sha',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => DeploymentStatus::class,
            'deployed_at' => 'datetime',
        ];
    }

    public function environment(): BelongsTo
    {
        return $this->belongsTo(Environment::class);
    }

    public function deployer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deployed_by');
    }
}
