#!/bin/bash

# Disable IPv6 Rules
sed -i "s/IPV6=yes/IPV6=no/g" /etc/default/ufw

# Restart UFW for above changes
ufw --force disable
ufw --force enable

# Set defaults...
ufw default deny incoming
ufw default allow outgoing

# Allow SSH
ufw allow ssh

# Allow HTTP
ufw allow http

# Allow HTTPS
ufw allow https

# Allow NetFlow
ufw allow 2055/udp

# Allow PostgreSQL
ufw allow 5432/tcp

# Allow Code Server
ufw allow 8080/tcp

# Allow Xdebug
ufw allow 9003/tcp
