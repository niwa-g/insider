<?php

namespace App\Domains\Assets\Enums;

enum ServerRole: string
{
    case Web = 'web';
    case Db = 'db';
    case Cache = 'cache';
    case Worker = 'worker';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Web => 'Web',
            self::Db => 'Database',
            self::Cache => 'Cache',
            self::Worker => 'Worker',
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
