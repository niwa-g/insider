<?php

namespace App\Domains\Integrations\Adapters\Null;

use App\Domains\Integrations\Contracts\CloudInventoryProviderInterface;
use App\Domains\Integrations\Contracts\DomainDnsProviderInterface;
use App\Domains\Integrations\Contracts\HostingProviderInterface;
use App\Domains\Integrations\Contracts\SourceControlProviderInterface;
use App\Domains\Integrations\Data\ExternalResourceDTO;

class NullAdapter implements CloudInventoryProviderInterface, DomainDnsProviderInterface, HostingProviderInterface, SourceControlProviderInterface
{
    public function testConnection(): bool
    {
        return false;
    }

    public function fetchInventory(): array
    {
        return [];
    }

    public function fetchRepositories(): array
    {
        return [];
    }

    public function fetchZones(): array
    {
        return [];
    }

    public function fetchDnsRecords(string $zoneId): array
    {
        return [];
    }

    public function fetchAccounts(): array
    {
        return [];
    }
}
