#!/bin/bash

# Crete the shared env folder, if it does not already exist.
mkdir -p /home/vagrant/env/

# Remove any stale env files.
rm -f /home/vagrant/env/{box,unms}.conf

# Copy over the env information created by UISP.
cp /home/unms/app/unms.conf /home/vagrant/env/unms.conf

# Create an ENV file for box specific information.

IPV4=`ip addr show eth1 | grep "inet\b" | awk '{print $2}' | cut -d/ -f1`
IPV6=`ip addr show eth1 | grep "inet6\b" | awk '{print $2}' | cut -d/ -f1`

echo 'HOSTNAME="'`hostname`'"' >> /home/vagrant/env/box.conf
echo 'IP="IPV4"' >> /home/vagrant/env/box.conf

# Make sure ownership and permissions are correct.
chown -R vagrant:vagrant /home/vagrant/env/

# We also set some ENV variables for use on the guest itself.
rm -f /etc/profile.d/box.sh
echo "export UISP_VERSION=\"$UISP_VERSION\"" >> /etc/profile.d/box.sh
echo "export UCRM_VERSION=\"$UCRM_VERSION\"" >> /etc/profile.d/box.sh
echo "export UISP_ENVIRONMENT=\"development\"" >> /etc/profile.d/box.sh
#echo "export COMPOSER_ALLOW_SUPERUSER=1"       >> /etc/profile.d/box.sh
# ... Add any other system-wide environment variables here!

# Double check permissions and source the new values.
chown root:root /etc/profile.d/box.sh
chmod +x /etc/profile.d/box.sh
source /etc/profile.d/box.sh
