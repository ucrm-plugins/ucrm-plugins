ARG VERSION
FROM node:${VERSION}-slim

# Set the work directory
WORKDIR /usr/src/app

# ----------------------------------------------------------------------------------------------------------------------
# USERS & PERMISSIONS
# ----------------------------------------------------------------------------------------------------------------------

# Create a dedicated user
RUN usermod -a -G root node

# Change the permissions so tools like composer and robo can self-update
RUN chmod g+w /usr/local/bin

# ----------------------------------------------------------------------------------------------------------------------
# YEOMAN
# ----------------------------------------------------------------------------------------------------------------------

ENV INSIGHT_DIR=/home/node/.config/configstore

RUN npm install -g yo\
 && mkdir -p ${INSIGHT_DIR}\
 && echo "{\n    optOut: true\n}" > ${INSIGHT_DIR}/insight-yo.yml\
 && chown node:node "${INSIGHT_DIR}"/insight-yo.yml\
 && unset INSIGHT_DIR

# ----------------------------------------------------------------------------------------------------------------------
# GULP
# ----------------------------------------------------------------------------------------------------------------------

#ENV INSTALL_DIR=/usr/local/bin/robo

#RUN DEBIAN_FRONTEND=noninteractive apt-get -y update \
# && DEBIAN_FRONTEND=noninteractive apt-get -y install wget \
# && wget https://robo.li/robo.phar -O "$INSTALL_DIR" \
# && chmod +x "$INSTALL_DIR" \
# && chmod g+w "$INSTALL_DIR" \
# && unset INSTALL_DIR

RUN npm install -g gulp

# ----------------------------------------------------------------------------------------------------------------------
# CLEANUP
# ----------------------------------------------------------------------------------------------------------------------

RUN rm -rf /var/lib/apt/lists/*

# ----------------------------------------------------------------------------------------------------------------------
# ENVIRONMENT
# ----------------------------------------------------------------------------------------------------------------------

# Set environment variables
ENV PATH "/home/node/bin:$PATH"
ENV PROJECT_DIR=/usr/src/app
ENV VIRTUAL_ENV=guest

# ----------------------------------------------------------------------------------------------------------------------
# FINALIZATION
# ----------------------------------------------------------------------------------------------------------------------

# Set the user
USER node

# ----------------------------------------------------------------------------------------------------------------------
# ENTRYPOINT & COMMAND
# ----------------------------------------------------------------------------------------------------------------------

COPY docker-entrypoint /usr/local/bin/
COPY node/docker-entrypoint /usr/local/bin/docker-node-entrypoint
ENTRYPOINT [ "docker-node-entrypoint" ]
CMD [ "node" ]
