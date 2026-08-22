# Development Roadmap

Do not parallelize phases. Stabilize each before the next.

## Core planned product (Phases 1–7) — complete

Phases 1–7 are the **core planned product** for v1. They deliver the success criteria: operators can answer who is the client, what we provide, where it runs, what it costs, what it earns, when it renews, and what needs attention today.

### Phase 1 — Foundation

- [x] Laravel + Inertia React + Postgres + Redis + Meilisearch
- [x] Docker Compose, quality tooling, CI
- [x] Fortify + Sanctum, Spatie RBAC
- [x] Organization, departments, teams, users
- [x] AppLayout, operational dashboard
- [x] Settings (users, teams, roles, audit log)
- [x] Audit logs + activity helper
- [x] Architecture docs
- [x] Feature tests: auth, policy deny, org scope

### Phase 2 — Clients

- [x] Clients CRUD + org scope
- [x] Contacts CRUD
- [x] Client groups
- [x] Client show (tabs with live related data + activity)
- [x] Permissions + nav + Pest tests

### Phase 3 — Services

- [x] Service categories + service types (catalog)
- [x] Client services assign/update/delete + pricing
- [x] Renewal sync from client services
- [x] Renewals dashboard + mark renewed
- [x] Permissions + nav + Pest tests

### Phase 4 — Projects

- [x] Projects CRUD + org scope + health score
- [x] Project–client-service linking
- [x] Tasks list + kanban board + status updates
- [x] Project templates with editable items
- [x] Permissions + nav + Pest tests

### Phase 5 — Technical assets

- [x] Applications, environments, deployments CRUD + org scope
- [x] Domains, DNS records, SSL certificates + renewal sync
- [x] Infrastructure: servers, cloud resources, hosting accounts, providers
- [x] Environment resource linking + relationship view
- [x] Permissions + nav + Pest tests

### Phase 6 — Operations

- [x] Tickets CRUD + comments + SLA deadlines
- [x] SLA policies with priority targets
- [x] Documents metadata + local file upload
- [x] Knowledge base categories + articles
- [x] Notifications inbox (ops_notifications)
- [x] Timeline (Spatie activity feed)
- [x] Permissions + nav + Pest tests

### Phase 7 — Finance

- [x] Financial entries (revenue/cost) with draft/post/void workflow
- [x] Invoices with line items and status transitions
- [x] MRR + profitability calculators and reports dashboard
- [x] Permissions + nav + Pest tests

## Optional extensions (not required for v1)

Phases 8–9 exist in the codebase as optional extensions. They were **not** part of the original core plan and are not required for v1 success criteria.

### Phase 8 — Integrations (optional)

- [x] AWS inventory, Git providers, Cloudflare/registrar/hosting adapters (fake/stub)
- [x] Integration records, encrypted credentials, external refs, sync logs
- [x] Permissions + nav + Pest tests

### Phase 9 — Automation (optional)

- [x] Automation rules (trigger, conditions, actions)
- [x] Scheduled jobs: renewals, SLA, integration sync
- [x] Run logs and manual run
- [x] Permissions + nav + Pest tests

## Phase 10 — AI (future / not started)

AI assistant, NL search, summaries, risk/cost analysis via safe tools

## Core complete checklist

Matches product success criteria from `01-product.md`:

- [x] **Who is the client** — Clients, contacts, groups, client show with linked records
- [x] **What we provide** — Service catalog, client services, project linking
- [x] **Where it runs** — Applications, environments, deployments, domains, hosting, servers, cloud
- [x] **What it costs** — Financial entries (cost), service costs, infrastructure costs
- [x] **What it earns** — Financial entries (revenue), MRR calculator, invoices
- [x] **When it renews** — Renewals dashboard, renewal sync from services/domains/SSL
- [x] **What needs attention today** — Operational dashboard KPIs + attention panels (renewals, overdue tasks, at-risk projects, SLA breaches)
- [x] **Settings (Phase 1 promise)** — Users, teams, roles, audit log under `/settings`
- [x] **Org-scoped tenancy** — `organization_id` on tenant tables + tests
- [x] **Permission-gated nav** — Sidebar respects Spatie permissions
- [x] **Audit trail** — Immutable audit log on sensitive actions

## Definition of done (core v1)

- [x] Docs under `docs/architecture/`
- [x] App boots (sqlite local / Docker Compose for postgres+redis+meilisearch)
- [x] Login works; seeded Super Admin
- [x] Org-scoped models + tests
- [x] Permission-gated nav + operational dashboard
- [x] Audit log writes on sensitive actions
- [x] CI workflow runs lint/static analysis/tests
