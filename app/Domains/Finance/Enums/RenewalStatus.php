<?php

namespace App\Domains\Finance\Enums;

enum RenewalStatus: string
{
    case Upcoming = 'upcoming';
    case Due = 'due';
    case Overdue = 'overdue';
    case Renewed = 'renewed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Upcoming => 'Upcoming',
            self::Due => 'Due',
            self::Overdue => 'Overdue',
            self::Renewed => 'Renewed',
            self::Cancelled => 'Cancelled',
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
