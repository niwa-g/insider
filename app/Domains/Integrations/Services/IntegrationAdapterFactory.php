<?php

namespace App\Domains\Integrations\Services;

use App\Domains\Integrations\Adapters\Fake\FakeAwsInventoryAdapter;
use App\Domains\Integrations\Adapters\Fake\FakeCloudflareAdapter;
use App\Domains\Integrations\Adapters\Fake\FakeGithubAdapter;
use App\Domains\Integrations\Adapters\Fake\FakeGitlabAdapter;
use App\Domains\Integrations\Adapters\Fake\FakeHostingAdapter;
use App\Domains\Integrations\Adapters\Fake\FakeNamecheapAdapter;
use App\Domains\Integrations\Adapters\Null\NullAdapter;
use App\Domains\Integrations\Contracts\CloudInventoryProviderInterface;
use App\Domains\Integrations\Contracts\DomainDnsProviderInterface;
use App\Domains\Integrations\Contracts\HostingProviderInterface;
use App\Domains\Integrations\Contracts\SourceControlProviderInterface;
use App\Domains\Integrations\Enums\IntegrationProvider;
use App\Domains\Integrations\Models\Integration;

class IntegrationAdapterFactory
{
    public function make(Integration $integration): CloudInventoryProviderInterface|DomainDnsProviderInterface|HostingProviderInterface|SourceControlProviderInterface
    {
        return match ($integration->provider) {
            IntegrationProvider::Aws => new FakeAwsInventoryAdapter($integration),
            IntegrationProvider::Github => new FakeGithubAdapter($integration),
            IntegrationProvider::Gitlab => new FakeGitlabAdapter($integration),
            IntegrationProvider::Cloudflare => new FakeCloudflareAdapter($integration),
            IntegrationProvider::Namecheap => new FakeNamecheapAdapter($integration),
            IntegrationProvider::Cpanel => new FakeHostingAdapter($integration),
            IntegrationProvider::Custom => new NullAdapter,
        };
    }
}
