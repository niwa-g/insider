<?php

namespace App\Domains\Integrations\Adapters\Fake;

use App\Domains\Integrations\Contracts\HostingProviderInterface;
use App\Domains\Integrations\Data\ExternalResourceDTO;
use App\Domains\Integrations\Models\Integration;

class FakeHostingAdapter implements HostingProviderInterface
{
    public function __construct(private Integration $integration) {}

    public function testConnection(): bool
    {
        return true;
    }

    public function fetchAccounts(): array
    {
        return [
            new ExternalResourceDTO('hosting_account', 'host-fake001', 'shared-prod-01', [
                'plan' => 'business',
                'primary_domain' => 'client.example.com',
                'panel' => $this->integration->provider->value,
            ]),
        ];
    }
}
