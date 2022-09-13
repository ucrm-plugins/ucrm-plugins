#!/bin/bash

# Change directory permissions from 700, as we may need to access some deeply nested folders by another user.
chmod 755 -R /home/unms/

# Take ownership of the UCRM data folder and ALL sub-folders for SFTP access.
# NOTE: This folder maps directly to /data/ inside the UCRM container.
# NOTE: Since the UID/GID of vagrant and nginx are the same in this case, we no longer need to change this!
#chown vagrant:vagrant -R /home/unms/data/ucrm/{ssl,ucrm,updates}
