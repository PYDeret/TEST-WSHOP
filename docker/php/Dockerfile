FROM php:8.2-fpm-alpine

RUN set -ex \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
    && apk add --no-cache \
        git \
        unzip \
        curl \
    && docker-php-ext-install pdo pdo_mysql \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps \
    && rm -rf /tmp/pear

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY --chown=www-data:www-data composer.json composer.lock* ./

RUN composer config audit.block-insecure false \
    && composer install --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs

COPY --chown=www-data:www-data . .

RUN composer dump-autoload --optimize

EXPOSE 9000