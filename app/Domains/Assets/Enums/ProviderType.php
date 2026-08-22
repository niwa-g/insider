<?php

namespace App\Domains\Assets\Enums;

enum ProviderType: string
{
    case Registrar = 'registrar';
    case Dns = 'dns';
    case Hosting = 'hosting';
    case Cloud = 'cloud';
    case Ssl = 'ssl';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Registrar => 'Registrar',
            self::Dns => 'DNS',
            self::Hosting => 'Hosting',
            self::Cloud => 'Cloud',
            self::Ssl => 'SSL',
            self::Other => 'Other',
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
