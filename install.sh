#!/bin/bash

# Snscrape Web Application Installation Script

echo "🚀 Installing Snscrape Web Application..."

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP 8.1 or higher."
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo "✅ PHP version: $PHP_VERSION"

# Check if Composer is installed
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed. Please install Composer first."
    exit 1
fi

echo "✅ Composer is installed"

# Check if Python is installed
if ! command -v python3 &> /dev/null; then
    echo "❌ Python 3 is not installed. Please install Python 3.8 or higher."
    exit 1
fi

# Check Python version
PYTHON_VERSION=$(python3 --version 2>&1 | awk '{print $2}')
echo "✅ Python version: $PYTHON_VERSION"

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-interaction

# Install NPM dependencies
echo "📦 Installing NPM dependencies..."
if command -v npm &> /dev/null; then
    npm install
else
    echo "❌ NPM is not installed. Please install Node.js and NPM."
    exit 1
fi

echo "🏗️ Building frontend assets..."
npm run build

if [ $? -ne 0 ]; then
    echo "❌ Failed to install PHP dependencies"
    exit 1
fi

# Copy environment file
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
fi

# Generate application key
echo "🔑 Generating application key..."
php artisan key:generate --no-interaction

# Create SQLite database
echo "🗄️ Setting up database..."
touch database/database.sqlite

# Run migrations
echo "🔄 Running database migrations..."
php artisan migrate --no-interaction

if [ $? -ne 0 ]; then
    echo "❌ Failed to run migrations"
    exit 1
fi


# Remove broken venv if exists
if [ -d "venv" ]; then
    echo "🗑 Removing broken virtual environment..."
    rm -rf venv
fi

# Create fresh venv
echo "📦 Creating Python virtual environment..."
pyenv install 3.11.9
pyenv local 3.11.9
python -m venv venv

# Install snscrape
echo "📦 Installing snscrape in virtual environment..."
./venv/bin/pip install --upgrade pip
./venv/bin/pip install snscrape

if [ $? -ne 0 ]; then
    echo "❌ Failed to install snscrape in venv"

else
    echo "✅ Snscrape installed successfully in ./venv"
fi

# Set permissions
echo "🔐 Setting file permissions..."
chmod -R 755 storage bootstrap/cache

echo ""
echo "🎉 Installation completed successfully!"
echo ""
echo "To start the application:"
echo "  php artisan serve"
echo ""
echo "The application will be available at: http://localhost:8000"
echo ""
