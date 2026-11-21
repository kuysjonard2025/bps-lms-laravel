#!/bin/sh
set -e

cd /var/www/html

echo "===> Ensuring storage and cache permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "===> Waiting for database connection..."
ATTEMPTS=0
MAX_ATTEMPTS=30
until php artisan db:monitor --expect-connection="${DB_CONNECTION:-pgsql}" >/dev/null 2>&1 || php -r "
    \$host = getenv('DB_HOST') ?: '127.0.0.1';
    \$port = getenv('DB_PORT') ?: (getenv('DB_CONNECTION') === 'mysql' ? '3306' : '5432');
    \$fp = @fsockopen(\$host, (int) \$port, \$errno, \$errstr, 2);
    if (!\$fp) { exit(1); }
    fclose(\$fp);
" >/dev/null 2>&1; do
    ATTEMPTS=$((ATTEMPTS + 1))
    if [ "$ATTEMPTS" -ge "$MAX_ATTEMPTS" ]; then
        echo "Database did not become reachable in time."
        exit 1
    fi
    sleep 2
done

echo "===> Running database migrations..."
if [ "$APP_ENV" = "local" ] && [ "$RUN_FRESH_LOCAL_MIGRATIONS" = "true" ]; then
    php artisan migrate:fresh --force --verbose
else
    php artisan migrate --force --verbose
fi

if [ "$RUN_SEEDERS" = "true" ]; then
    echo "===> Running database seeders..."
    php artisan db:seed --force --verbose
fi

echo "===> Preparing Laravel caches..."
php artisan optimize:clear
if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

echo "===> Starting PHP-FPM..."
php-fpm -D

echo "===> Starting Nginx..."
exec nginx -g "daemon off;"
