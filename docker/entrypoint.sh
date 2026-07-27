#!/bin/sh

# Cache configurations and views for production speed
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations automatically on deploy
php artisan migrate --force

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
nginx -g "daemon off;"
