# Dockerfile

# --- Stage 1: Build Frontend Assets ---
FROM php:8.3-fpm-alpine AS frontend

RUN apk add --no-cache \
    nodejs \
    npm \
    curl \
    git \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    oniguruma-dev \
    libzip-dev \
    postgresql-dev \
    mariadb-connector-c-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql bcmath zip gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build

# --- Stage 2: Application Runtime ---
FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    oniguruma-dev \
    libzip-dev \
    postgresql-client \
    postgresql-dev \
    mysql-client \
    mariadb-connector-c-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql bcmath opcache zip gd

RUN sed -i 's|^access.log = .*|access.log = /dev/null|g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's|^access.log = .*|access.log = /dev/null|g' /usr/local/etc/php-fpm.d/docker.conf \
    && echo "access.log = /dev/null" >> /usr/local/etc/php-fpm.d/zz-docker.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
