#!/bin/sh

# Ensure storage permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Clear stale caches FIRST
php artisan config:clear
php artisan cache:clear

# Run fresh migrations and seeders
php artisan migrate:fresh --force
php artisan db:seed --force

# Recache for production performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start application services
php-fpm -D
nginx -g "daemon off;"
