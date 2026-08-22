# Security Architecture

## Authentication

- Laravel Fortify (session) for Inertia UI
- Sanctum for future `/api/v1` tokens
- Password hashing via bcrypt/argon2 (Laravel default)
- Optional TOTP MFA (Phase 1 hook; enforce for Super Admin/Admin by Phase 6)
- Session management and idle timeout via Laravel config

## Authorization

- Spatie roles & permissions
- Laravel Policies on every model
- `OrganizationScope` — users only see their org’s data
- Form Requests for validation
- Rate limiting on auth and API routes

## Secrets

Never store in ordinary DB fields or frontend:

- AWS keys, API keys, DB passwords, SSH private keys, OAuth secrets

Use:

- Environment variables
- AWS Secrets Manager / equivalent
- `secret_references` (ARN/path only)

## Data protection

- CSRF on web routes
- Secure headers middleware
- Private S3 + signed URLs for documents
- Upload type/size validation
- Encrypted casts only for non-secret sensitive metadata when needed
- Soft deletes where appropriate; audit logs immutable

## Audit

Append-only `audit_logs`:

- user, action, entity, entity_id, old/new values, IP, user agent, timestamp

Normal roles cannot update/delete audit rows.

## Observability

- Application errors, queue failures, scheduled job failures
- Auth events, API errors, integration failures
- Laravel Pail / structured logs; APM later

## Multi-tenant readiness

`organization_id` on tenant tables from day one. Do not hardcode company name in business logic.
