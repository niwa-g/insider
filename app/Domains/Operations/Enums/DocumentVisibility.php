<?php

namespace App\Domains\Operations\Enums;

enum DocumentVisibility: string
{
    case Private = 'private';
    case Internal = 'internal';
    case Client = 'client';

    public function label(): string
    {
        return match ($this) {
            self::Private => 'Private',
            self::Internal => 'Internal',
            self::Client => 'Client',
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
