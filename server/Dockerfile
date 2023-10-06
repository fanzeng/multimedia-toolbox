# Dockerfile
FROM php:7.2-cli

RUN apt-get update -y && apt-get install -y software-properties-common libmcrypt-dev ffmpeg
# RUN apt-add-repository ppa:ondrej/php && apt-get update -y && apt-get install php7.2-sqlite3
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN docker-php-ext-install pdo mbstring

WORKDIR /app
COPY . /app

RUN composer install

EXPOSE 8000
CMD php artisan migrate:fresh
CMD php artisan serve --host=0.0.0.0 --port=10000