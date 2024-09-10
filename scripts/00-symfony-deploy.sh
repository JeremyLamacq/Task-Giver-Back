#!/usr/bin/env bash
echo "Running composer"
composer install --no-dev --optimize-autoloader

echo "Clearing cache..."
php bin/console cache:clear --env=prod

echo "Installing assets..."
php bin/console assets:install --symlink

echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
