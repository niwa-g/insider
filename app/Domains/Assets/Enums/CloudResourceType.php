<?php

namespace App\Domains\Assets\Enums;

enum CloudResourceType: string
{
    case Compute = 'compute';
    case Database = 'database';
    case Storage = 'storage';
    case Network = 'network';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Compute => 'Compute',
            self::Database => 'Database',
            self::Storage => 'Storage',
            self::Network => 'Network',
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
