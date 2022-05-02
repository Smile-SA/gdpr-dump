FROM php:7.3-cli-alpine

# Install required PHP extensions
RUN docker-php-ext-install pdo_mysql

# Copy PHP configuration
RUN ln -s $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini
COPY ./config/php.ini $PHP_INI_DIR/conf.d/gdpr-dump.ini

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME /tmp/composer
RUN set -ex; \
    mkdir $COMPOSER_HOME; \
    chmod 777 $COMPOSER_HOME
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/

WORKDIR /var/www/html
CMD ["sh"]
