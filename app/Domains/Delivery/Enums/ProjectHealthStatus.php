<?php

namespace App\Domains\Delivery\Enums;

enum ProjectHealthStatus: string
{
    case Healthy = 'healthy';
    case Watch = 'watch';
    case AtRisk = 'at_risk';
    case Critical = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::Healthy => 'Healthy',
            self::Watch => 'Watch',
            self::AtRisk => 'At risk',
            self::Critical => 'Critical',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }

    public static function fromScore(int $score): self
    {
        return match (true) {
            $score >= 80 => self::Healthy,
            $score >= 60 => self::Watch,
            $score >= 40 => self::AtRisk,
            default => self::Critical,
        };
    }
}
