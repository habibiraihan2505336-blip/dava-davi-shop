# Deploy Laravel ke Railway.
# PHP 8.4 karena composer.json menuntut >= 8.4.1, dengan ekstensi pdo_pgsql
# untuk PostgreSQL Supabase.
FROM php:8.4-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
        libpq-dev libonig-dev libzip-dev unzip git \
    && docker-php-ext-install pdo_pgsql pgsql mbstring bcmath zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY . .

RUN composer dump-autoload --optimize --no-dev --no-scripts \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8080

CMD ["sh", "-c", "php artisan config:cache; php artisan storage:link || true; PHP_CLI_SERVER_WORKERS=4 exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]
