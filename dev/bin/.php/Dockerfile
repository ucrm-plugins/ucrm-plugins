ARG VERSION=7.4.26
FROM php:${VERSION}

LABEL maintainer="Ryan Spaeth <rspaeth@spaethtech.com>"

### WORKING
ENV WORKING_DIR=/opt/project
RUN mkdir -p ${WORKING_DIR}
WORKDIR ${WORKING_DIR}

### PHP EXTENSIONS
ENV DEBIAN_FRONTEND=noninteractive

# COMMON
RUN apt-get update -y \
    && apt-get install -y libbz2-dev libpng-dev libgmp-dev libicu-dev libpq-dev libxml2-dev libzip-dev \
    && docker-php-ext-install bcmath bz2 exif gd gmp intl pdo_pgsql soap sockets sysvmsg sysvsem sysvshm opcache zip \
    && docker-php-ext-configure opcache --enable-opcache

# APCU
RUN pecl install apcu \
    && docker-php-ext-enable apcu \
    #&& echo "extension=apcu.so" >> /usr/local/etc/php/php.ini \
    && echo "apc.enable_cli=1" >> /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini \
    && echo "apc.enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini

# DS
RUN pecl install ds \
    && docker-php-ext-enable ds

# XDEBUG
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

### COMPOSER
RUN apt-get update -y \
    && apt-get install -y curl git subversion unzip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

### PHPUNIT
RUN composer global require phpunit/phpunit \
    && ln -s ~/.composer/vendor/bin/phpunit /usr/local/bin/phpunit

### ROBO
RUN composer global require consolidation/robo \
    && ln -s ~/.composer/vendor/bin/robo /usr/local/bin/robo

### CLEANUP
RUN rm -rf /var/lib/apt/lists/*

### PROXY
RUN ln -s ${WORKING_DIR}/dev/bin/.php/php-proxy /usr/local/bin/php-proxy

VOLUME [ "${WORKING_DIR}" ]

ENTRYPOINT [ "php" ]
