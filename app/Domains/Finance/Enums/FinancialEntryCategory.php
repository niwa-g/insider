<?php

namespace App\Domains\Finance\Enums;

enum FinancialEntryCategory: string
{
    case Hosting = 'hosting';
    case License = 'license';
    case Labor = 'labor';
    case Subscription = 'subscription';
    case Office = 'office';
    case Rent = 'rent';
    case Utilities = 'utilities';
    case Marketing = 'marketing';
    case Travel = 'travel';
    case Software = 'software';
    case Equipment = 'equipment';
    case Vendor = 'vendor';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Hosting => 'Hosting',
            self::License => 'License',
            self::Labor => 'Labor',
            self::Subscription => 'Subscription',
            self::Office => 'Office',
            self::Rent => 'Rent',
            self::Utilities => 'Utilities',
            self::Marketing => 'Marketing',
            self::Travel => 'Travel',
            self::Software => 'Software',
            self::Equipment => 'Equipment',
            self::Vendor => 'Vendor / supplier',
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
