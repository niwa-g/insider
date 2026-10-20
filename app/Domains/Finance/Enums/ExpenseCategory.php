<?php

namespace App\Domains\Finance\Enums;

enum ExpenseCategory: string
{
    case Travel = 'travel';
    case Meals = 'meals';
    case Transport = 'transport';
    case Software = 'software';
    case Equipment = 'equipment';
    case Office = 'office';
    case ClientBillable = 'client_billable';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Travel => 'Travel',
            self::Meals => 'Meals',
            self::Transport => 'Transport',
            self::Software => 'Software',
            self::Equipment => 'Equipment',
            self::Office => 'Office',
            self::ClientBillable => 'Client billable',
            self::Other => 'Other',
        };
    }

    public function costCategory(): FinancialEntryCategory
    {
        return match ($this) {
            self::Travel => FinancialEntryCategory::Travel,
            self::Meals => FinancialEntryCategory::Other,
            self::Transport => FinancialEntryCategory::Travel,
            self::Software => FinancialEntryCategory::Software,
            self::Equipment => FinancialEntryCategory::Equipment,
            self::Office => FinancialEntryCategory::Office,
            self::ClientBillable => FinancialEntryCategory::Other,
            self::Other => FinancialEntryCategory::Other,
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
