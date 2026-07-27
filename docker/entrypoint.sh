#!/bin/sh

# Ensure storage permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Cache routes/views/config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations and seeders safely
php artisan migrate --force
php artisan db:seed --force

# Start application services
php-fpm -D
nginx -g "daemon off;"
