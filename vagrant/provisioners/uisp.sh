#!/bin/bash
# cspell:ignore gettext

apt-get update
DEBIAN_FRONTEND=noninteractive apt-get install -y curl gettext-base

echo "Installing UISP..."
curl -fsSL https://uisp.ui.com/v1/install > /tmp/uisp_inst.sh

if [ -f /home/unms/app/unms.conf ]; then
    bash /tmp/uisp_inst.sh \
        --update
else
    bash /tmp/uisp_inst.sh \
        --version "$UISP_VERSION" \
        --ssl-cert-dir "$BOX_CERT_DIR" \
        --ssl-cert "$BOX_HOSTNAME".crt \
        --ssl-cert-key "$BOX_HOSTNAME".key
        #--http-port 8080 \
        #--suspend-port 8081 \
        #--https-port 8443 \
        #--ws-port 8444
fi

# Grant the vagrant user access to the newly installed Docker system
usermod -aG docker vagrant
chmod 666 /var/run/docker.sock

# Change directory permissions from 700, as we may need to access some deeply nested folders by another user.
#chmod 775 -R /home/unms/

# Take ownership of the UCRM data folder and ALL sub-folders for SFTP access.
# NOTE: This folder maps directly to /data/ inside the UCRM container.
#chown vagrant:vagrant -R /home/unms/data/ucrm/
