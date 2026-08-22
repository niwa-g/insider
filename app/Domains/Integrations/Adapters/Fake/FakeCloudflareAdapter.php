<?php

namespace App\Domains\Integrations\Adapters\Fake;

use App\Domains\Integrations\Contracts\DomainDnsProviderInterface;
use App\Domains\Integrations\Data\ExternalResourceDTO;
use App\Domains\Integrations\Models\Integration;

class FakeCloudflareAdapter implements DomainDnsProviderInterface
{
    public function __construct(private Integration $integration) {}

    public function testConnection(): bool
    {
        return true;
    }

    public function fetchZones(): array
    {
        return [
            new ExternalResourceDTO('cloudflare_zone', 'zone-fake001', 'example.com', [
                'status' => 'active',
                'name_servers' => ['ns1.cloudflare.com', 'ns2.cloudflare.com'],
            ]),
        ];
    }

    public function fetchDnsRecords(string $zoneId): array
    {
        return [
            new ExternalResourceDTO('dns_record', 'rec-fake001', 'example.com', [
                'zone_id' => $zoneId,
                'type' => 'A',
                'content' => '203.0.113.10',
            ]),
        ];
    }
}
