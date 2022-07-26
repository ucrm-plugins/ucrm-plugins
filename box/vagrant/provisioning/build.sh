#!/bin/bash

cd /home/unms/app

# ----------------------------------------------------------------------------------------------------------------------
# docker-compose.override.yml
# ----------------------------------------------------------------------------------------------------------------------
filename=./overrides/docker-compose.override.yml

sed -i.bak -E "s/UCRM_VERSION:.*$/UCRM_VERSION: $UCRM_VERSION/g" $filename

if ! diff "$filename" "$filename.bak" &> /dev/null; then
  echo "File 'docker-compose.override.yml' has been updated!"
fi

rm "$filename.bak"

# Always (re-)link
rm -f docker-compose.override.yml
ln -s ./overrides/docker-compose.override.yml docker-compose.override.yml

# ----------------------------------------------------------------------------------------------------------------------
# xdebug_params
# ----------------------------------------------------------------------------------------------------------------------
filename=./overrides/ucrm/xdebug_params

# Change the serverName in xdebug_params based on the current hostname
sed -i.bak -E "s/\"serverName=[a-z_-]+\"/\"serverName=`hostname`\"/" $filename

if ! diff "$filename" "$filename.bak" &> /dev/null; then
  echo "File 'xdebug_params' has been updated!"
fi

rm "$filename.bak"

xdebug=/home/unms/data/ucrm/log/ucrm/app/logs/xdebug.log
touch $xdebug
chmod 775 $xdebug
chown vagrant:vagrant $xdebug

# ----------------------------------------------------------------------------------------------------------------------
# Build
# ----------------------------------------------------------------------------------------------------------------------

# Restart UCRM while forcing a (re-)build of our custom docker image.
#docker-compose -p unms up -d --build ucrm
