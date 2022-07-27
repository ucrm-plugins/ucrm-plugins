#!/bin/bash

app=/home/unms/app
env=/home/vagrant/env
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
ipv4=`ip addr show eth1 | grep "inet\b" | awk '{print $2}' | cut -d/ -f1`
ipv6=`ip addr show eth1 | grep "inet6\b" | awk '{print $2}' | cut -d/ -f1`

echo 'HOSTNAME="'`hostname`'"' >> $env/box.conf
echo "IP=\"$ipv4\"" >> $env/box.conf

# Make sure ownership and permissions are correct.
chown -R vagrant:vagrant $env

# ----------------------------------------------------------------------------------------------------------------------
# ENVIRONMENT VARIABLES
# ----------------------------------------------------------------------------------------------------------------------
rm -f $profile/box.sh
echo "export UISP_VERSION=\"$UISP_VERSION\""    >> $profile/box.sh
echo "export UCRM_VERSION=\"$UCRM_VERSION\""    >> $profile/box.sh
echo "export UISP_ENVIRONMENT=\"development\""  >> $profile/box.sh

# FUTURE: Add any other system-wide environment variables here!

# Double check permissions and source the new values.
chown root:root $profile/box.sh
chmod +x $profile/box.sh
source $profile/box.sh
