#!/bin/bash

echo "Updating root password..."
echo "root:$ROOT_PASSWORD" | sudo chpasswd


# read -r -d '' BLOCK << 'EOF'

# # PROVISIONER:user ~/bin
# if [ -d "$HOME/.config/composer/vendor/bin" ] ; then
#     PATH="$HOME/.config/composer/vendor/bin:$PATH"
# fi
# # PROVISIONER:user
# EOF

# # # *PROVISIONER:([A-Za-z_-]+) +([^ \r\n]+).*# *PROVISIONER:\1

# if ! grep -q '# PROVISIONER:user' /home/vagrant/.profile;
# then
#     echo "Configuring User's PATH..."
#     echo "$BLOCK" >> /home/vagrant/.profile
# fi
