#!/bin/sh
set -e

mkdir -p storage/cache storage/ratelimit storage/logs
chown -R www-data:www-data storage

php-fpm -D
nginx -g 'daemon off;'
