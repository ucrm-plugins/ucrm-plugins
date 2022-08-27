#!/usr/bin/env bash

curl -fsSL https://code-server.dev/install.sh | sh

RUN_AS=vagrant

cat << EOF > ~/.config/code-server/config.yaml
bind-addr: 0.0.0.0:8080
auth: password
password: vagrant
cert: false
EOF

systemctl enable --now code-server@$RUN_AS
systemctl start code-server@$RUN_AS

code-server -r /src/ucrm-plugins --ignore-last-opened

# Docker fixes!
usermod -aG docker vagrant
chmod 666 /var/run/docker.sock


apt-get install -y nginx

cat << EOF > /etc/nginx/sites-available/code-server
server {
    listen 3000;
    listen [::]:3000;
    server_name $BOX_HOSTNAME;
    location / {
        proxy_pass http://localhost:8080/;
        proxy_set_header Host \$host;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection upgrade;
        proxy_set_header Accept-Encoding gzip;
    }
}
EOF

rm -f /etc/nginx/sites-enabled/default

ln -s /etc/nginx/sites-available/code-server /etc/nginx/sites-enabled/code-server

systemctl start nginx
systemctl enable nginx

#systemctl restart nginx
systemctl restart code-server@$RUN_AS
