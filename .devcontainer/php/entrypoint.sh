#!/bin/sh
set -e

if [ ! -f composer.json ]; then
    echo "No composer.json found in /var/www/html - is the project mounted correctly?"
    exit 1
fi

if [ ! -f vendor/autoload.php ]; then
    echo "Installing composer dependencies..."
    composer install --no-interaction --prefer-dist
fi

if [ ! -f .env ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
fi

if ! grep -q "^APP_KEY=base64" .env; then
    echo "Generating application key..."
    php artisan key:generate --ansi
fi

mkdir -p database
touch database/database.sqlite

echo "Running migrations..."
php artisan migrate --force

exec "$@"
