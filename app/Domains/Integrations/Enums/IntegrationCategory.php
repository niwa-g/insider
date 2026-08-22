<?php

namespace App\Domains\Integrations\Enums;

enum IntegrationCategory: string
{
    case Cloud = 'cloud';
    case SourceControl = 'source_control';
    case Dns = 'dns';
    case Registrar = 'registrar';
    case Hosting = 'hosting';

    public function label(): string
    {
        return match ($this) {
            self::Cloud => 'Cloud',
            self::SourceControl => 'Source Control',
            self::Dns => 'DNS',
            self::Registrar => 'Registrar',
            self::Hosting => 'Hosting',
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
