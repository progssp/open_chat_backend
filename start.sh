#!bin/sh
set -e

# run migrations
php /var/www/html/artisan migrate --force || echo "migration err $?"


echo "launching web server and queue workers"
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf