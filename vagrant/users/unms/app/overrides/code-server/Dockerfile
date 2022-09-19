FROM codercom/code-server:latest

USER root

COPY config.yaml /home/coder/.config/code-server/config.yaml
COPY coder.json /home/coder/.local/share/code-server/coder.json
COPY settings.json /home/coder/.local/share/code-server/Machine/settings.json

RUN mkdir -p /src/ucrm-plugins \
 && chown coder:coder -R /src/ucrm-plugins \
 && chown coder:coder -R /home/coder

USER coder
