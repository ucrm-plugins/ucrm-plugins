#!/bin/bash

apt-get update
apt-get install -y curl gettext-base

echo "Installing UISP..."
curl -fsSL https://uisp.ui.com/v1/install > /tmp/uisp_inst.sh
bash /tmp/uisp_inst.sh --version "$UISP_VERSION"

# Grant the vagrant user access to the newly installed Docker system
usermod -aG docker vagrant
chmod 666 /var/run/docker.sock
