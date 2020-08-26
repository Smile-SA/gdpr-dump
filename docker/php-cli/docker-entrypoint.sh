#!/bin/bash

set -e

if [[ "$UPDATE_UID_GID" -eq 1 ]]; then
    echo "Updating user uid and gid"

    DOCKER_UID=`stat -c "%u" /app`
    DOCKER_GID=`stat -c "%g" /app`

    INCUMBENT_USER=`getent passwd $DOCKER_UID | cut -d: -f1`
    INCUMBENT_GROUP=`getent group $DOCKER_GID | cut -d: -f1`

    # Once we've established the ids and incumbent ids then we need to free them
    # up (if necessary) and then make the change to $CLI_USER

    [ ! -z "$INCUMBENT_USER" ] && usermod -u 99$DOCKER_UID $INCUMBENT_USER
    usermod -u $DOCKER_UID cli

    [ ! -z "$INCUMBENT_GROUP" ] && groupmod -g 99$DOCKER_GID $INCUMBENT_GROUP
    groupmod -g $DOCKER_GID cli
fi

# Configure composer
[ ! -z "$COMPOSER_GITHUB_TOKEN" ] && \
    composer config --global github-oauth.github.com $COMPOSER_GITHUB_TOKEN

exec "$@"
