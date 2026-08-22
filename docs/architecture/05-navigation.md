# Application Navigation & Dashboard

## Main navigation (permission-gated)

```text
Dashboard

Clients
├── All Clients
├── Contacts
└── Client Groups

Projects
├── All Projects
├── Active
├── Completed
└── Templates

Services
├── Service Catalog
├── Client Services
├── Renewals
└── Service Costs

Assets
├── Applications
├── Environments
├── Deployments
├── Infrastructure
├── Cloud
├── Providers
├── Domains
├── SSL Certificates
└── Hosting

Tasks

Support
├── Tickets
├── SLA
└── Knowledge Base

Finance
├── Revenue
├── Costs
├── Renewals
└── Profitability

Documents
Reports
Integrations
Settings
```

Phase 1 ships Dashboard + Settings (users/teams/roles/audit) only; other items appear as disabled/placeholder or are omitted until their phase.

## Global UX

- Clean sidebar + top bar
- Global search (Cmd/Ctrl+K) from Phase 2
- Breadcrumbs on every resource
- Dark mode support
- Responsive mobile for dashboard, clients, projects, tasks, tickets, renewals, notifications

## Operational dashboard

**KPI cards:** total/active clients, projects, MRR, monthly revenue/cost/profit, outstanding payments, apps, production envs, servers, domains, SSL, open tickets, failed deployments

**Needs attention:** renewals (7/14/30/60/90), overdue tasks, SLA breaches, failed deploys, at-risk projects, pending approvals

## Role homes

| Role group | Widget focus |
|---|---|
| Management | Commercial KPIs, at-risk projects, renewals |
| Technical | Assets, deploys, tickets, monitoring |
| Finance | P&L, MRR, renewals |
| Sales | Pipeline, prospects, renewals |

Same layout shell; different widget sets.

## Client show tabs

Overview, Contacts, Projects, Services, Domains, Hosting, Applications, Infrastructure, Contracts, Invoices, Profitability, Tickets, Tasks, Documents, Activity.
