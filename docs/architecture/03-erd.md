# ERD & Database Schema

## Ownership rules

- `organization_id` required on tenant tables
- `client_id` required on operational records (except catalog, identity, pure platform)
- `project_id` nullable
- Morph columns only for attachable / subject / renewable / allocatable

## Logical ERD

```text
organizations
  └── clients
        ├── contacts
        ├── projects
        │     ├── project_services → client_services
        │     ├── applications → environments → deployments
        │     └── tasks, tickets, change_requests
        ├── client_services → service_types → service_categories
        ├── domains → dns_records, ssl_certificates
        ├── hosting_accounts → servers
        ├── cloud_resources
        ├── financial_entries
        ├── renewals → renewal_events
        └── tickets, documents
```

Environment resources link environments to servers / cloud resources / hosting via `environment_resources`.

## Conventions

- ULID primary keys (except `audit_logs` bigint)
- `timestamps` on all tables
- Soft deletes on business entities
- No soft delete / no update on `audit_logs`
- Posted `financial_entries` are voided with reversing entries, not edited

## Table groups

### Identity
`organizations`, `departments`, `teams`, `team_user`, `users`, Spatie permission tables

### CRM
`client_groups`, `client_group_client`, `clients`, `contacts`, `client_onboardings`, `onboarding_items`

### Catalog
`service_categories`, `service_types`, `sla_policies`, `sla_targets`

### Delivery
`projects`, `project_templates`, `project_template_items`, `project_services`, `tasks`, `tags`, `taggables`, `change_requests`, `handovers`, `handover_items`

### Assets
`providers`, `applications`, `environments`, `deployments`, `domains`, `dns_records`, `ssl_certificates`, `hosting_accounts`, `servers`, `cloud_resources`, `environment_resources`, `backup_configurations`, `monitoring_checks`, `secret_references`

### Operations
`tickets`, `ticket_comments`, `documents`, `knowledge_categories`, `knowledge_articles`, `article_links`, Spatie `activity_log`

### Finance
`financial_entries`, `invoices`, `invoice_lines`, `renewals`, `renewal_events`

### Platform
`audit_logs`, `integrations`, `integration_logs`, `saved_filters`, `automation_rules` (later)

## Critical indexes

- `(organization_id, status)` — clients, projects, services
- `(organization_id, next_renewal_at)` — renewals
- `(organization_id, occurred_on, entry_type)` — financial_entries
- `(client_id, project_id)` — tasks, tickets, assets

## Finance model

`financial_entries.status`: `draft` | `posted` | `void`

Profit = sum(posted revenue) − sum(posted cost) via `ProfitabilityCalculator` only.

## Renewals

Unified `renewals` table synced from Domain, SSL, HostingAccount, ClientService, Contract, CloudResource. Renewal dashboard queries one table.
