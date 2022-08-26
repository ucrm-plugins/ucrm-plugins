#!/usr/bin/env bash

########################################################################################################################
# PHP
########################################################################################################################

apt-get install -y software-properties-common apt-transport-https
add-apt-repository -y ppa:ondrej/php
apt-get update -y
apt-get install -y php7.4-cli php7.4-curl php7.4-xdebug php7.4-xml php7.4-zip

# TODO: Install extensions!

########################################################################################################################
# COMPOSER
########################################################################################################################

COMPOSER_SETUP=/tmp/composer-setup.php

apt-get install -y unzip

curl -sS https://getcomposer.org/installer -o "$COMPOSER_SETUP"

HASH=$(curl -sS https://composer.github.io/installer.sig)

php -r "
if (hash_file('SHA384', '/tmp/composer-setup.php') === '$HASH')
{
    echo 'Installer verified';
}
else
{
    echo 'Installer corrupt';
    unlink('/tmp/composer-setup.php');
}

echo PHP_EOL;
"

php "$COMPOSER_SETUP" --install-dir=/usr/local/bin --filename=composer && rm "$COMPOSER_SETUP"


apt-get install -y git

git config --global user.name "#{GIT_USER_NAME}"
git config --global user.email "#{GIT_USER_EMAIL}"
