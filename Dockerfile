FROM php:7-fpm-alpine

RUN set -ex \
 && echo "http://mirror1.hs-esslingen.de/pub/Mirrors/alpine/v3.4/main" > /etc/apk/repositories \
 && apk update \
 && apk upgrade --available \
 && apk add git file \
 && docker-php-ext-install opcache mbstring \
# Clean up anything else
 && rm -rf \
    /tmp/* \
    /var/tmp/* \
    /var/cache/apk/*

WORKDIR /srv/www

RUN mkdir -p cache www/repos/git www/repos/work \
 && chmod og+w cache www/repos/git www/repos/work

ADD . /srv/www

VOLUME /srv/www/cache
VOLUME /srv/www/www/repos/git
VOLUME /srv/www/www/repos/work