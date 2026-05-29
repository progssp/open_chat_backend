#!bin/sh

# run migrations and make caches
php artisan migrate --force
php artisan config:clear
php artisan route:clear

/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf