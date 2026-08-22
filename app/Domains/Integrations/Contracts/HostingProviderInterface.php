<?php

namespace App\Domains\Integrations\Contracts;

use App\Domains\Integrations\Data\ExternalResourceDTO;

interface HostingProviderInterface
{
    public function testConnection(): bool;

    /** @return array<int, ExternalResourceDTO> */
    public function fetchAccounts(): array;
}
