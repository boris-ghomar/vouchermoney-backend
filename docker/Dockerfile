# Use an official PHP image with extensions needed for Laravel
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libzip-dev \
    libjpeg-dev \
    libpng-dev \
    libfreetype6-dev \
    libxml2-dev \
    zip \
    unzip \
    libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql zip gd intl

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy the entire application into the container
COPY .. .

# Set build-time arguments for Nova credentials (ARG only for build stage)
ARG NOVA_LICENSE_USERNAME
ARG NOVA_LICENSE_KEY

# Configure Composer with Nova credentials and install dependencies
RUN composer config http-basic.nova.laravel.com "$NOVA_LICENSE_USERNAME" "$NOVA_LICENSE_KEY" \
    && composer install --no-dev --prefer-dist --optimize-autoloader --ignore-platform-reqs

# Set permissions for Laravel
RUN chown -R www-data:www-data . \
    && chmod -R 755 storage

# Make sure the entrypoint script is executable (if not already done)
RUN chmod +x ./docker/docker-entrypoint.sh

# Use the entrypoint script located in the project
ENTRYPOINT ["./docker/docker-entrypoint.sh"]

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Start PHP-FPM server
CMD ["php-fpm"]
