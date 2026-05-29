#!bin/bash

# run migrations and make caches
cd /var/www/html
php artisan migrate --force
php artisan config:clear
php artisan route:clear

# start nginx in
service nginx start

# start php-fpm in
php-fpm