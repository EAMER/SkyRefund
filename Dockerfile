FROM php:8.3-fpm-alpine

RUN apk add --no-cache oniguruma-dev libxml2-dev zip unzip curl git bash \
    && docker-php-ext-install pdo pdo_mysql opcache \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts

COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

CMD ["php-fpm"]
