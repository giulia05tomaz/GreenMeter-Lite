FROM php:8.3-cli-alpine AS runtime

RUN apk add --no-cache icu-dev libzip-dev unzip \
    && docker-php-ext-install intl pdo_mysql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
EXPOSE 8000

FROM runtime AS development
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts
COPY . .
RUN composer dump-autoload --optimize
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

FROM dunglas/frankenphp:1-php8.3-alpine AS production

RUN install-php-extensions intl pdo_mysql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
ENV APP_ENV=production
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts --optimize-autoloader
COPY . .
COPY Caddyfile /etc/frankenphp/Caddyfile
RUN composer dump-autoload --no-dev --optimize \
    && chown -R www-data:www-data storage bootstrap/cache
EXPOSE 8000
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
