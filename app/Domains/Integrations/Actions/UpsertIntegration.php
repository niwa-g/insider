<?php

namespace App\Domains\Integrations\Actions;

use App\Domains\Integrations\Enums\IntegrationProvider;
use App\Domains\Integrations\Enums\IntegrationStatus;
use App\Domains\Integrations\Models\Integration;
use App\Domains\Integrations\Services\CredentialVault;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class UpsertIntegration
{
    public function __construct(
        private AuditLogger $auditLogger,
        private CredentialVault $credentialVault,
    ) {}

    /** @param  array<string, mixed>  $data  @param  array<string, mixed>|null  $credentials */
    public function handle(?Integration $model, array $data, ?array $credentials = null): Integration
    {
        return DB::transaction(function () use ($model, $data, $credentials) {
            if (isset($data['provider']) && is_string($data['provider'])) {
                $provider = IntegrationProvider::from($data['provider']);
                $data['provider'] = $provider;
                $data['category'] = $data['category'] ?? $provider->defaultCategory();
            }

            if ($model === null) {
                $data['status'] = $data['status'] ?? IntegrationStatus::Disconnected;
                $model = Integration::query()->create($data);
                $this->auditLogger->log('integration.created', $model, null, $model->toPublicArray());
                ActivityHelper::log('Integration created', $model);
            } else {
                $old = $model->toPublicArray();
                $model->update($data);
                $this->auditLogger->log('integration.updated', $model, $old, $model->fresh()->toPublicArray());
                ActivityHelper::log('Integration updated', $model);
            }

            if ($credentials !== null && $credentials !== []) {
                $filtered = array_filter($credentials, fn ($value) => filled($value));
                if ($filtered !== []) {
                    $this->credentialVault->store($model, $filtered);
                }
            }

            return $model->fresh();
        });
    }
}
