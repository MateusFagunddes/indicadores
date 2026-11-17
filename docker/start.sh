#!/bin/sh
set -e

cd /var/www/html

sed -i "s/__PORT__/${PORT:-10000}/g" /etc/nginx/conf.d/app.conf

php artisan migrate --force

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
    php artisan db:seed --force
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

php-fpm -D
exec nginx -g 'daemon off;'
