#!/bin/bash

set -e

APP_PATH="/var/www/html/antrianonline"

echo "SETUP PERMISSION"
cd $APP_PATH
sudo chown -R wa-blast-user:www-data $APP_PATH

echo "INSTALL COMPOSER"
pwd
sudo -u wa-blast-user composer install --no-dev --optimize-autoloader

#echo "Laravel migrate"
#sudo -u wa-blast-user php artisan migrate --force

echo "NODE.JS.."
pwd
sudo -u wa-blast-user env PATH="/usr/local/bin:/usr/bin:/bin:/home/wa-blast-user/.nvm/versions/node/v22.14.0/bin" npm install
sudo -u wa-blast-user env PATH="/usr/local/bin:/usr/bin:/bin:/home/wa-blast-user/.nvm/versions/node/v22.14.0/bin" npm run build

echo "CLEAR CACHE"
pwd
sudo -u wa-blast-user php artisan optimize:clear

echo "CLEAR CACHE CONFIG"
pwd
sudo -u wa-blast-user php artisan config:cache
sudo -u wa-blast-user php artisan route:cache
sudo -u wa-blast-user php artisan view:cache

#echo "Optimize"
#php artisan optimize

echo "RESTART QUEUE"
sudo -u wa-blast-user php artisan queue:restart || true

echo "FIX PERMISSION"
cd $APP_PATH
sudo chown -R wa-blast-user:www-data $APP_PATH
sudo chmod -R 775 $APP_PATH/storage $APP_PATH/bootstrap/cache

echo "FINISHED"
