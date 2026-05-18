# Fanous Production Hosting Checklist

## Server Basics

- Point the web server document root to `public/`.
- Use PHP 8.2 or newer.
- Keep `.env` private and never commit it.
- Make `storage/` and `bootstrap/cache/` writable by the web server.
- Run the app behind HTTPS and set `APP_URL` to the real HTTPS domain.

## Required `.env` Values

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_LOCALE=fa
APP_FALLBACK_LOCALE=fa
APP_FORCE_HTTPS=true
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
FILESYSTEM_DISK=public
```

## Deployment Commands

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## After Deployment

- Confirm uploaded profile photos load from `/storage/profile-photos/...`.
- Confirm dorm student documents open only after login from the student page.
- Confirm login, logout, dashboard, finance, library, rooms, students, and settings routes.
- Confirm `APP_DEBUG=false` by checking that raw exception pages are not shown to normal users.
