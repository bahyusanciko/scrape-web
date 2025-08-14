FROM php:8.2-fpm

# 1. Install dependencies dasar + Python 3.12 (tanpa PPA)
RUN apt-get update && apt-get install -y \
    lsb-release \
    gnupg \
    libsqlite3-dev unzip git curl nodejs npm python3 python3-venv python3-dev python3-pip \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

# 2. Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 3. Buat virtual environment Python untuk snscrape
RUN python3 -m venv /var/www/venv \
    && /var/www/venv/bin/pip install --upgrade pip \
    && /var/www/venv/bin/pip install snscrape requests

# 4. Set working directory Laravel
WORKDIR /var/www

EXPOSE 9000
CMD ["php-fpm"]
