#!/bin/sh

echo "===> Ensuring storage permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "===> Clearing old config cache..."
php artisan config:clear
php artisan cache:clear

echo "===> Running migrations..."
php artisan migrate:fresh --force --verbose

echo "===> Running seeders..."
php artisan db:seed --force --verbose

echo "===> Caching route and view configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "===> Starting application services..."
php-fpm -D
nginx -g "daemon off;"
