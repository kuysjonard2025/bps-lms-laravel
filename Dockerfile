# Dockerfile

# --- Stage 1: Build Frontend Assets ---
FROM node:22-alpine AS frontend

WORKDIR /app

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
    libpng \
    libjpeg-turbo \
    freetype \
    libxml2 \
    oniguruma \
    libzip \
    libpq \
    mariadb-connector-c \
    postgresql-client \
    mysql-client \
    && apk add --no-cache --virtual .build-deps \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libxml2-dev \
        oniguruma-dev \
        libzip-dev \
        postgresql-dev \
        mariadb-connector-c-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo pdo_mysql pdo_pgsql bcmath opcache zip gd \
    && apk del .build-deps

# Ensure PHP-FPM listens on 127.0.0.1:9000 and disables noisy access logs
RUN sed -i 's|^listen = .*|listen = 127.0.0.1:9000|g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's|^access.log = .*|access.log = /dev/null|g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's|^access.log = .*|access.log = /dev/null|g' /usr/local/etc/php-fpm.d/docker.conf \
    && echo "access.log = /dev/null" >> /usr/local/etc/php-fpm.d/zz-docker.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

RUN rm -f /etc/nginx/http.d/*.conf
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
