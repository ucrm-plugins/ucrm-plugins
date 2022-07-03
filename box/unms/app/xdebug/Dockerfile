ARG UCRM_VERSION=3.4.5
FROM ubnt/unms-crm:${UCRM_VERSION}

MAINTAINER Ryan Spaeth <rspaeth@spaethtech.com>

### XDEBUG
RUN  sed -i 's|$document_root;|$document_root;\n        include xdebug_params;|g' \
       /etc/nginx/available-servers/ucrm.conf \
  && apk add --update --no-cache $PHPIZE_DEPS \
  && pecl install -f xdebug \
  && docker-php-ext-enable xdebug

# Conviently, nginx used the same UID:GID as vagrant, so ownership changes are simple!
#RUN echo -n "Fixing Permissions & Ownerships..." \
#    && mkdir -p /data/{log,ssl,ucrm,updates}/  && chown -R nginx:nginx /data/ \
#    && echo "DONE!"
# NOTE: This is done in provisioning now!

COPY ./xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
COPY ./xdebug_params /etc/nginx/xdebug_params

