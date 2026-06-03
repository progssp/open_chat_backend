#!bin/sh
set -e

# run migrations and make caches
php artisan migrate --force

echo "launching web server and queue workers"
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf