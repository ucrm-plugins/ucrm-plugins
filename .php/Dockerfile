ARG PHP_VERSION=7.4.26
FROM php:${PHP_VERSION}

MAINTAINER Ryan Spaeth <rspaeth@spaethtech.com>

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

### COMPOSER
RUN apt-get update -y \
    && apt-get install -y curl git subversion unzip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

### ROBO
RUN composer global require consolidation/robo \
    && ln -s ~/.composer/vendor/bin/robo /usr/local/bin/robo

### CLEANUP
RUN rm -rf /var/lib/apt/lists/*

### WORKING
ENV PHP_WORKING_DIR=/opt/project
RUN mkdir -p ${PHP_WORKING_DIR}
WORKDIR ${PHP_WORKING_DIR}

RUN ln -s ${PHP_WORKING_DIR}/.php/php-proxy /usr/local/bin/php-proxy

VOLUME [ "${PHP_WORKING_DIR}" ]

ENTRYPOINT [ "php" ]
#ENTRYPOINT [ "/bin/bash" ]
#CMD [ "docker-php" ]
