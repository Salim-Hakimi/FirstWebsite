#!/usr/bin/env sh
set -e

php artisan queue:work --tries=3 --timeout=90
