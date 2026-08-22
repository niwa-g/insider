<?php

namespace App\Domains\Automation\Enums;

enum AutomationScheduleJobType: string
{
    case ScanRenewals = 'scan_renewals';
    case ScanSla = 'scan_sla';
    case SyncIntegrations = 'sync_integrations';
    case EvaluateRules = 'evaluate_rules';

    public function label(): string
    {
        return match ($this) {
            self::ScanRenewals => 'Scan renewals',
            self::ScanSla => 'Scan SLA breaches',
            self::SyncIntegrations => 'Sync integrations',
            self::EvaluateRules => 'Evaluate rules',
        };
    }

    public function toTrigger(): AutomationTrigger
    {
        return match ($this) {
            self::ScanRenewals => AutomationTrigger::RenewalExpiring,
            self::ScanSla => AutomationTrigger::SlaBreach,
            self::SyncIntegrations => AutomationTrigger::IntegrationSyncFailed,
            self::EvaluateRules => AutomationTrigger::ScheduleDaily,
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
