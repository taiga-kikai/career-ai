#!/bin/sh
set -eu
PORT="${PORT:-10000}"
sed -ri "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf
mkdir -p /tmp
DB_FILE="${DB_DATABASE:-/tmp/career-ai.sqlite}"
mkdir -p "$(dirname "$DB_FILE")"
touch "$DB_FILE"
export DB_CONNECTION=sqlite
export DB_DATABASE="$DB_FILE"
export SESSION_DRIVER=file
export CACHE_STORE=file
export QUEUE_CONNECTION=sync
php artisan config:cache
php artisan migrate --force --seed
exec apache2-foreground