<?php

namespace App\Domains\Assets\Enums;

enum EnvironmentResourceRole: string
{
    case Primary = 'primary';
    case Replica = 'replica';
    case Cache = 'cache';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Primary => 'Primary',
            self::Replica => 'Replica',
            self::Cache => 'Cache',
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
