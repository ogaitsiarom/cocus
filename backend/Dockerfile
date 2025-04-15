FROM php:8.4.5-cli

WORKDIR /api

COPY . .

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev zip libonig-dev libxml2-dev \
    libpq-dev pkg-config

RUN docker-php-ext-install pdo pdo_pgsql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install

CMD php -S 0.0.0.0:8000 -t public
