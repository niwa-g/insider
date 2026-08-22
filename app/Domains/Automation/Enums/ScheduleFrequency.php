<?php

namespace App\Domains\Automation\Enums;

enum ScheduleFrequency: string
{
    case EveryFifteenMinutes = 'every_fifteen_minutes';
    case Hourly = 'hourly';
    case Daily = 'daily';

    public function label(): string
    {
        return match ($this) {
            self::EveryFifteenMinutes => 'Every 15 minutes',
            self::Hourly => 'Hourly',
            self::Daily => 'Daily',
        };
    }

    public function isDue(?\DateTimeInterface $lastRunAt): bool
    {
        if ($lastRunAt === null) {
            return true;
        }

        $minutes = match ($this) {
            self::EveryFifteenMinutes => 15,
            self::Hourly => 60,
            self::Daily => 1440,
        };

        return now()->diffInMinutes($lastRunAt) >= $minutes;
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
