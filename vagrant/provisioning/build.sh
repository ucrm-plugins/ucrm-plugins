#!/bin/bash

set -e
cd /home/unms/app

########################################################################################################################
# VERSIONING
########################################################################################################################

FOUND_UISP=$(sed -e 's/^"//' -e 's/"$//' <<< $(awk -F "=" '/VERSION/ {print $2}' unms.conf))

echo -n "Found: UISP $FOUND_UISP "
[[ "$UISP_VERSION" == "$FOUND_UISP" ]] && echo -n "(match with " || echo -n "(overriding "
echo "provided UISP_VERSION: $UISP_VERSION)"

if [[ $FOUND_UISP =~ ([0-9]+).([0-9]+).([0-9]+) ]]; then
    BUILD_UCRM=$(echo "${BASH_REMATCH[1]} + 2" | bc)".${BASH_REMATCH[2]}.${BASH_REMATCH[3]}"
else
    echo "Could not determine the version of UCRM to build!"
    exit 1
fi

echo -n "Build: UCRM $BUILD_UCRM "
[[ "$UCRM_VERSION" == "$BUILD_UCRM" ]] && echo -n "(match with " || echo -n "(overriding "
echo "provided UCRM_VERSION: $UCRM_VERSION)"

########################################################################################################################
# OVERRIDES
########################################################################################################################

ln -s /src/ucrm-plugins/vagrant/unms/app/overrides overrides

compose=/src/ucrm-plugins/vagrant/unms/app/docker-compose.override.yml


#sed -i.bak -E "s/UCRM_VERSION:.*$/UCRM_VERSION: $BUILD_UCRM/g" $compose
sed -i.bak -E "s/(UCRM_VERSION|ubnt\/unms-crm)(: ?)[0-9]+\.[0-9]+\.[0-9]+(-xdebug)?$/\1\2$BUILD_UCRM\3/g" $compose

if ! diff "$compose" "$compose.bak" &> /dev/null; then
  echo "File 'docker-compose.override.yml' has been updated!"
fi

rm "$compose.bak"

# Always (re-)link
rm -f docker-compose.override.yml
ln -s $compose docker-compose.override.yml

########################################################################################################################
# xdebug_params
########################################################################################################################

params=./overrides/ucrm/xdebug_params

# Change the serverName in xdebug_params based on the current hostname
sed -i.bak -E "s/\"serverName=[a-z_-]+\"/\"serverName=`hostname`\"/" $params

if ! diff "$params" "$params.bak" &> /dev/null; then
  echo "File 'xdebug_params' has been updated!"
fi

rm "$params.bak"

########################################################################################################################
# xdebug.log
########################################################################################################################
xdebug_log=/home/unms/data/ucrm/log/ucrm/app/logs/xdebug.log

touch $xdebug_log
chmod 775 $xdebug_log
chown vagrant:vagrant $xdebug_log

########################################################################################################################
# Build
########################################################################################################################

# WATCH: Caching currently takes around 4-5 times longer than the actual build process!
#cache_dir=/home/vagrant/docker
#if [ -f $cache_dir/unms-crm.tar.gz ]; then
#    echo "Loading previously saved image..."
#    docker load < $cache_dir/unms-crm.tar.gz
#fi

# Restart UCRM while forcing a (re-)build of our custom docker image.
docker-compose -p unms up -d --build ucrm

# cf55d8617d95
#IMAGES=`docker images ubnt/unms-crm | awk '{print $1}' | tail -n +2`
#$docker save $(echo ${IMAGES[@]}) | gzip > $cache_dir/unms-crm.tar.gz
