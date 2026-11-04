FROM composer:2 AS composer-deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts --optimize-autoloader

FROM node:26-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

FROM php:8.5-apache
RUN apt-get update && apt-get install -y --no-install-recommends libpq-dev libsqlite3-dev libzip-dev unzip && docker-php-ext-install pdo_pgsql pdo_sqlite zip && a2enmod rewrite headers && sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf && rm -rf /var/lib/apt/lists/*
WORKDIR /var/www/html
COPY . .
COPY --from=composer-deps /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build
RUN chown -R www-data:www-data storage bootstrap/cache && chmod +x docker-entrypoint.sh && sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
ENTRYPOINT ["./docker-entrypoint.sh"]
