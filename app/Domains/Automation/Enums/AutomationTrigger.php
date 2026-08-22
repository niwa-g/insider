<?php

namespace App\Domains\Automation\Enums;

enum AutomationTrigger: string
{
    case RenewalExpiring = 'renewal_expiring';
    case SslExpiring = 'ssl_expiring';
    case DomainExpiring = 'domain_expiring';
    case SlaBreach = 'sla_breach';
    case TicketCreated = 'ticket_created';
    case DeploymentFailed = 'deployment_failed';
    case IntegrationSyncFailed = 'integration_sync_failed';
    case ScheduleDaily = 'schedule_daily';
    case ScheduleHourly = 'schedule_hourly';

    public function label(): string
    {
        return match ($this) {
            self::RenewalExpiring => 'Renewal expiring',
            self::SslExpiring => 'SSL expiring',
            self::DomainExpiring => 'Domain expiring',
            self::SlaBreach => 'SLA breach',
            self::TicketCreated => 'Ticket created',
            self::DeploymentFailed => 'Deployment failed',
            self::IntegrationSyncFailed => 'Integration sync failed',
            self::ScheduleDaily => 'Schedule (daily)',
            self::ScheduleHourly => 'Schedule (hourly)',
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
