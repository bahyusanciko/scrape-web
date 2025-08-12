#!/bin/bash

set -e  # Stop script on error

echo "🚀 Installing Snscrape Web Application..."

# ==== PHP ====
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP 8.1 or higher."
    exit 1
fi
PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo "✅ PHP version: $PHP_VERSION"

# ==== Composer ====
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed. Please install Composer."
    exit 1
fi
echo "✅ Composer is installed"

# ==== Python base check ====
if ! command -v python3 &> /dev/null; then
    echo "❌ Python 3 is not installed. Please install Python 3.8 or higher."
    exit 1
fi
PYTHON_VERSION=$(python3 --version 2>&1 | awk '{print $2}')
echo "ℹ️  Detected Python version: $PYTHON_VERSION"

# ==== pyenv check ====
if ! command -v pyenv &> /dev/null; then
    echo "❌ pyenv is not installed. Please install pyenv first."
    exit 1
fi
echo "✅ pyenv is installed"

# ==== Force compatible Python version for snscrape ====
COMPAT_PY="3.11.9"
if [[ "$PYTHON_VERSION" == 3.13* || "$PYTHON_VERSION" == 3.14* ]]; then
    echo "⚠️  Python $PYTHON_VERSION detected. This is incompatible with snscrape."
    echo "📦 Installing Python $COMPAT_PY via pyenv..."
    if ! pyenv versions --bare | grep -q "$COMPAT_PY"; then
        pyenv install $COMPAT_PY
    fi
    pyenv local $COMPAT_PY
    PYTHON_CMD="python3"
    PYTHON_VERSION=$COMPAT_PY
    echo "✅ Using Python $PYTHON_VERSION"
else
    PYTHON_CMD="python3"
    echo "✅ Current Python version is compatible."
fi

# ==== NPM ====
if ! command -v npm &> /dev/null; then
    echo "❌ NPM is not installed. Please install Node.js and NPM."
    exit 1
fi
echo "✅ NPM is installed"

# ==== PHP dependencies ====
echo "📦 Installing PHP dependencies..."
composer install --no-interaction --prefer-dist

# ==== NPM dependencies ====
echo "📦 Installing NPM dependencies..."
npm install

echo "🏗️ Building frontend assets..."
npm run build

# ==== Environment setup ====
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
fi

echo "🔑 Generating application key..."
php artisan key:generate --no-interaction

# ==== Database setup ====
echo "🗄️ Setting up database..."
mkdir -p database
touch database/database.sqlite

echo "🔄 Running database migrations..."
php artisan migrate --no-interaction

# ==== Python venv setup ====
echo "🗑 Removing old virtual environment..."
rm -rf venv

echo "📦 Creating Python virtual environment with Python $PYTHON_VERSION..."
$PYTHON_CMD -m venv venv

echo "⬆️ Upgrading pip, setuptools, and wheel..."
./venv/bin/pip install --upgrade pip setuptools wheel

# ==== Install snscrape ====
echo "📦 Installing snscrape..."
if ! ./venv/bin/pip install --no-cache-dir snscrape; then
    echo "❌ Failed to install snscrape from PyPI. Retrying from GitHub source..."
    ./venv/bin/pip install --no-cache-dir git+https://github.com/JustAnotherArchivist/snscrape.git
fi

echo "✅ Snscrape installed successfully in ./venv"

# ==== Save venv path to Laravel .env ====
if ! grep -q "VENV_PYTHON_PATH" .env; then
    echo "VENV_PYTHO_
