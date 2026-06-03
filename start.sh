#!bin/sh
set -e

# run migrations
php /var/www/html/artisan migrate --force || echo "migration err $?"
php /var/www/html/artisan db:seed || echo "db:seed err $?"
php /var/www/html/artisan passport:keys --force || echo "passport:keys err $?"
php /var/www/html/artisan passport:client --personal --no-interaction || echo "passport:client err $?"
php /var/www/html/artisan storage:link || echo "storage:link err $?"


echo "launching web server and queue workers"
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf