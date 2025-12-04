FROM php:8.2-fpm

WORKDIR /var/www/html

COPY . /var/www/html

RUN apt-get update && apt-get install -y --no-install-recommends \
    libcurl4-openssl-dev \
    nginx-light \
    && docker-php-ext-install curl \
    && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader

RUN mkdir -p storage/cache storage/ratelimit storage/logs && chown -R www-data:www-data storage

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]
