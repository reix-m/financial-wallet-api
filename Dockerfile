ARG PHP_VERSION=8.5

# --- Build stage: compile PHP extensions ---
FROM php:${PHP_VERSION}-cli-bookworm AS builder

RUN apt-get update && apt-get install -y --no-install-recommends \
        $PHPIZE_DEPS \
        libpq-dev \
        libicu-dev \
        libzip-dev \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        intl \
        pcntl \
        pdo_pgsql \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

# --- Runtime stage: slim image with only compiled extensions + runtime libs ---
FROM php:${PHP_VERSION}-cli-bookworm AS runtime

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_MEMORY_LIMIT=-1

RUN apt-get update && apt-get install -y --no-install-recommends \
        libpq5 \
        libicu72 \
        libzip4 \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN docker-php-ext-enable bcmath intl pcntl pdo_pgsql redis zip

WORKDIR /var/www/html

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
