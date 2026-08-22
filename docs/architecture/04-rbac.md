# RBAC Model

## Roles

| Role | Purpose |
|---|---|
| Super Admin | Full access including system |
| Administrator | Full operational access; cannot delete audit logs |
| Management | Commercial KPIs, clients, projects, renewals, reports |
| Project Manager | Projects, tasks, tickets, docs |
| Tech Lead | Assets, deployments, tickets, tasks |
| Developer | Apps/envs/tasks/tickets; no selling-price edits |
| DevOps | Infrastructure, deployments, integrations |
| Support | Tickets, limited clients, knowledge base |
| Finance | Revenue, costs, renewals, profitability |
| Sales | Clients, prospects, services, proposals, renewals |
| Client | Reserved; no portal routes in v1 |

## Permission format

`module.action` — e.g. `clients.view`, `projects.create`, `finance.view_costs`, `audit.view`, `assets.view_secrets` (metadata only; never values).

## Modules (Phase 1 seed)

- `dashboard`, `users`, `teams`, `roles`, `audit`, `settings`
- Later: `clients`, `projects`, `services`, `applications`, `infrastructure`, `domains`, `hosting`, `tasks`, `tickets`, `finance`, `documents`, `reports`, `integrations`

## Access patterns

- Policies on every model
- `OrganizationScope` global scope on tenant models
- No Inertia payload includes secret values
- MFA: optional Phase 1; enforced for Super Admin / Admin by Phase 6

## Permission matrix (summary)

| Capability | Super | Admin | Mgmt | PM | Tech | Dev | DevOps | Support | Finance | Sales |
|---|---|---|---|---|---|---|---|---|---|---|
| Dashboard | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Users/Roles | ✓ | ✓ | | | | | | | | |
| Audit view | ✓ | ✓ | ✓ | | | | | | | |
| Clients CRUD | ✓ | ✓ | ✓ | ✓ | R | R | R | R | R | ✓ |
| Finance costs | ✓ | ✓ | ✓ | | | | | | ✓ | |
| Secrets meta | ✓ | ✓ | | | ✓ | | ✓ | | | |
| Integrations | ✓ | ✓ | | | | | ✓ | | | |

R = read-limited where noted.
