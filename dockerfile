FROM php:8.5-fpm-alpine

# install system dependencies and nginx
RUN apk add --no-cache nginx supervisor zip unzip git libpng-dev libjpeg-turbo-dev freetype-dev shadow && docker-php-ext-install pdo pdo_mysql gd

# get composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

#set working dir
WORKDIR /var/www/html

COPY . .

RUN mkdir -p /var/log/supervisor /var/run /var/lib/nginx /var/log/nginx && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/lib/nginx /var/log/nginx

# copy nginx conf
COPY nginx.conf /etc/nginx/sites-available/default
COPY supervisor.conf /etc/supervisor/conf.d/supervisord.conf

COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# instal composer dependencies
RUN composer install --no-dev --optimize-autoloader

# expose port 80
EXPOSE 80

ENTRYPOINT ["start.sh"]