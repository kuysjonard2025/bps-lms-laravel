# --- Stage 1: Build Frontend Assets ---
FROM php:8.3-fpm-alpine AS frontend

# Install Node.js, npm, git, libzip-dev, and PHP zip extension
RUN apk add --no-cache nodejs npm curl git unzip libpng-dev libxml2-dev oniguruma-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql bcmath zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy composer files and install PHP dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy package files and install frontend dependencies
COPY package*.json ./
RUN npm ci

# Copy the rest of the application source code
COPY . .

# Run the Tailwind production build
RUN npm run build

# --- Stage 2: Application Runtime ---
FROM php:8.3-fpm-alpine

# Install system dependencies, libzip-dev, and runtime packages
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    git \
    zip \
    unzip \
    libpng-dev \
    libxml2-dev \
    oniguruma-dev \
    libzip-dev \
    postgresql-dev \
    mariadb-connector-c-dev

# Install required PHP extensions (including zip)
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql bcmath opcache zip

# Disable PHP-FPM access logging
RUN sed -i 's/^access.log = .*/access.log = \/dev\/null/' /usr/local/etc/php-fpm.d/www.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application files and built frontend assets
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
