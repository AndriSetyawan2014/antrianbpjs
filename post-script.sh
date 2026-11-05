#!/bin/bash

set -e

APP_PATH="/var/www/html/antrianonline"

cd $APP_PATH

echo "Install composer"
composer install --no-dev --optimize-autoloader

echo "Laravel migrate"
php artisan migrate --force

echo "Clear cache"
php artisan optimize:clear

echo "Cache config"
php artisan config:cache
php artisan route:cache
php artisan view:cache

#echo "Optimize"
#php artisan optimize

echo "Restart queue"
php artisan queue:restart || true

echo "Fix permission"
sudo chown -R wa-blast-user:www-data storage bootstrap/cache
sudo chown -R wa-blast-user:www-data /var/www/html/antrianonline

echo "Deploy finished"
