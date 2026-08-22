<?php

namespace App\Domains\Assets\Enums;

enum ServerStatus: string
{
    case Active = 'active';
    case Provisioning = 'provisioning';
    case Maintenance = 'maintenance';
    case Decommissioned = 'decommissioned';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Provisioning => 'Provisioning',
            self::Maintenance => 'Maintenance',
            self::Decommissioned => 'Decommissioned',
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
