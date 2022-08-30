#!/usr/bin/env bash

if [ ! -f /usr/bin/code-server ]; then
    echo "Installing Code Server..."
    curl -fsSL https://code-server.dev/install.sh | bash
else
    echo "Code Server already installed..."
fi

mkdir -p /home/vagrant/.config/code-server

cat << EOF > /home/vagrant/.config/code-server/config.yaml
bind-addr: 0.0.0.0:8443
auth: none
password: vagrant
cert: /src/ucrm-plugins/vagrant/certs/$BOX_HOSTNAME.crt
cert-key: /src/ucrm-plugins/vagrant/certs/$BOX_HOSTNAME.key
EOF

chown vagrant:vagrant -R /home/vagrant

systemctl stop code-server@vagrant

echo "Configuring Code Server..."
# Fix the service definition for our configuration!
cat << EOF > /usr/lib/systemd/user/code-server.service
[Unit]
Description=code-server
After=network.target

[Service]
Type=exec
User=vagrant
ExecStart=/usr/bin/code-server --disable-telemetry $WORKSPACE
Restart=always

[Install]
WantedBy=default.target
EOF

cat << EOF > /home/vagrant/.local/share/code-server/Machine/settings.json
{
    "workbench.startupEditor": "none"
}
EOF

sed -i "s/\"query\": {},/\"query\": {\n    \"folder\": \"$WORKSPACE\"\n  },/g" /home/vagrant/.local/share/code-server/coder.json

echo "Starting Code Server..."
systemctl enable --now code-server@vagrant
systemctl start code-server@vagrant
