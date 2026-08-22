<?php

namespace App\Domains\Integrations\Contracts;

use App\Domains\Integrations\Data\ExternalResourceDTO;

interface DomainDnsProviderInterface
{
    public function testConnection(): bool;

    /** @return array<int, ExternalResourceDTO> */
    public function fetchZones(): array;

    /** @return array<int, ExternalResourceDTO> */
    public function fetchDnsRecords(string $zoneId): array;
}
