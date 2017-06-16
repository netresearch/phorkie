FROM php:7-apache

ENV DEBIAN_FRONTEND=noninteractive
ENV TERM=xterm

RUN apt-get update \
 && apt-get upgrade -y -o Dpkg::Options::="--force-confnew" --no-install-recommends \
 && apt-get install -y -o Dpkg::Options::="--force-confnew" --no-install-recommends \
    git file \
    --no-install-recommends \
 && docker-php-ext-install -j$(nproc) opcache mbstring \
# clean up
 && apt-get autoremove \
 && apt-get clean -y \
 && rm -rf /tmp/* \
 && rm -rf /var/tmp/* \
 && for logs in `find /var/log -type f`; do > $logs; done \
 && rm -rf /var/lib/apt/lists/* \
 && rm -f /var/cache/apt/*.bin

RUN a2enmod rewrite