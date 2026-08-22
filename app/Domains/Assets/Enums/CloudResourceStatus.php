<?php

namespace App\Domains\Assets\Enums;

enum CloudResourceStatus: string
{
    case Active = 'active';
    case Stopped = 'stopped';
    case Terminated = 'terminated';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Stopped => 'Stopped',
            self::Terminated => 'Terminated',
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
