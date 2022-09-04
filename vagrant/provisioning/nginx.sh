#!/usr/bin/env bash


apt-get install -y nginx

rm -f /etc/nginx/sites-enabled/default
rm -f /etc/nginx/sites-available/default

# cat << EOF > /etc/nginx/sites-available/"$BOX_HOSTNAME"
# server {
#     listen 80;
#     server_name $BOX_HOSTNAME;
#     return 301 https://\$server_name\$request_uri;
# }
# EOF
#ln -s /etc/nginx/sites-available/uisp-dev /etc/nginx/sites-enabled/"$BOX_HOSTNAME"

cat << EOF > /etc/nginx/sites-available/"$BOX_HOSTNAME"-ssl
server {
    listen 443 ssl default_server;
    server_name $BOX_HOSTNAME;

    ssl_certificate     /src/ucrm-plugins/vagrant/certs/$BOX_HOSTNAME.crt;
    ssl_certificate_key /src/ucrm-plugins/vagrant/certs/$BOX_HOSTNAME.key;

    location / {
        proxy_pass https://localhost:8443;
        proxy_set_header Host \$host;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection upgrade;
    }

    location /code {
        proxy_pass http://localhost:3000;
        proxy_set_header Host \$host;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection upgrade;
        proxy_set_header Accept-Encoding gzip;
    }

}
EOF

ln -s /etc/nginx/sites-available/uisp-dev-ssl /etc/nginx/sites-enabled/"$BOX_HOSTNAME"-ssl


systemctl enable nginx
systemctl start nginx
