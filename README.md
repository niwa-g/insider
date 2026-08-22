# Ark — IT Operations Platform

Centralized company operations platform (Laravel + Inertia + React).

## Documentation

See [docs/architecture/](docs/architecture/) for product, domain, ERD, RBAC, and roadmap.

## Phase 1

Foundation: auth (Fortify + MFA), organizations/teams, Spatie RBAC, audit logs, dashboard shell, Docker, CI.

## Local development

Requires PostgreSQL (`ark` / `ark_test` databases).

```bash
cp .env.example .env
composer install
npm install
# Local Homebrew Postgres, or:
docker compose up -d postgres redis meilisearch mailpit
php artisan key:generate
php artisan migrate --seed
npm run dev
php artisan serve
```

Default admin: `admin@cypherhost.local` / `password`

Or full stack: `docker compose up --build`

## Quality

```bash
vendor/bin/pint
vendor/bin/phpstan analyse
php artisan test
npm run typecheck
npm run test
npm run build
```
