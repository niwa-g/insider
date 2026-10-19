<?php

namespace App\Domains\Finance\Enums;

enum DiscountReasonStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    /** @return array<string, string> */
    public static function options(): array
    {
        return [
            self::Active->value => 'Active',
            self::Inactive->value => 'Inactive',
        ];
    }
}
