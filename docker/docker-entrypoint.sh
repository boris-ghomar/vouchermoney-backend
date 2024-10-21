#!/bin/sh

# Exit immediately if a command exits with a non-zero status
set -e

# Install composer dependencies
composer install --no-dev --prefer-dist --optimize-autoloader

# Run migrations and seeders
php artisan key:generate
php artisan storage:link
php artisan migrate -n --force
php artisan migrate:fresh --seed -n

# Call the original CMD (start PHP-FPM)
exec "$@"
