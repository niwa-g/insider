<?php

namespace App\Domains\Delivery\Actions;

use App\Domains\Catalog\Models\ClientService;
use App\Domains\Delivery\Models\Project;
use App\Domains\Delivery\Models\ProjectService;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SyncProjectServices
{
    public function __construct(private AuditLogger $auditLogger) {}

    /**
     * @param  list<string>  $clientServiceIds
     */
    public function handle(Project $project, array $clientServiceIds): Project
    {
        return DB::transaction(function () use ($project, $clientServiceIds) {
            $services = ClientService::query()
                ->whereIn('id', $clientServiceIds)
                ->get();

            if ($services->count() !== count(array_unique($clientServiceIds))) {
                throw new InvalidArgumentException('One or more client services were not found.');
            }

            foreach ($services as $service) {
                if ($service->client_id !== $project->client_id) {
                    throw new InvalidArgumentException('Client service does not belong to the project client.');
                }
            }

            $existing = $project->services()->pluck('client_service_id')->all();
            $toDetach = array_diff($existing, $clientServiceIds);
            $toAttach = array_diff($clientServiceIds, $existing);

            if ($toDetach !== []) {
                ProjectService::query()
                    ->where('project_id', $project->id)
                    ->whereIn('client_service_id', $toDetach)
                    ->delete();

                ClientService::query()
                    ->whereIn('id', $toDetach)
                    ->where('project_id', $project->id)
                    ->update(['project_id' => null]);
            }

            foreach ($toAttach as $serviceId) {
                ProjectService::query()->create([
                    'organization_id' => $project->organization_id,
                    'project_id' => $project->id,
                    'client_service_id' => $serviceId,
                ]);

                ClientService::query()->whereKey($serviceId)->update(['project_id' => $project->id]);
            }

            $this->auditLogger->log('project.services_synced', $project, ['ids' => $existing], ['ids' => $clientServiceIds]);
            ActivityHelper::log('Project services updated', $project);

            return $project->fresh(['services.clientService']);
        });
    }
}
