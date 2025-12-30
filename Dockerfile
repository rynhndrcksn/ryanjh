# Build stage - for compiling assets only
FROM php:8.5-fpm-trixie AS builder

ARG APP_ENV=prod

# Install build dependencies (minimal, just what's needed for building)
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
    && APP_ENV=${APP_ENV} php bin/console asset-map:compile --env=${APP_ENV}

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

# Configure PHP (combine all configs into fewer layers)
RUN { \
    echo 'memory_limit=64M'; \
    echo 'max_execution_time=30'; \
    echo 'max_input_time=60'; \
    echo 'post_max_size=8M'; \
    echo 'upload_max_filesize=8M'; \
    echo 'expose_php=Off'; \
    echo 'display_errors=Off'; \
    echo 'log_errors=On'; \
    echo 'error_log=/proc/self/fd/2'; \
    echo ''; \
    echo '[opcache]'; \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.save_comments=1'; \
    echo 'opcache.fast_shutdown=1'; \
    echo ''; \
    echo '[apcu]'; \
    echo 'apc.enabled=1'; \
    echo 'apc.shm_size=64M'; \
    echo 'apc.enable_cli=1'; \
} > /usr/local/etc/php/conf.d/99-app.ini \
    && { \
    echo '[www]'; \
    echo 'pm = dynamic'; \
    echo 'pm.max_children = 20'; \
    echo 'pm.start_servers = 4'; \
    echo 'pm.min_spare_servers = 2'; \
    echo 'pm.max_spare_servers = 10'; \
    echo 'pm.max_requests = 500'; \
    echo 'pm.status_path = /fpm-status'; \
    echo 'ping.path = /fpm-ping'; \
    echo 'catch_workers_output = yes'; \
    echo 'decorate_workers_output = no'; \
} > /usr/local/etc/php-fpm.d/zz-docker.conf

# Copy custom PHP-FPM config
COPY docker/php-fpm.d/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf

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
