ARG VERSION=16.17.0
FROM node:${VERSION}

MAINTAINER Ryan Spaeth <rspaeth@spaethtech.com>

### WORKING
ENV WORKING_DIR=/usr/src/app
RUN mkdir -p ${WORKING_DIR}
WORKDIR ${WORKING_DIR}

### NODE EXTENSIONS
#ENV DEBIAN_FRONTEND=noninteractive

# COMMON
#RUN apt-get update -y \
#    && apt-get install -y libbz2-dev libpng-dev libgmp-dev libicu-dev libpq-dev libxml2-dev libzip-dev \
#    && docker-php-ext-install bcmath bz2 exif gd gmp intl pdo_pgsql soap sockets sysvmsg sysvsem sysvshm opcache zip \
#    && docker-php-ext-configure opcache --enable-opcache

### Yeoman
RUN npm install -g yo \
    && mkdir -p /root/.config/configstore \
    && mkdir -p /root/.config/insight-nodejs \
    && chmod -R g+rwx /root /root/.config \
    && yo --no-insight
    #&& mkdir -p ${NODE_WORKING_DIR}/templates \
    #&& cd ${NODE_WORKING_DIR}/templates \
    #&& npm link
    #&& /root/.config/configstore/insight-yo.json

### Gulp
RUN npm install -g gulp


### CLEANUP
#RUN rm -rf /var/lib/apt/lists/*



RUN ln -s ${WORKING_DIR}/.dev/node/node-proxy /usr/local/bin/node-proxy

VOLUME [ "${WORKING_DIR}" ]

#ENTRYPOINT [ "node" ]
#ENTRYPOINT [ "/bin/bash" ]
#CMD [ "docker-php" ]
