#!/bin/bash

# postgres:x:70:70:Linux User,,,:/var/lib/postgresql:/bin/sh

apt-get install -y curl gpg gnupg2 software-properties-common apt-transport-https lsb-release ca-certificates
curl -fsSL https://www.postgresql.org/media/keys/ACCC4CF8.asc | gpg --dearmor -o /etc/apt/trusted.gpg.d/postgresql.gpg
echo "deb http://apt.postgresql.org/pub/repos/apt/ `lsb_release -cs`-pgdg main" | tee  /etc/apt/sources.list.d/pgdg.list


#apt-get install -y postgresql-13 postgresql-client-13

# postgres:x:70:70:PostgreSQL administrator,,,:/var/lib/postgresql:/bin/bash

groupadd \
    --gid 70 \
    postgres

useradd \
    --home-dir /var/lib/postgresql \
    --uid 70 \
    --gid 70 \
    --no-create-home \
    --password vagrant \
    --shell /bin/bash \
    --comment "PostgreSQL Administrator,,," \
    postgres

apt-get update -y
apt-get install -y postgresql-13 postgresql-client-13

systemctl stop postgresql
systemctl disable postgresql

# NOTE: PostgreSQL environment variables should be set to allow simple client execution...
# psql -U ucrm
