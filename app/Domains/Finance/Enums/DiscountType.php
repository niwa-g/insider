<?php

namespace App\Domains\Finance\Enums;

enum DiscountType: string
{
    case Flat = 'flat';
    case Percentage = 'percentage';

    /** @return array<string, string> */
    public static function options(): array
    {
        return [
            self::Flat->value => 'Flat amount',
            self::Percentage->value => 'Percentage',
        ];
    }
}
