#!/usr/bin/env bash

set -e

OS_TYPE="$(uname -s)"

echo "🚀 Installing Snscrape Web Application on $OS_TYPE..."

# ==== Detect command availability ====
check_command() {
    if ! command -v "$1" >/dev/null 2>&1; then
        echo "❌ $1 is not installed. $2"
        exit 1
    fi
}

# ==== PHP ====
check_command php "Please install PHP 8.1 or higher."
PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo "✅ PHP version: $PHP_VERSION"

# ==== Composer ====
check_command composer "Please install Composer."
echo "✅ Composer is installed"

# ==== Python ====
check_command python3 "Please install Python 3.8 or higher."
PYTHON_VERSION=$(python3 --version | awk '{print $2}')
echo "ℹ️  Detected Python version: $PYTHON_VERSION"

# ==== pyenv (optional, for version switching) ====
if command -v pyenv >/dev/null 2>&1; then
    echo "✅ pyenv is installed"
    COMPAT_PY="3.11.9"
    if [[ "$PYTHON_VERSION" == 3.13* || "$PYTHON_VERSION" == 3.14* ]]; then
        echo "⚠️  Python $PYTHON_VERSION detected. Installing $COMPAT_PY..."
        if ! pyenv versions --bare | grep -q "$COMPAT_PY"; then
            pyenv install $COMPAT_PY
        fi
        pyenv local $COMPAT_PY
        PYTHON_CMD="python3"
        PYTHON_VERSION=$COMPAT_PY
    else
        PYTHON_CMD="python3"
    fi
else
    echo "ℹ️  pyenv not found. Using system Python."
    PYTHON_CMD="python3"
fi

# ==== NPM ====
check_command npm "Please install Node.js and NPM."
echo "✅ NPM is installed"

# ==== PHP dependencies ====
echo "📦 Installing PHP dependencies..."
composer install --no-interaction --prefer-dist

# ==== NPM dependencies ====
echo "📦 Installing NPM dependencies..."
npm install

echo "🏗️ Building frontend assets..."
npm run build

# ==== Laravel environment ====
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
fi

echo "🔑 Generating application key..."
php artisan key:generate --no-interaction

# ==== SQLite setup ====
mkdir -p database
touch database/database.sqlite
php artisan migrate --no-interaction

# ==== Python venv ====
rm -rf venv
echo "📦 Creating Python venv with Python $PYTHON_VERSION..."
$PYTHON_CMD -m venv venv

./venv/bin/pip install --upgrade pip setuptools wheel

# ==== Install snscrape ====
if ! ./venv/bin/pip install --no-cache-dir snscrape; then
    echo "❌ Failed to install snscrape from PyPI. Retrying from GitHub source..."
    ./venv/bin/pip install --no-cache-dir git+https://github.com/JustAnotherArchivist/snscrape.git
fi

echo "✅ Snscrape installed successfully."

# ==== Save venv path ====
if ! grep -q "VENV_PYTHON_PATH" .env; then
    echo "VENV_PYTHON_PATH=$(pwd)/venv/bin/python3" >> .env
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
