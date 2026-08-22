<?php

namespace App\Domains\Integrations\Enums;

enum IntegrationProvider: string
{
    case Aws = 'aws';
    case Github = 'github';
    case Gitlab = 'gitlab';
    case Cloudflare = 'cloudflare';
    case Namecheap = 'namecheap';
    case Cpanel = 'cpanel';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Aws => 'AWS',
            self::Github => 'GitHub',
            self::Gitlab => 'GitLab',
            self::Cloudflare => 'Cloudflare',
            self::Namecheap => 'Namecheap',
            self::Cpanel => 'cPanel',
            self::Custom => 'Custom',
        };
    }

    public function defaultCategory(): IntegrationCategory
    {
        return match ($this) {
            self::Aws => IntegrationCategory::Cloud,
            self::Github, self::Gitlab => IntegrationCategory::SourceControl,
            self::Cloudflare => IntegrationCategory::Dns,
            self::Namecheap => IntegrationCategory::Registrar,
            self::Cpanel => IntegrationCategory::Hosting,
            self::Custom => IntegrationCategory::Cloud,
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
