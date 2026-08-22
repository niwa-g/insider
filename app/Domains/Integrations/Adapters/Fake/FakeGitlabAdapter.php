<?php

namespace App\Domains\Integrations\Adapters\Fake;

use App\Domains\Integrations\Contracts\SourceControlProviderInterface;
use App\Domains\Integrations\Data\ExternalResourceDTO;
use App\Domains\Integrations\Models\Integration;

class FakeGitlabAdapter implements SourceControlProviderInterface
{
    public function __construct(private Integration $integration) {}

    public function testConnection(): bool
    {
        return true;
    }

    public function fetchRepositories(): array
    {
        $baseUrl = rtrim($this->integration->config['base_url'] ?? 'https://gitlab.com', '/');
        $group = $this->integration->config['org'] ?? 'cypherhost';

        return [
            new ExternalResourceDTO('gitlab_repo', "{$group}/infra", 'infra', [
                'base_url' => $baseUrl,
                'default_branch' => 'main',
                'url' => "{$baseUrl}/{$group}/infra",
            ]),
        ];
    }
}
