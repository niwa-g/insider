<?php

namespace App\Domains\Finance\Enums;

enum FinancialEntryCategory: string
{
    case Hosting = 'hosting';
    case License = 'license';
    case Labor = 'labor';
    case Subscription = 'subscription';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Hosting => 'Hosting',
            self::License => 'License',
            self::Labor => 'Labor',
            self::Subscription => 'Subscription',
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
