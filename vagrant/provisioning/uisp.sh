#!/bin/bash

apt-get update
apt-get install -y curl gettext-base

echo "Installing UISP..."
curl -fsSL https://uisp.ui.com/v1/install > /tmp/uisp_inst.sh

bash /tmp/uisp_inst.sh \
    --version "$UISP_VERSION" \
    --ssl-cert-dir "$BOX_CERT_DIR" \
    --ssl-cert "$BOX_HOSTNAME".crt \
    --ssl-cert-key "$BOX_HOSTNAME".key

# Grant the vagrant user access to the newly installed Docker system
usermod -aG docker vagrant
chmod 666 /var/run/docker.sock
