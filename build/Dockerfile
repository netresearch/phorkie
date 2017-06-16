FROM php:7-alpine

RUN set -x \
 && echo "http://mirror1.hs-esslingen.de/pub/Mirrors/alpine/latest-stable/main" > /etc/apk/repositories \
 && apk update \
 && apk upgrade --available \
 && apk add bash \
# Clean up anything else
 && rm -rf \
    /tmp/* \
    /var/tmp/* \
    /var/cache/apk/*

RUN mkdir -p cache www/repos/git www/repos/work \
 && chmod og+w cache www/repos/git www/repos/work

#
# composer
#
# https://getcomposer.org/download/
# https://getcomposer.org/doc/faqs/how-to-install-composer-programmatically.md
#
ADD install-composer.sh /install-composer.sh
RUN /install-composer.sh \
 && mv composer.phar /usr/bin/composer \
 && rm /install-composer.sh

ADD build /usr/bin/build
ADD INFO /INFO

ENTRYPOINT []
CMD ["cat", "/INFO"]