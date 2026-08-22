<?php

namespace App\Domains\Assets\Enums;

enum EnvironmentStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Provisioning = 'provisioning';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Provisioning => 'Provisioning',
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
