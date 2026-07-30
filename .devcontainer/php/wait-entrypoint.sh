#!/bin/sh
set -e

echo "Waiting for dependencies to be installed by the app service..."
until [ -f vendor/autoload.php ]; do
    sleep 1
done

exec "$@"
