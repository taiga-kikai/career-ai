#!/bin/sh
set -eu
PORT="${PORT:-10000}"
if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY is required to start the application." >&2
    exit 1
fi
case "$APP_KEY" in
    base64:*) ;;
    *) APP_KEY="base64:${APP_KEY}" ;;
esac
export APP_KEY
sed -ri "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf
if [ -n "${DB_URL:-}" ]; then
    export DB_CONNECTION="${DB_CONNECTION:-pgsql}"
else
    export DB_CONNECTION="${DB_CONNECTION:-sqlite}"
fi
if [ "$DB_CONNECTION" = "sqlite" ]; then
    mkdir -p /tmp
    DB_FILE="${DB_DATABASE:-/tmp/career-ai.sqlite}"
    mkdir -p "$(dirname "$DB_FILE")"
    touch "$DB_FILE"
    export DB_DATABASE="$DB_FILE"
fi
export DB_SSLMODE="${DB_SSLMODE:-require}"
export SESSION_DRIVER=file
export CACHE_STORE=file
export QUEUE_CONNECTION=sync
php artisan config:cache
php artisan migrate --force --seed
exec apache2-foreground
