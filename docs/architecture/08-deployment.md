# Deployment & Local Development

## Local (Docker Compose)

Services:

- `app` — PHP-FPM / Laravel
- `nginx` — web
- `postgres` — PostgreSQL 16
- `redis` — cache/queues
- `meilisearch` — Scout
- `queue` — `php artisan queue:work`
- `scheduler` — `php artisan schedule:work`
- `mailpit` — local mail

## Production (target)

- AWS ECS/Fargate
- RDS PostgreSQL
- ElastiCache Redis
- S3 + CloudFront
- ALB
- ECR

Never commit secrets. Per-environment `.env` / Secrets Manager.

## CI/CD (GitHub Actions)

```text
Push
 → Install deps
 → Pint
 → PHPStan / Larastan
 → Pest
 → ESLint
 → TypeScript check
 → Vitest
 → Build assets
 → Docker build
 → Push ECR (deploy environments)
 → Migrate
 → Health check
```

## Environments

Local → Development → QA → Staging → UAT → Production

Isolated configuration per environment.

## Testing strategy

- **Unit:** Actions, calculators, services
- **Feature:** Auth, CRUD, policies, org scope, workflows
- **Integration:** External providers mocked

## Quality tooling

- Laravel Pint, Larastan/PHPStan
- ESLint, Prettier, TypeScript strict
- Pest + Vitest + React Testing Library
