FROM php:8.3-apache

run apt-get update 
    && apt-get install -y --no-install-recommends git unziplibzip-dev \
    && docker-php-ext-install pdo_mysql mysqli zip \
    && a2endmod rewrite headers \
    && rm -rf /var/lib/apt/lists/* \

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY app/composer.json app/composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts --no-autoloader

COPY app/ ./
RUN composer dump-autoload --optimize --no-dev \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80