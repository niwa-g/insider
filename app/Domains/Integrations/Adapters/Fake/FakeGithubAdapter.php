<?php

namespace App\Domains\Integrations\Adapters\Fake;

use App\Domains\Integrations\Contracts\SourceControlProviderInterface;
use App\Domains\Integrations\Data\ExternalResourceDTO;
use App\Domains\Integrations\Models\Integration;

class FakeGithubAdapter implements SourceControlProviderInterface
{
    public function __construct(private Integration $integration) {}

    public function testConnection(): bool
    {
        return true;
    }

    public function fetchRepositories(): array
    {
        $org = $this->integration->config['org'] ?? $this->integration->config['login'] ?? 'cypherhost';

        return [
            new ExternalResourceDTO('github_repo', "{$org}/platform-api", 'platform-api', [
                'org' => $org,
                'default_branch' => 'main',
                'private' => true,
                'url' => "https://github.com/{$org}/platform-api",
            ]),
        ];
    }
}
