<?php

namespace App\Domains\Integrations\Enums;

enum IntegrationStatus: string
{
    case Disconnected = 'disconnected';
    case Connected = 'connected';
    case Error = 'error';
    case Syncing = 'syncing';

    public function label(): string
    {
        return match ($this) {
            self::Disconnected => 'Disconnected',
            self::Connected => 'Connected',
            self::Error => 'Error',
            self::Syncing => 'Syncing',
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
