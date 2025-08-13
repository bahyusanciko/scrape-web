FROM php:8.2-fpm

# Install system dependencies and Node.js
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    unzip \
    git \
    curl \
    nodejs \
    npm \
    && docker-php-ext-install pdo pdo_sqlite

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy application files
COPY . /var/www

# Install Laravel PHP dependencies
RUN composer install --no-interaction --prefer-dist

# Run Laravel database migrations
RUN php artisan migrate --force

# Install and build frontend assets
RUN npm install && npm run build

# Set permissions for Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

CMD ["php-fpm"]
