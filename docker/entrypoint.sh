# docker/entrypoint.sh
#!/bin/sh
set -e

echo "===> Ensuring storage directories and permissions..."
mkdir -p \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/framework/cache \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "===> Running database migrations..."
if [ "$APP_ENV" = "local" ] && [ "$RUN_FRESH_LOCAL_MIGRATIONS" = "true" ]; then
    php artisan migrate:fresh --force --verbose
else
    php artisan migrate --force --verbose
fi

echo "===> Running seeders..."
php artisan db:seed --force --verbose

echo "===> Refreshing and caching application configurations..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "===> Starting PHP-FPM background service..."
php-fpm -D

echo "===> Starting Nginx foreground service..."
exec nginx -g "daemon off;"
