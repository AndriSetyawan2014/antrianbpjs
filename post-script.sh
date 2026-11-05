#!/bin/bash

set -e

APP_PATH="/var/www/html/antrianonline"

echo "========================================"
echo "  DEPLOYMENT ANTRIAN BPJS ONLINE"
echo "========================================"

echo ""
echo "[1/8] SETUP PERMISSION"
cd $APP_PATH
sudo chown -R wa-blast-user:www-data $APP_PATH

echo ""
echo "[2/8] INSTALL COMPOSER"
sudo -u wa-blast-user composer install --no-dev --optimize-autoloader

echo ""
echo "[3/8] INSTALL NODE.JS DEPENDENCIES"
sudo -u wa-blast-user bash -c 'export PATH="/usr/local/bin:/usr/bin:/bin:/home/wa-blast-user/.nvm/versions/node/v22.14.0/bin" && cd /var/www/html/antrianonline && npm install'

echo ""
echo "[4/8] BUILD VITE ASSETS (CSS + JS)"
sudo -u wa-blast-user bash -c 'export PATH="/usr/local/bin:/usr/bin:/bin:/home/wa-blast-user/.nvm/versions/node/v22.14.0/bin" && cd /var/www/html/antrianonline && npm run build'

# Verifikasi manifest.json ada (mencegah error Vite manifest not found)
if [ ! -f "$APP_PATH/public/build/manifest.json" ]; then
    echo "ERROR: manifest.json tidak ditemukan setelah npm run build!"
    exit 1
fi
echo "✓ manifest.json tersedia"

echo ""
echo "[5/8] CLEAR LARAVEL CACHE"
sudo -u wa-blast-user php artisan optimize:clear
sudo -u wa-blast-user php artisan view:clear

echo ""
echo "[6/8] CACHE CONFIG & ROUTE & VIEW"
sudo -u wa-blast-user php artisan config:cache
sudo -u wa-blast-user php artisan route:cache
sudo -u wa-blast-user php artisan view:cache

echo ""
echo "[7/8] RESTART QUEUE"
sudo -u wa-blast-user php artisan queue:restart || true

echo ""
echo "[8/8] FIX PERMISSION"
cd $APP_PATH
sudo chown -R wa-blast-user:www-data $APP_PATH
sudo chmod -R 775 $APP_PATH/storage $APP_PATH/bootstrap/cache

echo ""
echo "========================================"
echo "  DEPLOYMENT SELESAI"
echo "========================================"