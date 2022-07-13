MAINTAINER Ryan Spaeth <rspaeth@spaethtech.com>

# NOTE: The version here should probably be bumped when new versions become available, but the end-user developer can
# always specifiy the specific version they would like when building on their local machines.
ARG UCRM_VERSION=3.4.5

# Start with UI's UCRM image.
FROM ubnt/unms-crm:${UCRM_VERSION}

# We over-specify PHPIZE_DEPS to ensure we have the correct dependencies and to remove any warnings from our IDE.
ENV PHPIZE_DEPS="autoconf dpkg-dev dpkg file g++ gcc libc-dev make pkgconf re2c"

# We include some logic to prevent warnings, as this may be a re-build and Xdebug may already be installed.
RUN cd /etc/nginx/available-servers ; \
    sed -i '/        include xdebug_params;/d' ucrm.conf ; \
    sed -i 's|\$document_root;|\$document_root;\n        include xdebug_params;|g' ucrm.conf ; \
    if ! pecl list | grep xdebug >/dev/null 2>&1; then apk add --update --no-cache $PHPIZE_DEPS; fi ; \
    if ! pecl list | grep xdebug >/dev/null 2>&1; then pecl install xdebug; fi ; \
    if ! php -m    | grep xdebug >/dev/null 2>&1; then docker-php-ext-enable xdebug; fi

# NOTE: Permissions are now handled in the Vagrant provisioners, but it is also almost completely unnecessary, as it so
# happens our "nginx" UID:GID in the container is also the same as the "unms" UID:GID on the host VM.

# Copy over any custom Xdebug settings.
COPY ./xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
COPY ./xdebug_params /etc/nginx/xdebug_params

