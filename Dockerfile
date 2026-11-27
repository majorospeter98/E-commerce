# syntax=docker/dockerfile:1

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --ignore-platform-reqs

FROM node:24-slim AS assets
WORKDIR /app
COPY package.json package-lock.json .npmrc ./
RUN npm install --no-audit --no-fund
COPY . .
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

FROM php:8.4-apache AS app
WORKDIR /var/www/html

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/var/www/html/storage/app/database.sqlite

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && a2enmod rewrite headers \
    && sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && printf '<Directory /var/www/html/public>\n    AllowOverride None\n    Require all granted\n    FallbackResource /index.php\n</Directory>\n' > /etc/apache2/conf-available/laravel.conf \
    && printf '<Directory /var/www/html/public/build/assets>\n    Header set Cache-Control "public, max-age=31536000, immutable"\n</Directory>\n<Directory /var/www/html/public/Teams>\n    Header set Cache-Control "public, max-age=86400, stale-while-revalidate=604800"\n</Directory>\n<FilesMatch "^(ball\\.webp|favicon\\.ico)$">\n    Header set Cache-Control "public, max-age=86400, stale-while-revalidate=604800"\n</FilesMatch>\n' >> /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

RUN mkdir -p bootstrap/cache storage/app/public storage/logs storage/framework/cache/data storage/framework/sessions storage/framework/views \
    && composer dump-autoload --no-dev --optimize \
    && ln -s ../storage/app/public public/storage \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

CMD ["sh", "-c", "touch \"$DB_DATABASE\" && php artisan migrate --force && php artisan db:seed --class=ItemSeeder --force && php artisan optimize && chown -R www-data:www-data storage bootstrap/cache && exec apache2-foreground"]
