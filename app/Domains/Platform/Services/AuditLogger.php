<?php

namespace App\Domains\Platform\Services;

use App\Domains\Platform\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    public function log(
        string $action,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $organizationId = null,
    ): AuditLog {
        $user = Auth::user();

        return AuditLog::query()->create([
            'organization_id' => $organizationId
                ?? ($user !== null ? $user->organization_id : null)
                ?? (app()->bound('currentOrganizationId') ? app('currentOrganizationId') : null),
            'user_id' => $user !== null ? $user->id : null,
            'action' => $action,
            'auditable_type' => $auditable ? $auditable::class : null,
            'auditable_id' => $auditable?->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }
}
