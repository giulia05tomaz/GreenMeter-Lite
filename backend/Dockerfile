FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
        git \
        curl \
        libpng \
        libpng-dev \
        oniguruma-dev \
        autoconf \
        make \
        gcc \
        g++ \
        icu-dev \
        zip \
        unzip \
        bash \
        mysql-client \
    && docker-php-ext-install pdo pdo_mysql

# Copy Composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-interaction --prefer-dist && \
    php artisan key:generate || true

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]