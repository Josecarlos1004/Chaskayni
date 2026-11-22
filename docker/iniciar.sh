#!/bin/sh
set -eu

PUERTO="${PORT:-8080}"
sed -i "s/Listen 80/Listen ${PUERTO}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PUERTO}>/" /etc/apache2/sites-available/000-default.conf

php artisan package:discover --ansi
php artisan migrate --force
exec apache2-foreground