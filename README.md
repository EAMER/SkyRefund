# SkyRefundSage

SkyRefundSage is a lightweight refund-management platform built with Laravel 13 and a simple admin/passenger UI. The repository now includes deployment scaffolding for local container use, CI, API docs, and operational defaults.

## Quick start

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

## Docker

```bash
docker compose up --build
```

The containerized app exposes the web app on port 8000 and uses a MySQL service for persistence.

## APIs

- Versioned API routes are available under /api/v1.
- Health check: GET /api/v1/health
- OpenAPI spec: [openapi.yaml](openapi.yaml)

## CI

A GitHub Actions workflow is defined in [.github/workflows/ci.yml](.github/workflows/ci.yml) and runs the Laravel test suite on push and pull requests.

## Operational notes

- Queue processing should be run with: php artisan queue:work
- Logs are written to storage/logs/laravel.log
- For production, set APP_ENV=production, APP_DEBUG=false, and configure a real mail driver and database connection.
