#!/usr/bin/env bash

DATA_DIR=/home/vagrant/.local/share/code-server
CONF_DIR=/home/vagrant/.config/code-server
#CONF_DIR=/src/ucrm-plugins/.vscode-server

if [ ! -f /usr/bin/code-server ]; then
    echo "Installing Code Server..."
    curl -fsSL https://code-server.dev/install.sh | bash
else
    echo "Code Server already installed..."
    systemctl stop code-server@vagrant
fi

echo "Killing existing processes..."
fuser -k "$BIND_PORT"/tcp > /dev/null 2>&1

echo "Resetting data directory..."
if [ -d "$DATA_DIR" ]; then
    rm -rf "$DATA_DIR"
fi

mkdir -p "$DATA_DIR"/{Machine,extensions}
mkdir -p "$CONF_DIR"

echo "Creating User's code-server config..."
cat << EOF > "$CONF_DIR"/config.yaml
bind-addr: $BIND_HOST:$BIND_PORT
auth: none
cert: $WORKSPACE/vagrant/certs/$BOX_HOSTNAME.crt
cert-key: $WORKSPACE/vagrant/certs/$BOX_HOSTNAME.key
#user-data-dir: $DATA_DIR
#ignore-last-opened: true
disable-telemetry: true
EOF

echo "Defining code-server service..."
# Fix the service definition for our configuration!
cat << EOF > /usr/lib/systemd/user/code-server.service
[Unit]
Description=code-server
After=network.target

[Service]
Type=exec
User=vagrant
ExecStart=/usr/bin/code-server --config $CONF_DIR/config.yaml $WORKSPACE
Restart=always

[Install]
WantedBy=default.target
EOF

echo "Creating Machine settings..."
cat << EOF > "$DATA_DIR"/Machine/settings.json
{
    "workbench.startupEditor": "readme"
}
EOF

# NOTE: This seems to be the only current way to set the default workspace folder on startup!
echo "Setting the default Workspace folder..."
cat << EOF > "$DATA_DIR"/coder.json
{
    "query": { "folder": "$WORKSPACE" }
}
EOF

# shellcheck disable=SC2206
# Parse provided extensions
EXTS=(${EXTENSIONS//[\[,\]\"]/})

if [ ${#EXTS[@]} -ne 0 ]; then
    ARGS="--extensions-dir $DATA_DIR/extensions --force"
    for i in "${EXTS[@]}"; do
        ARGS="$ARGS --install-extension $i"
    done

    # shellcheck disable=SC2086
    # Attempt to install any requested extensions...
    code-server $ARGS
fi

echo "Fixing permissions..."
chown vagrant:vagrant -R /home/vagrant

echo "Starting Code Server..."
systemctl enable --now code-server@vagrant
systemctl start code-server@vagrant
