# SkyRefund

SkyRefund is a refund-management platform (Laravel backend + lightweight frontend) for handling passenger refund submissions and administrative workflows.

This README documents how to set up, run, test, and deploy the application locally and in containerized environments.

**Project layout (important folders)**
- `app/` — Laravel application code (Models, Controllers, Services, Jobs, Mail, Policies).
- `routes/` — `web.php` (public/web routes) and API route files.
- `resources/views/` — Blade views for frontend and admin UI.
- `public/js`, `public/css` — Distributed frontend assets (app.js, admin.js, styles.css).
- `resources/js`, `resources/css` — source assets (when present).
- `database/` — migrations, factories, seeders.
- `openapi.yaml` — API specification for the versioned API.

## Requirements
- PHP 8.1+ (as required by Laravel 13)
- Composer
- Node.js 16+ (for building frontend assets)
- pnpm / npm / yarn (the repo includes Vite config)
- MySQL or compatible database
- Docker (optional, for containerized development)

## Quick Local Setup
1. Install PHP dependencies:

```bash
composer install
```

2. Copy environment and generate key:

```bash
cp .env.example .env
php artisan key:generate
```

3. Configure `.env` database credentials and other services (mail, queue driver).

4. Run migrations and optional seeders:

```bash
php artisan migrate --seed
```

5. Install frontend dependencies and build (optional for development):

```bash
pnpm install
pnpm run dev   # or `pnpm run build` for production assets
```

6. Start the app:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

The application will be available at `http://127.0.0.1:8000`.

## Docker (recommended for parity)
The repository includes a `Dockerfile` and `docker-compose.yml` for local development. Start the stack:

```bash
docker compose up --build -d
```

Useful container commands:

```bash
docker compose exec app php artisan migrate --force
docker compose exec app php artisan queue:work
docker compose exec app vendor/bin/phpunit
```

## Environment Variables
Edit `.env` (copy from `.env.example`) and set at minimum:
- `APP_URL`, `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `MAIL_*` settings for email notifications
- `QUEUE_CONNECTION` for background jobs

## Running Background Workers
Start queue workers to process notifications and jobs:

```bash
php artisan queue:work --sleep=3 --tries=3
```

Schedule (if used):

```bash
php artisan schedule:run
```

## Tests
Run the PHPUnit test suite:

```bash
vendor/bin/phpunit
```

There are unit and feature tests under `tests/`.

## Frontend
- The public-facing UI uses simple Blade templates in `resources/views/frontend` and a small vanilla JS app at `public/js/app.js` that submits refunds to the API (`POST /api/v1/refunds`).
- The admin UI lives under `resources/views/admin` and uses `public/js/admin.js`.
- Styles are in `public/css/styles.css`.

Client-side validation: the frontend contains `public/js/app.js` which performs basic client-side validation and submission flow — extend this file to add stricter validation rules (name, email, phone number, file validations).

## CLI (Local)

A small standalone CLI is provided at `bin/refund-cli.php` for quick administrative tasks without using the web UI. It bootstraps the Laravel app and exposes these commands:

- `php bin/refund-cli.php list [--status=STATUS]` — list recent refunds
- `php bin/refund-cli.php show {id}` — show refund JSON
- `php bin/refund-cli.php approve {id} [--note='...'] [--by=USER_ID]`
- `php bin/refund-cli.php reject {id} --reason='...' [--by=USER_ID]`
- `php bin/refund-cli.php cancel {id} --reason='...' [--by=USER_ID]`
- `php bin/refund-cli.php return {id} --note='...' [--by=USER_ID]`
- `php bin/refund-cli.php complete {id} --payment=REF [--by=USER_ID]`

Run the script from the repository root with PHP (it requires the app's dependencies and `.env` to be configured):

```bash
php bin/refund-cli.php list
php bin/refund-cli.php approve 123 --note="Approved via CLI" --by=1
```
## API
- Versioned API endpoints live under `/api/v1` (see `routes/api.php` and controllers under `app/Http/Controllers/Api`).
- OpenAPI specification: [openapi.yaml](openapi.yaml)

Common endpoints (examples):
- `POST /api/v1/refunds` — create refund (public)
- `GET /api/v1/airlines` — list airlines for passenger form
- Admin routes: `/api/v1/admin/refunds/*` for approve/reject/return/assign actions

Use `php artisan route:list --path=refunds` to view refund-related routes.

## Code Structure & Key Services
- `App\Services\RefundWorkflowService` — encapsulates workflow transitions, status logs, and notification dispatching.
- `App\Models\Refund`, `RefundTicket`, `RefundAttachment` — domain models and relations.
- Jobs under `app/Jobs` handle async work such as `SendRefundNotification` and `ProcessRefundAi`.

## Deployment Notes
- In production set `APP_ENV=production` and `APP_DEBUG=false`.
- Use a process supervisor (supervisord/systemd) to run `php artisan queue:work` and other workers.
- Use a real mail provider (SMTP, Mailgun, SES) and secure credentials via environment injection.

## Troubleshooting
- If routes fail to boot, run `php artisan config:clear` and `php artisan route:clear`.
- Check logs: `storage/logs/laravel.log`.
- Clear cached views: `php artisan view:clear`.

## Contributing
- Fork the repo, open a feature branch, add tests, and submit a PR.

## License & Contacts
- See the project root for licensing information. For developer questions, open an issue or contact the maintainer listed in repository metadata.

---

If you'd like, I can also:
- add a development checklist to `.github/`,
- generate a `CONTRIBUTING.md`, or
- create a short `DEPLOY.md` with example Docker Compose production hints.


