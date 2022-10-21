
# On Host (PowerShell)
wsl --install Ubuntu-20.04 --set-version 2

# Download mkcert, move it to your system path, and install the local CA in your system trust store.
Invoke-WebRequest -URI https://dl.filippo.io/mkcert/latest?for=windows/amd64 -OutFile $Env:WINDIR\System32\mkcert.exe
mkcert -install
mkcert -cert-file \\$wsl\Ubuntu-20.04\root\ssl\certs\uisp.crt -key-file \\$wsl\Ubuntu-20.04\root\ssl\certs\uisp.key uisp uisp.dev




# Inside WSL

sudo tee /etc/wsl.conf > /dev/null <<'EOF'
[boot]
systemd=true

EOF

exit

# Future connections should now have systemd operational!

sudo usermod -aG docker $USER

logout

# Future connections should now not require "sudo docker ..."


# If any existing docker networks are using 172.18.251.1/24, you will need to append "--subnet X.X.X.X" to the install
# command or there will be a pool conflict.
#
# To see all of the used subnets, the following commands can be run:
#
# sudo apt-get -y install jq
# docker network inspect $(docker network ls | awk '$3 == "bridge" { print $1}') | jq -r '.[] | .Name + " " + .IPAM.Config[0].Subnet' -


# Install UISP as usual
curl -fsSL https://uisp.ui.com/v1/install > /tmp/uisp_inst.sh
sudo bash /tmp/uisp_inst.sh \
    --version 1.4.8 \
    --ssl-cert-dir ~/ssl/certs \
    --ssl-cert uisp.crt \
    --ssl-cert-key uisp.key

# NOTE: If you see 'df: /var/lib/docker: No such file or directory', then run the following and try the install again!
sudo ln -s /var/lib/docker-desktop /var/lib/docker
# sudo mkdir /var/lib/docker

# Best way to get access to the necessary files/folders of UNMS?
sudo usermod -aG root $USER
sudo chmod 0775 -R /home/unms/{app,data}
