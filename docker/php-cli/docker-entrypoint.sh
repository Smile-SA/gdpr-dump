#!/bin/bash

# Configure composer
[ ! -z "$COMPOSER_GITHUB_TOKEN" ] && echo $COMPOSER_GITHUB_TOKEN && composer config --global github-oauth.github.com $COMPOSER_GITHUB_TOKEN

exec "$@"
