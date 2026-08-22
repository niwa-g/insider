# Product Architecture

## Purpose

Central IT operations platform for a services company. Single source of truth linking:

**Clients → Services → Projects → Applications → Environments → Infrastructure → Domains → Hosting → Tasks → Support → Costs → Revenue → Renewals → Documents → Activities**

## Product shape

- **Modular Laravel monolith** (not microservices)
- **Inertia + React** for internal UI
- **Versioned `/api/v1`** for future portal and integrations (not used by main UI in Phase 1)
- **Organization-scoped** from day one (`organization_id` on tenant tables)

## Three graphs

1. **Ownership** — Organization → Client → Project → Application → Environment → Infrastructure
2. **Commercial** — ServiceType → ClientService → FinancialEntry → Renewal
3. **Work** — Tasks, Tickets, Documents, Activity

Every major record answers: which org, which client, which project, cost, revenue, renewal, owner.

## Locked defaults (v1)

| Decision | Choice |
|---|---|
| Tenancy | `organization_id` everywhere; one seeded org |
| Currency | USD only |
| Finance | Operational cost/revenue/profit — not full accounting |
| Hours | Fields only; no timesheets |
| Client role | Exists; no portal routes |
| CI/CD | GitHub Actions |
| Auth | Fortify + Sanctum; Spatie Permission |
| Keys | ULID primary keys |

## Out of scope (v1)

Microservices, client portal, live AWS/DNS mutation, AI-generated SQL, full monitoring engine, GST/invoicing engine, timesheets, WhatsApp/SMS.

## Success criteria

Operators can answer: who is the client, what we provide, where it runs, what it costs, what it earns, when it renews, what needs attention today.
