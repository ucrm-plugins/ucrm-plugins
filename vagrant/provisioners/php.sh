#!/usr/bin/env bash

########################################################################################################################
# PHP
########################################################################################################################

if [ ! -f /usr/bin/php ]; then

    # ------------------------------------------------------------------------------------------------------------------
    # PHP in Docker (UCRM)
    # ------------------------------------------------------------------------------------------------------------------

    #rm -f /usr/bin/php
    #ln -s /src/ucrm-plugins/vagrant/bin/php /usr/bin/php

    # #!/usr/bin/env bash
    # docker exec \
    #     --interactive \
    #     --workdir /proxy$(pwd) \
    #     ucrm \
    #     php "$@"

    # ------------------------------------------------------------------------------------------------------------------
    # PHP from Sources
    # ------------------------------------------------------------------------------------------------------------------

    #apt-get install -y build-essential autoconf libtool bison re2c pkg-config
    #apt-get install -y libxml2-dev libsqlite3-dev
    #cd /home/vagrant/
    #curl -sSL https://www.php.net/distributions/php-7.4.26.tar.gz
    #tar -xvf php-*.tar.gz
    #cd php-*
    #./configure
    #make -j $(nproc)
    #make install

    # ------------------------------------------------------------------------------------------------------------------
    # PHP in VM (Current Version)
    # ------------------------------------------------------------------------------------------------------------------

    DEBIAN_FRONTEND=noninteractive apt-get install -y \
        software-properties-common \
        apt-transport-https

    add-apt-repository -y ppa:ondrej/php
    apt-get update

    DEBIAN_FRONTEND=noninteractive apt-get install -y \
        php7.4-cli \
        php7.4-curl \
        php7.4-mbstring \
        php7.4-xdebug \
        php7.4-xml \
        php7.4-zip

    # TODO: Install matching extensions!

fi

########################################################################################################################
# COMPOSER
########################################################################################################################

if [ ! -f /usr/local/bin/composer ]; then

    DEBIAN_FRONTEND=noninteractive apt-get install -y unzip git

    cd /tmp
    curl -sS https://getcomposer.org/installer -o composer-setup.php

    HASH=$(curl -sS https://composer.github.io/installer.sig)

    php -r "
    if (hash_file('SHA384', 'composer-setup.php') === '$HASH')
    {
        echo 'composer-setup.php verified, installing...';
    }
    else
    {
        echo 'composer-setup.php corrupt, skipping!';
        unlink('composer-setup.php');
    }
    echo PHP_EOL;
    "

    if [ -f composer-setup.php ]; then
        php composer-setup.php --install-dir=/usr/local/bin --filename=composer 1>/dev/null
        rm composer-setup.php
    fi

    if ! grep -P 'alias composer=.*' /home/vagrant/.bash_aliases; then
        echo "alias composer=\"composer --ansi\"" >> /home/vagrant/.bash_aliases
        chown vagrant:vagrant /home/vagrant/.bash_aliases
    fi

fi

########################################################################################################################
# EXTRAS
########################################################################################################################

sudo -H -u vagrant bash -c "composer global require --ansi phpunit/phpunit consolidation/robo"

read -r -d '' BLOCK << 'EOF'

# set PATH so it includes user's composer vendor/bin if it exists
if [ -d "$HOME/.config/composer/vendor/bin" ] ; then
    PATH="$HOME/.config/composer/vendor/bin:$PATH"
fi
EOF

if ! grep -q 'composer vendor/bin' /home/vagrant/.profile;
then
    echo "Configuring User's PATH..."
    echo "$BLOCK" >> /home/vagrant/.profile
fi
