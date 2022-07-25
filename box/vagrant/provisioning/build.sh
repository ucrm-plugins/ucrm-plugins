#!/bin/bash

cd /home/unms/app

# ----------------------------------------------------------------------------------------------------------------------
# docker-compose.override.yml
# ----------------------------------------------------------------------------------------------------------------------
filename=./overrides/docker-compose.override.yml

sed -i.bak -E "s/UCRM_VERSION:.*$/UCRM_VERSION: $UCRM_VERSION/g" $filename

if ! diff "$filename" "$filename.bak" &> /dev/null; then
  echo "changed"
else
  echo "not changed"
fi
rm "$filename.bak"


rm -f docker-compose.override.yml
ln -s ./overrides/docker-compose.override.yml docker-compose.override.yml

# ----------------------------------------------------------------------------------------------------------------------
# docker-compose.override.yml
# ----------------------------------------------------------------------------------------------------------------------

# Change the serverName in xdebug_params based on the current hostname
sed -i -E "s/\"serverName=[a-z_-]+\"/\"serverName=`hostname`\"/" ./overrides/ucrm/xdebug_params

# Restart UCRM while forcing a (re-)build of our custom docker image.
#docker-compose -p unms up -d --build ucrm
