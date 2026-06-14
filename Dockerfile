############################################
# Base Image
############################################
FROM serversideup/php:8.5-fpm-nginx AS base

############################################
# Development Image
############################################
FROM base AS development

USER root

ARG USER_ID=1000
ARG GROUP_ID=1000

RUN docker-php-serversideup-set-id www-data "${USER_ID}:${GROUP_ID}" && \
    docker-php-serversideup-set-file-permissions --owner "${USER_ID}:${GROUP_ID}" && \
    install-php-extensions intl

USER www-data
