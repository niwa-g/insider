<?php

namespace App\Domains\Integrations\Contracts;

use App\Domains\Integrations\Data\ExternalResourceDTO;

interface CloudInventoryProviderInterface
{
    public function testConnection(): bool;

    /** @return array<int, ExternalResourceDTO> */
    public function fetchInventory(): array;
}
