<?php

namespace App\Domains\Assets\Enums;

enum EnvironmentSlug: string
{
    case Production = 'production';
    case Staging = 'staging';
    case Development = 'development';
    case Qa = 'qa';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Production => 'Production',
            self::Staging => 'Staging',
            self::Development => 'Development',
            self::Qa => 'QA',
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
