<?php

namespace App\Domains\Assets\Enums;

enum DomainStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case PendingTransfer = 'pending_transfer';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Expired => 'Expired',
            self::PendingTransfer => 'Pending transfer',
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
