FROM php:7.3-cli

# Install dependencies (unzip is used by composer)
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        unzip

# Install required PHP extensions
RUN docker-php-ext-install pdo_mysql

# Copy PHP configuration
COPY config/php.ini /usr/local/etc/php/conf.d/gdpr-dump.ini

# Create a user with access to the application
ARG APP_USER=app
ARG APP_GROUP=app
ARG APP_UID=1000
ARG APP_GID=1000
RUN groupadd -g "$APP_GID" $APP_GROUP; \
    useradd -g "$APP_GID" -u "$APP_UID" -s /bin/bash $APP_USER

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME /composer
ENV COMPOSER_GITHUB_TOKEN ""
RUN mkdir -p $COMPOSER_HOME/cache; \
    chown -R $APP_USER:$APP_GROUP $COMPOSER_HOME
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy scripts to /usr/local/bin
ADD bin/* /usr/local/bin/
RUN ["chmod", "+x", "/usr/local/bin/run-sniffers"]
RUN ["chmod", "+x", "/usr/local/bin/run-tests"]

# Set entry point
ADD docker-entrypoint.sh /docker-entrypoint.sh
RUN ["chmod", "+x", "/docker-entrypoint.sh"]
ENTRYPOINT ["/docker-entrypoint.sh"]

USER $APP_USER
WORKDIR /app
CMD ["bash"]
