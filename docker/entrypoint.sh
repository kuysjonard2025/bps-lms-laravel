#!/bin/sh
set -e # Exit immediately if a command exits with a non-zero status

echo "===> Ensuring storage permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "===> Running database migrations..."
# Safe migration execution without wiping tables
php artisan migrate --force --verbose

# Run seeders conditionally (Optional: pass RUN_SEEDERS=true in docker-compose/env)
if [ "$RUN_SEEDERS" = "true" ]; then
    echo "===> Running seeders..."
    php artisan db:seed --force --verbose
fi

echo "===> Caching application configurations..."
# Optimizing Laravel performance for production
if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
else
    # Ensure fresh config resolution in dev/staging environments
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
fi

echo "===> Starting PHP-FPM background service..."
php-fpm -D

echo "===> Starting Nginx foreground service..."
exec nginx -g "daemon off;"
