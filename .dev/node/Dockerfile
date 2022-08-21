ARG NODE_VERSION=16.17.0
FROM node:${NODE_VERSION}

MAINTAINER Ryan Spaeth <rspaeth@spaethtech.com>

### NODE EXTENSIONS
#ENV DEBIAN_FRONTEND=noninteractive

# COMMON
#RUN apt-get update -y \
#    && apt-get install -y libbz2-dev libpng-dev libgmp-dev libicu-dev libpq-dev libxml2-dev libzip-dev \
#    && docker-php-ext-install bcmath bz2 exif gd gmp intl pdo_pgsql soap sockets sysvmsg sysvsem sysvshm opcache zip \
#    && docker-php-ext-configure opcache --enable-opcache

### ROBO
RUN npm install -g yo

### CLEANUP
#RUN rm -rf /var/lib/apt/lists/*

### WORKING
ENV NODE_WORKING_DIR=/usr/src/app
RUN mkdir -p ${NODE_WORKING_DIR}
WORKDIR ${NODE_WORKING_DIR}

RUN ln -s ${NODE_WORKING_DIR}/.dev/node/node-proxy /usr/local/bin/node-proxy

VOLUME [ "${NODE_WORKING_DIR}" ]

#ENTRYPOINT [ "node" ]
#ENTRYPOINT [ "/bin/bash" ]
#CMD [ "docker-php" ]
