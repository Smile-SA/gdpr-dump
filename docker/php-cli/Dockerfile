FROM php:7.3-cli

# Install dependencies (unzip is used by composer)
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        sudo \
        unzip

# Install required PHP extensions
RUN docker-php-ext-install pdo_mysql

ENV UPDATE_UID_GID 1

# Set entry point
ADD docker-entrypoint.sh /docker-entrypoint.sh
RUN ["chmod", "+x", "/docker-entrypoint.sh"]
ENTRYPOINT ["/docker-entrypoint.sh"]

# Create a user with the same UID/GID as the host
ARG CLI_USER=cli
RUN addgroup $CLI_USER
RUN adduser --disabled-password --gecos "" --ingroup $CLI_USER $CLI_USER
RUN adduser $CLI_USER sudo

# Set up composer cache directory
RUN mkdir -p /home/$CLI_USER/.composer/cache
RUN chown -R $CLI_USER:$CLI_USER /home/$CLI_USER/.composer
VOLUME /home/$CLI_USER/.composer/cache

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_GITHUB_TOKEN ""
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer.phar

# Copy scripts to /usr/local/bin
ADD bin/* /usr/local/bin/
RUN ["chmod", "+x", "/usr/local/bin/composer"]
RUN ["chmod", "+x", "/usr/local/bin/run-sniffers"]
RUN ["chmod", "+x", "/usr/local/bin/run-tests"]

WORKDIR /app
CMD ["bash"]
