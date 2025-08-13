FROM php:8.2-fpm

WORKDIR /var/www

# Install dependencies
RUN apt-get update && apt-get install -y \
    libsqlite3-dev unzip git curl nodejs npm \
    python3 python3-pip python3-venv python3-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Buat virtual environment Python
RUN python3 -m venv /var/www/venv \
    && /var/www/venv/bin/pip install --upgrade pip \
    && /var/www/venv/bin/pip install snscrape requests \
    && echo 'source /var/www/venv/bin/activate' >> /root/.bashrc

# Copy project
COPY . .

# Install Laravel dependencies
RUN composer install --no-interaction --prefer-dist

# Install frontend dependencies
RUN npm install && npm run build

# Set permission Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

CMD ["php-fpm"]
