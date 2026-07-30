#!/bin/sh
set -e

echo "Waiting for .env to be ready..."
until [ -f .env ] && grep -q "^APP_KEY=base64" .env; do
    sleep 1
done

if [ ! -d node_modules ] || [ ! -f node_modules/.bin/vite ]; then
    echo "Installing npm dependencies..."
    npm install
fi

exec "$@"
