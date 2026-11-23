#!/bin/sh
set -eu

PUERTO="${PORT:-8080}"
sed -i "s/Listen 80/Listen ${PUERTO}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PUERTO}>/" /etc/apache2/sites-available/000-default.conf

php artisan package:discover --ansi
mkdir -p storage/app/private storage/app/public
chown www-data:www-data storage/app storage/app/private storage/app/public
if [ ! -e public/storage ] && [ ! -L public/storage ]; then
    php artisan storage:link
fi
php artisan migrate --force
exec apache2-foreground
