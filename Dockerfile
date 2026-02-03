# Build stage - for compiling assets only
FROM php:8.5-fpm-trixie AS builder

ARG APP_ENV=prod
ARG APP_DEBUG=0

# Install build dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    autoconf \
    g++ \
    make \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) pdo pdo_pgsql zip intl \
    && pecl channel-update pecl.php.net \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && apt-get purge -y --auto-remove autoconf g++ make \
    && rm -rf /var/lib/apt/lists/* \
    && rm -rf /tmp/pear \
    && apt-get clean

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Install all dependencies (including dev for build tools)
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-scripts --no-progress --prefer-dist

# Copy app and compile assets
COPY . .
RUN APP_ENV=${APP_ENV} php bin/console importmap:install --env=${APP_ENV} \
    && APP_ENV=${APP_ENV} php bin/console asset-map:compile --env=${APP_ENV} \
    && APP_ENV=${APP_ENV} php bin/console assets:install public --env=${APP_ENV}

# Warmup the cache so routing, templates, DI container, etc. are ready and baked into the image.
APP_ENV=${APP_ENV} php bin/console cache:warmup --env=${APP_ENV}

# Remove dev dependencies (keep compiled assets)
RUN composer install --no-dev --no-scripts --optimize-autoloader --classmap-authoritative

# Production stage
FROM php:8.5-fpm-trixie

# Install runtime libs + build deps for PECL, then clean up in one layer
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) pdo pdo_pgsql zip intl \
    && pecl channel-update pecl.php.net \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && rm -rf /var/lib/apt/lists/*

# Copy custom php.ini file
COPY infra/php/php.ini /usr/local/etc/php/conf.d/99-app.ini

# Copy custom PHP-FPM config
COPY infra/docker/php-fpm.d/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf

WORKDIR /app

# Copy production app
COPY --from=builder /app /app

# Set permissions
RUN mkdir -p var/cache var/log \
    && chown -R www-data:www-data var \
    && chmod -R 775 var

HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD php-fpm -t || exit 1

USER www-data

EXPOSE 9000

CMD ["php-fpm", "-F"]
