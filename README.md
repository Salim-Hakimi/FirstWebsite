# Fanous Dormitory and Library Management System

Fanous is a Laravel-based management system for dormitory, library, finance, rooms, students, books, cards, receipts, reports, and staff roles.

## Stack

- PHP 8.2+
- Laravel 12
- MySQL or compatible production database
- Blade for the current stable server-rendered UI
- Vue 3 + Vite progressive frontend architecture
- Tailwind CSS through Vite

## Local Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
php artisan serve
```

## Development

```bash
npm run dev
php artisan serve
```

## Production Build

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize
```

## Security Notes

- Never commit `.env`, server passwords, admin passwords, or database credentials.
- Set `APP_DEBUG=false` in production.
- Use HTTPS and set `APP_FORCE_HTTPS=true`.
- Set strong admin credentials through environment variables or the setup screen.
- Keep `storage/` and `bootstrap/cache/` writable by the web server.

## Frontend Direction

The current production-safe migration strategy is progressive Vue integration. Existing Laravel Blade pages continue to work, while Vue 3 components can be mounted page-by-page through Vite without changing backend routes, authentication, roles, or database logic.


Fanous@2026#Admin!