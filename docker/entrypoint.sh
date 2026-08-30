#!/bin/sh
set -e # Exit immediately if a command exits with a non-zero status

echo "===> Ensuring storage permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "===> Running database migrations..."
# Safe migration execution without wiping tables
if [ "$APP_ENV" = "production" ]; then
    php artisan migrate --force --verbose
else
    if [ "$RUN_FRESH_LOCAL_MIGRATIONS" = "true" ]; then
        php artisan migrate:fresh --force --verbose
    fi
fi

# Run seeders conditionally (Optional: pass RUN_SEEDERS=true in docker-compose/env)
if [ "$RUN_SEEDERS" = "true" ]; then
    echo "===> Running seeders..."
    php artisan db:seed --force --verbose
fi

echo "===> Caching application configurations..."
# Optimizing Laravel performance for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "===> Starting PHP-FPM background service..."
php-fpm -D

echo "===> Starting Nginx foreground service..."
exec nginx -g "daemon off;"
