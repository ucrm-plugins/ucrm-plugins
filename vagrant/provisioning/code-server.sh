#!/usr/bin/env bash

if [ ! -f /usr/bin/code-server ]; then
    echo "Installing Code Server..."
    curl -fsSL https://code-server.dev/install.sh | bash
else
    echo "Code Server already installed..."
fi

mkdir -p "$WORKSPACE"

echo "Configuring Code Server..."
# Fix the service definition for our configuration!
cat << EOF > /usr/lib/systemd/user/code-server.service
[Unit]
Description=code-server
After=network.target

[Service]
Type=exec
User=vagrant
ExecStart=/usr/bin/code-server --bind-addr $BIND_ADDR --disable-telemetry $WORKSPACE
Restart=always

[Install]
WantedBy=default.target
EOF

echo "Starting Code Server..."
systemctl enable --now code-server@vagrant
systemctl start code-server@vagrant
