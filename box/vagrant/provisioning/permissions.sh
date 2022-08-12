#!/bin/bash

# Change directory permissions from 700, as we may need to access some deeply nested folders by another user.
chmod 775 -R /home/unms/

# Take ownership of the UCRM data folder and ALL sub-folders for SFTP access.
# NOTE: This folder maps directly to /data/ inside the UCRM container.
chown vagrant:vagrant -R /home/unms/data/ucrm/
