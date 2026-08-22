# API, Integrations & Automation

## Frontend transport

Primary UI: Laravel → Inertia.js → React (no REST for page navigation).

## External API (`/api/v1`)

Reserved for future clients and integrations:

```text
/api/v1/clients
/api/v1/projects
/api/v1/services
/api/v1/domains
/api/v1/hosting
/api/v1/applications
/api/v1/environments
/api/v1/infrastructure
/api/v1/tasks
/api/v1/tickets
```

Conventions: Form Requests, API Resources, Policies, pagination, filtering, sorting, rate limiting, consistent JSON errors. Same Actions as Inertia controllers.

## Integration architecture

Each integration record: provider, connection status, config, credentials reference, last sync, error status, logs.

Interfaces (Phase 8+):

- `DomainProviderInterface` — register, renew, getDetails, getExpiry, updateDNS, transfer
- `HostingProviderInterface` — create, suspend, terminate, renew, getDetails
- `SourceControlProviderInterface` — repos, commits, deploy hooks
- `CloudInventoryProviderInterface` — pull metadata for EC2/ECS/RDS/S3/…

Sync is **pull metadata** by default. Mutating production cloud/DNS requires explicit authorized action.

## Automation (Phase 9)

Domain events emitted from Phase 2+:

- `DomainExpiring`, `SslExpiring`, `DeploymentFailed`, `SlaBreachWarning`, …

Until Phase 9: hardcoded listeners/jobs. Later: configurable automation rules.

## Notifications

Channels: in-app, email; optional Slack/Teams later.

Triggers: renewals, deadlines, overdue tasks, failed deploys, SLA breach, backup failure.

## AI readiness (Phase 10)

`AiGateway` + allowlisted tools only (`SearchClients`, `GetClientSummary`, `ListExpiringRenewals`, …).

**Never** execute AI-generated SQL. Use Meilisearch + structured query services.
