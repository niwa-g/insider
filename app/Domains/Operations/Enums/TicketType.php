<?php

namespace App\Domains\Operations\Enums;

enum TicketType: string
{
    case Incident = 'incident';
    case Request = 'request';
    case Problem = 'problem';
    case Change = 'change';

    public function label(): string
    {
        return match ($this) {
            self::Incident => 'Incident',
            self::Request => 'Request',
            self::Problem => 'Problem',
            self::Change => 'Change',
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
