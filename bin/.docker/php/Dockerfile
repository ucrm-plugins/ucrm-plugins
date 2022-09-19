ARG VERSION
FROM php:${VERSION}

# Set the work directory
WORKDIR /opt/project

# ----------------------------------------------------------------------------------------------------------------------
# USERS & PERMISSIONS
# ----------------------------------------------------------------------------------------------------------------------

# Create a dedicated user
RUN useradd -ms /bin/bash -G root php

# Change the permissions so tools like composer and robo can self-update
RUN chmod g+w /usr/local/bin

# ----------------------------------------------------------------------------------------------------------------------
# EXTENSIONS
# ----------------------------------------------------------------------------------------------------------------------

RUN DEBIAN_FRONTEND=noninteractive apt-get -y update \
 && docker-php-ext-install bcmath

RUN DEBIAN_FRONTEND=noninteractive apt-get -y install libbz2-dev \
 && docker-php-ext-install bz2

RUN docker-php-ext-install exif

RUN DEBIAN_FRONTEND=noninteractive apt-get -y install zlib1g-dev libpng-dev \
 && docker-php-ext-install gd

RUN DEBIAN_FRONTEND=noninteractive apt-get -y install libgmp-dev \
 && docker-php-ext-install gmp

RUN DEBIAN_FRONTEND=noninteractive apt-get -y install libicu-dev \
 && docker-php-ext-install intl

RUN DEBIAN_FRONTEND=noninteractive apt-get -y install libpq-dev \
 && docker-php-ext-install pdo_pgsql

RUN DEBIAN_FRONTEND=noninteractive apt-get -y install libxml2-dev \
 && docker-php-ext-install soap

RUN docker-php-ext-install sockets

RUN docker-php-ext-install sysvmsg sysvsem sysvshm

RUN pecl install apcu \
 && docker-php-ext-enable apcu --ini-name docker-php-ext-apcu.ini \
 && echo "apc.enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini \
 && echo "apc.enable_cli=1" >> /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini

RUN pecl install ds \
 && docker-php-ext-enable ds

RUN DEBIAN_FRONTEND=noninteractive apt-get -y install libzip-dev \
 && docker-php-ext-install zip

RUN docker-php-ext-install opcache

RUN pecl install xdebug \
 && docker-php-ext-enable xdebug

# ----------------------------------------------------------------------------------------------------------------------
# COMPOSER
# ----------------------------------------------------------------------------------------------------------------------

ENV INSTALL_TMP=/tmp/composer

RUN DEBIAN_FRONTEND=noninteractive apt-get -y install git unzip \
 && mkdir -p "$INSTALL_TMP" \
 && php -r "copy('https://getcomposer.org/installer', '$INSTALL_TMP/composer-setup.php');" \
 && php "$INSTALL_TMP"/composer-setup.php --install-dir=/usr/local/bin --filename=composer \
 && rm "$INSTALL_TMP"/composer-setup.php \
 && unset INSTALL_TMP

# ----------------------------------------------------------------------------------------------------------------------
# ROBO
# ----------------------------------------------------------------------------------------------------------------------

ENV INSTALL_DIR=/usr/local/bin/robo

RUN DEBIAN_FRONTEND=noninteractive apt-get -y update \
 && DEBIAN_FRONTEND=noninteractive apt-get -y install wget \
 && wget https://robo.li/robo.phar -O "$INSTALL_DIR" \
 && chmod +x "$INSTALL_DIR" \
 && chmod g+w "$INSTALL_DIR" \
 && unset INSTALL_DIR

# ----------------------------------------------------------------------------------------------------------------------
# CLEANUP
# ----------------------------------------------------------------------------------------------------------------------

RUN rm -rf /var/lib/apt/lists/*

# ----------------------------------------------------------------------------------------------------------------------
# ENVIRONMENT
# ----------------------------------------------------------------------------------------------------------------------

# Set environment variables
ENV PATH "/home/php/bin:$PATH"
ENV PROJECT_DIR=/opt/project
ENV VIRTUAL_ENV=guest

# ----------------------------------------------------------------------------------------------------------------------
# FINALIZATION
# ----------------------------------------------------------------------------------------------------------------------

# Set the user
USER php

# ----------------------------------------------------------------------------------------------------------------------
# ENTRYPOINT & COMMAND
# ----------------------------------------------------------------------------------------------------------------------

COPY docker-entrypoint /usr/local/bin/
COPY php/docker-entrypoint /usr/local/bin/docker-node-entrypoint
ENTRYPOINT [ "docker-php-entrypoint" ]
CMD [ "php" ]
