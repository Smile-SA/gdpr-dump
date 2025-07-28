FROM php:8.1-cli-alpine

# Install required PHP extensions
RUN docker-php-ext-install pdo_mysql

# Install git (for application version detection during compilation)
RUN apk add --no-cache git

# Install xdebug
RUN set -ex; \
    apk add --no-cache --virtual .build-deps $PHPIZE_DEPS linux-headers; \
    pecl install -o -f xdebug-3.4.5; \
    apk del .build-deps; \
    rm -rf /tmp/pear; \
    docker-php-ext-enable xdebug

# PHP configuration
RUN ln -s $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini
COPY ./config/php.ini $PHP_INI_DIR/conf.d/gdpr-dump.ini

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER 1
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Remove system user
RUN set -ex; \
    deluser --remove-home www-data; \
    rm -rf /var/www

# Add application user with matching uid/gid
ARG APP_UID=1000
ARG APP_GID=1000
RUN set -ex; \
    addgroup --gid "$APP_GID" www-data; \
    adduser --uid "$APP_UID" --ingroup www-data --disabled-password --gecos "" www-data

# Add application directory
RUN set -ex; \
    mkdir -p /app; \
    chown www-data:www-data /app

USER www-data

# Create directories that may be mounted as volumes (otherwise they would be created with root permissions)
RUN mkdir -p ~/.composer

WORKDIR /app
CMD ["sh"]
