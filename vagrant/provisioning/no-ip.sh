#!/usr/bin/env bash

apt-get update && apt-get install -y gcc make

# shellcheck disable=SC2164
cd /usr/local/src/
wget http://www.noip.com/client/linux/noip-duc-linux.tar.gz
tar xf noip-duc-linux.tar.gz
# shellcheck disable=SC2164
cd noip-2.1.9-1/
echo "0" | make install

