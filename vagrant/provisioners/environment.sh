#!/bin/bash

app=/home/unms/app
env=/src/ucrm-plugins/vagrant/env
profile=/etc/profile.d

# ----------------------------------------------------------------------------------------------------------------------
# ENVIRONMENT FILES
# ----------------------------------------------------------------------------------------------------------------------
mkdir -p $env

# Remove any stale files.
rm -f $env/{box,unms}.conf

# Copy over any information created by UISP.
cp $app/unms.conf $env/unms.conf

# Get the Box's IP information.
ipv4=$(ip addr show eth1 | grep "inet\b" | awk '{print $2}' | cut -d/ -f1)
#ipv6=$(ip addr show eth1 | grep "inet6\b" | awk '{print $2}' | cut -d/ -f1)

echo "HOSTNAME=\"$(hostname)\"" >> $env/box.conf
echo "IP=\"$ipv4\"" >> $env/box.conf

# Make sure ownership and permissions are correct.
chown -R vagrant:vagrant $env

# ----------------------------------------------------------------------------------------------------------------------
# ENVIRONMENT VARIABLES
# ----------------------------------------------------------------------------------------------------------------------

PGPASSWORD=$(awk -F "=" '/UCRM_POSTGRES_PASSWORD/ {print $2}' $app/unms.conf | tr -d '"')
PGUSER=$(awk -F "=" '/UCRM_POSTGRES_USER/ {print $2}' $app/unms.conf | tr -d '"')
PGDATABASE=$(awk -F "=" '/UCRM_POSTGRES_DB/ {print $2}' $app/unms.conf | tr -d '"')

rm -f $profile/box.sh

cat << EOF >> $profile/box.sh
export UISP_VERSION="$UISP_VERSION"
export UCRM_VERSION="$UCRM_VERSION"
export UISP_ENVIRONMENT="development"
export PGPASSWORD="$PGPASSWORD"
export PGUSER="$PGUSER"
export PGDATABASE="$PGDATABASE"
export PGHOST="localhost"
export ENVIRONMENT="guest"
EOF

# Double check permissions and source the new values.
chown root:root $profile/box.sh
chmod +x $profile/box.sh

# shellcheck disable=SC1091
source $profile/box.sh
