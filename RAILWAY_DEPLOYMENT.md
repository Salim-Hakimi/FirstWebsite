# Railway deployment

This Laravel project is ready to deploy on Railway for a 30-day test.

## Free trial note

As of May 5, 2026, Railway offers a 30-day free trial with a one-time $5 credit for new users. After the trial, Railway moves to the Free plan with limited monthly credit. Use this only as a manager testing environment, not production.

## Dashboard setup

1. Push this repository to GitHub.
2. Open Railway and create a new project from the GitHub repository.
3. Add a database service in the same Railway project. PostgreSQL is recommended on Railway, but MySQL also works if you want to keep the same database type you used before.
4. In the app service, set the build command:

```sh
npm run build
```

5. In the app service, set the pre-deploy command:

```sh
chmod +x ./railway/init-app.sh && sh ./railway/init-app.sh
```

6. Generate a public domain from the app service Networking tab.

## Required Railway variables

Set these variables on the app service when using PostgreSQL:

```env
APP_NAME=Fanous
APP_ENV=production
APP_KEY=base64:GENERATE_THIS_WITH_php_artisan_key_generate_show
APP_DEBUG=false
APP_URL=https://YOUR-RAILWAY-DOMAIN

APP_LOCALE=fa
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stderr
LOG_LEVEL=info

DB_CONNECTION=pgsql
DB_URL=${{Postgres.DATABASE_URL}}

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

FILESYSTEM_DISK=local
MAIL_MAILER=log
```

If you use MySQL on Railway instead, use the MySQL service variables:

```env
DB_CONNECTION=mysql
DB_URL=${{MySQL.DATABASE_URL}}
```

Generate the app key locally with:

```sh
php artisan key:generate --show
```

## Optional services

For a one-month manager test, the app service plus PostgreSQL is usually enough. If queues or scheduled tasks must run continuously, create separate Railway services using these start commands:

Worker:

```sh
chmod +x ./railway/run-worker.sh && sh ./railway/run-worker.sh
```

Cron:

```sh
chmod +x ./railway/run-cron.sh && sh ./railway/run-cron.sh
```

## Broken CSS or images checklist

If the site deploys but styles, fonts, or uploaded images are broken, check these first:

```sh
npm run build
php artisan storage:link
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

On Railway, `railway/init-app.sh` already runs `storage:link` and Laravel cache commands during pre-deploy. The most common missing setting is the build command:

```sh
npm run build
```

For uploaded profile photos and documents, the public URL depends on this symlink:

```text
public/storage -> storage/app/public
```

If you deploy on cPanel or another shared host, make sure the web root points to Laravel's `public` directory. If the web root points to the project root instead, CSS and image paths often break.
