# --- Stage 1: Build Frontend Assets ---
FROM php:8.3-fpm-alpine AS frontend

# Install Node.js, npm, and Composer in the frontend build stage
RUN apk add --no-cache nodejs npm curl
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy composer files first to install PHP dependencies for Tailwind scanning
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy package files and install frontend dependencies
COPY package*.json ./
RUN npm ci

# Copy the rest of the application source code
COPY . .

# Run the Tailwind production build (now it can successfully find /vendor)
RUN npm run build

# --- Stage 2: Application Runtime ---
FROM php:8.3-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    oniguruma-dev \
    postgresql-dev \
    mariadb-connector-c-dev

RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql bcmath opcache

# Disable PHP-FPM access logging
RUN sed -i 's/^access.log = .*/access.log = \/dev\/null/' /usr/local/etc/php-fpm.d/www.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application files
COPY . .
COPY --from=frontend /app/public/build ./public/build

# Install PHP dependencies for production
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set correct permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy Nginx and Entrypoint configs
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
