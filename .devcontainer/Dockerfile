#FROM ubuntu:20.04
FROM robertdebock/ubuntu:focal

RUN apt-get -y update && \
    apt-get -y install curl gettext-base

RUN curl -fsSL https://uisp.ui.com/v1/install > /tmp/uisp_inst.sh && bash /tmp/uisp_inst.sh
