# Domain Architecture

## Bounded contexts

| Domain | Responsibility |
|---|---|
| Identity | Organization, departments, teams, users, roles, MFA, sessions |
| Crm | Clients, contacts, groups, onboarding |
| Catalog | Service categories/types, SLA policies |
| Delivery | Projects, templates, project-services, tasks, change requests, handover, health |
| Assets | Apps, environments, deployments, domains, DNS, SSL, hosting, servers, cloud, backups, monitoring |
| Operations | Tickets, SLA clocks, documents, knowledge base, activity timeline |
| Finance | Costs, revenue, renewals, profitability |
| Platform | Audit, notifications, search, saved filters, automations, integrations |
| Intelligence | AI provider interface + safe tools (Phase 10) |

## Code layout

```text
app/
  Domains/{Domain}/
    Actions/ Models/ Policies/ Events/ Listeners/
    Jobs/ Notifications/ Data/ Enums/ QueryBuilders/
  Http/Controllers/{Domain}/
  Http/Requests/ Http/Resources/ Http/Middleware/
  Support/
```

Request path: **Route → Form Request → Policy → Action → Model/Events → Inertia**.

Controllers stay thin. Money, health, renewals, and SLA clocks live in domain services/actions.

## Shared traits

- `BelongsToOrganization`
- `BelongsToClient`
- `OptionallyBelongsToProject`
- `Auditable`
- `Searchable` (Scout)

## Core entities by domain

**Identity:** Organization, Department, Team, User, Role, Permission

**CRM:** ClientGroup, Client, Contact, ClientOnboarding

**Catalog:** ServiceCategory, ServiceType, SlaPolicy, SlaTarget

**Delivery:** Project, ProjectTemplate, ProjectService, Task, ChangeRequest, Handover

**Assets:** Provider, Application, Environment, Deployment, Domain, DnsRecord, SslCertificate, HostingAccount, Server, CloudResource, EnvironmentResource, BackupConfiguration, MonitoringCheck, SecretReference

**Operations:** Ticket, TicketComment, Document, KnowledgeArticle, Activity

**Finance:** FinancialEntry, Invoice, Renewal, RenewalEvent

**Platform:** AuditLog, Integration, IntegrationLog, SavedFilter, AutomationRule

Health scores are computed (not user-edited source of truth).

## Secrets

`secret_references` store provider + external ARN/path only. Never secret values in DB or Inertia props.
