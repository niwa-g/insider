<?php

namespace App\Domains\Integrations\Adapters\Fake;

use App\Domains\Integrations\Contracts\CloudInventoryProviderInterface;
use App\Domains\Integrations\Data\ExternalResourceDTO;
use App\Domains\Integrations\Models\Integration;

class FakeAwsInventoryAdapter implements CloudInventoryProviderInterface
{
    public function __construct(private Integration $integration) {}

    public function testConnection(): bool
    {
        return true;
    }

    public function fetchInventory(): array
    {
        $region = $this->integration->config['region'] ?? 'us-east-1';
        $accountId = $this->integration->config['account_id'] ?? '123456789012';

        return [
            new ExternalResourceDTO('ec2_instance', 'i-fake001', 'web-prod-01', [
                'region' => $region,
                'instance_type' => 't3.medium',
                'state' => 'running',
                'account_id' => $accountId,
            ]),
            new ExternalResourceDTO('rds', 'db-fake001', 'app-db-primary', [
                'region' => $region,
                'engine' => 'postgres',
                'instance_class' => 'db.t3.small',
                'account_id' => $accountId,
            ]),
            new ExternalResourceDTO('s3_bucket', 'assets-fake-bucket', 'assets-fake-bucket', [
                'region' => $region,
                'account_id' => $accountId,
            ]),
        ];
    }
}
