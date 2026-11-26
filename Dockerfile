FROM php:8.3-apache
RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip git libzip-dev \
    && docker-php-ext-install pdo_mysql bcmath zip \
    && a2enmod rewrite \
    && printf 'upload_max_filesize=6M\npost_max_size=8M\n' > /usr/local/etc/php/conf.d/cargas.ini \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY . .
COPY docker/sitio-apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/iniciar.sh /usr/local/bin/iniciar-chaskayni

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts \
    && mkdir -p storage/app/private storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr

EXPOSE 8080
CMD ["sh", "/usr/local/bin/iniciar-chaskayni"]