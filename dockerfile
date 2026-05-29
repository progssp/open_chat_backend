FROM php:8.5-fpm-alpine

# install system dependencies and nginx
RUN apt-get update && apt-get install -y \
    nginx supervisor \
    zip \
    unzip \
    git \
    libonig-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# install extensions for laravel
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# get composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

#set working dir
WORKDIR /var/www/html

COPY . .

# copy nginx conf
COPY nginx.conf /etc/nginx/sites-available/default
COPY supervisor.conf /etc/supervisor/conf.d/supervisord.conf

RUN echo '#!/bin/sh' > /start.sh && \
    echo 'php artisan config:cache' >> /start.sh && \
    echo 'php artisan route:cache' >> /start.sh && \
    echo 'php artisan view:cache' >> /start.sh && \
    echo '/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf' >> /start.sh

# set permissions for storage and cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# instal composer dependencies
RUN composer install --no-dev --optimize-autoloader

# expose port 80
EXPOSE 80

# make start.sh executable
RUN chmod +x /start.sh

CMD ["/bin/sh","/start.sh"]