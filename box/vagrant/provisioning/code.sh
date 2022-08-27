#!/usr/bin/env bash

curl -fsSL https://code-server.dev/install.sh | sh

systemctl start code-server@$USER
systemctl enable --now code-server@$USER
