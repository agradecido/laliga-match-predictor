#!/bin/sh
set -e

if [ ! -d node_modules ] || [ ! -f node_modules/.bin/vite ]; then
    echo "Installing npm dependencies..."
    npm install
fi

exec "$@"
