<?php

namespace App\Domains\Operations\Actions;

use App\Domains\Operations\Models\SlaPolicy;
use App\Domains\Operations\Models\SlaTarget;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class UpsertSlaPolicy
{
    public function __construct(private AuditLogger $auditLogger) {}

    /** @param  array<string, mixed>  $data  @param  array<int, array<string, mixed>>  $targets */
    public function handle(?SlaPolicy $model, array $data, array $targets = []): SlaPolicy
    {
        return DB::transaction(function () use ($model, $data, $targets) {
            if ($model === null) {
                $model = SlaPolicy::query()->create($data);
                $this->auditLogger->log('sla_policy.created', $model, null, $model->toArray());
                ActivityHelper::log('SLA policy created', $model);
            } else {
                $old = $model->toArray();
                $model->update($data);
                $this->auditLogger->log('sla_policy.updated', $model, $old, $model->fresh()->toArray());
                ActivityHelper::log('SLA policy updated', $model);
            }

            if ($targets !== []) {
                $model->targets()->delete();
                foreach ($targets as $target) {
                    SlaTarget::query()->create([
                        'sla_policy_id' => $model->id,
                        'priority' => $target['priority'],
                        'respond_within_minutes' => $target['respond_within_minutes'],
                        'resolve_within_minutes' => $target['resolve_within_minutes'],
                    ]);
                }
            }

            if ($model->is_default) {
                SlaPolicy::query()
                    ->where('organization_id', $model->organization_id)
                    ->where('id', '!=', $model->id)
                    ->update(['is_default' => false]);
            }

            return $model->fresh(['targets']);
        });
    }
}
