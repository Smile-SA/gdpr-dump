FROM php:8.1-cli-alpine

# Install required PHP extensions
RUN docker-php-ext-install pdo_mysql

# PHP configuration
RUN ln -s $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini
COPY ./config/php.ini $PHP_INI_DIR/conf.d/gdpr-dump.ini

# Install git (for application version detection during compilation)
RUN apk add --no-cache git

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER 1
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Add application user (more elegant than changing the uid of www-data, which is a system user)
ARG APP_UID=1000
ARG APP_GID=1000
RUN set -ex; \
    addgroup --gid "$APP_GID" www; \
    adduser --uid "$APP_UID" --ingroup www --disabled-password --gecos "" www
USER www

# Create directories that may be mounted as volumes (otherwise they would be created with root permissions)
RUN mkdir -p ~/.composer

WORKDIR /var/www/html
CMD ["sh"]
