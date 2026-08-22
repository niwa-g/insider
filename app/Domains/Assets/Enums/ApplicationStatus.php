<?php

namespace App\Domains\Assets\Enums;

enum ApplicationStatus: string
{
    case Planned = 'planned';
    case Active = 'active';
    case Deprecated = 'deprecated';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Planned',
            self::Active => 'Active',
            self::Deprecated => 'Deprecated',
            self::Archived => 'Archived',
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
