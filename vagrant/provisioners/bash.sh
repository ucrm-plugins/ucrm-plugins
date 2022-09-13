#!/usr/bin/env bash

# The block of code to add to any existing ~/.bashrc file.
read -r -d '' BLOCK << 'EOF'

# MARKER:bash
if [ -f ~/.bash_extras ]; then
    . ~/.bash_extras
fi
EOF

# IF the block does NOT already exist, THEN add it!
if ! grep -q '# MARKER:bash' /home/vagrant/.bashrc
then
    echo "$BLOCK" >> /home/vagrant/.bashrc
fi

# Then recreate the symlink to our .bashrc as .bash_extras, for which the user's .bashrc is already looking.
rm -f /home/vagrant/.bash_extras
ln -s /src/ucrm-plugins/.bashrc /home/vagrant/.bash_extras
