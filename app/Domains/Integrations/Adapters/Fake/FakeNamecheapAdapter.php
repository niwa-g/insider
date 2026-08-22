<?php

namespace App\Domains\Integrations\Adapters\Fake;

use App\Domains\Integrations\Contracts\DomainDnsProviderInterface;
use App\Domains\Integrations\Data\ExternalResourceDTO;
use App\Domains\Integrations\Models\Integration;

class FakeNamecheapAdapter implements DomainDnsProviderInterface
{
    public function __construct(private Integration $integration) {}

    public function testConnection(): bool
    {
        return true;
    }

    public function fetchZones(): array
    {
        return [
            new ExternalResourceDTO('domain', 'nc-fake001', 'cypherhost.io', [
                'registrar' => 'namecheap',
                'expires_at' => now()->addYear()->toDateString(),
            ]),
        ];
    }

    public function fetchDnsRecords(string $zoneId): array
    {
        return [];
    }
}
