<?php

namespace App\Domains\Finance\Enums;

enum FinancialEntryType: string
{
    case Revenue = 'revenue';
    case Cost = 'cost';

    public function label(): string
    {
        return match ($this) {
            self::Revenue => 'Revenue',
            self::Cost => 'Cost',
        };
    }

    public function opposite(): self
    {
        return match ($this) {
            self::Revenue => self::Cost,
            self::Cost => self::Revenue,
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
