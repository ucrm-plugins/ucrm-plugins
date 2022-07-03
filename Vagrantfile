# -*- mode: ruby -*-
# vi: set ft=ruby :

require "rbconfig"

Vagrant.configure("2") do |config|

    # Override the following defaults as desired:
    VBOX_ADDRESS        = "192.168.50.10"
    VBOX_ROOT_PASSWORD  = "vagrant"
    UISP_VERSION        = "1.4.5"

    # It seems that the UCRM version is ALWAYS exactly 2 major versions ahead.
    UCRM_VERSION = UISP_VERSION.gsub(/^(\d+)/) { |capture| (capture.to_i + 2).to_s }

    # Set the VM's network type to private and assign a static IP address.
    config.vm.network "private_network", ip: "#{VBOX_ADDRESS}"

    # Forward the necessary ports to the box.
    config.vm.network "forwarded_port", guest: 80, host: 80, host_ip: "127.0.0.1"
    config.vm.network "forwarded_port", guest: 443, host: 443, host_ip: "127.0.0.1"

    # Including our newly exposed PostgreSQL port.
    config.vm.network "forwarded_port", guest: 5432, host: 5432, host_ip: "127.0.0.1"

    # Disable the default synced folder.
    config.vm.synced_folder ".", "/vagrant", disabled: true

    # Synchronize our docker-composer override file.
    config.vm.synced_folder "./box/unms/app", "/home/unms/app",
        type: "rsync",
        rsync__args: ["-r", "--include=docker-compose.override.yml", "--exclude=*"],
        owner: "1001", # unms
        group: "root",
        create: true

    # Synchronize our custom UCRM (w/ Xdebug) docker image files.
    config.vm.synced_folder "./box/unms/app/xdebug/", "/home/unms/app/xdebug/",
        type: "rsync",
        owner: "1001", # unms
        group: "root",
        create: true

    # Synchronize a folder specifically for pushing sensitive information back from the guest system.
    config.vm.synced_folder "./box/vagrant/", "/home/vagrant/",
        type: "rsync",
        rsync__args: ["-r", "--include=install-composer.php", "--include=.bash_aliases", "--exclude=*"],
        owner: "vagrant",
        group: "vagrant",
        create: true

    # Synchronize a folder specifically for pushing sensitive information back from the guest system.
    config.vm.synced_folder "./box/vagrant/env/", "/home/vagrant/env/", type: "smb"

    # Synchronize the "plugins" folder that maps to UCRM: /data/ucrm/data/plugins/
    # /home/unms/data/ucrm/ucrm/data/plugins
    #config.vm.synced_folder "./box/vagrant/src/", "/home/vagrant/ucrm/src/", type: "smb"

    # Synchronize the "routers" folder that maps to UCRM: /usr/src/ucrm/web/_plugins/
    #config.vm.synced_folder "./box/vagrant/www/", "/home/vagrant/ucrm/www/", type: "smb"

    # Synchronize the "code" folder
    #config.vm.synced_folder "./plugins/", "/home/vagrant/plugins/",
    #    type: "smb"

    # When a local package is added to the box cache, as outlined in './box/README.md', the version is always 0.  To
    # alleviate any version issues from this, we simply append the version to the box name when adding it from a local
    # package.  The following are examples of boxes added this way:
    # - ucrm-plugins/uisp-1.4.4 (virtualbox, 0)
    # - ucrm-plugins/uisp-1.4.5 (virtualbox, 0)
    #
    # Boxes downloaded from vagrant Cloud differ in that their names do not always contain the version and instead an
    # actual version is included.  For example:
    # - ucrm-plugins/uisp (virtualbox, 1.4.4)
    # - ucrm-plugins/uisp (virtualbox, 1.4.5)
    #
    # The following code attempts to determine the correct box version to use, in cases where it is already cached.  If
    # the code fails to find a valid box, locally, it will then fail over to trying Vagrant Cloud.

    if `vagrant box list`.match(/^ucrm-plugins\/uisp-#{UISP_VERSION.gsub(".", "\\.")}\s*\(virtualbox, 0\)$/m)
        #puts "Locally added box version exists..."
        config.vm.box = "ucrm-plugins/uisp-#{UISP_VERSION}"
    else
        #puts "A locally cached version of the specified box does not exist, continuing to download as necessary..."
        config.vm.box = "ucrm-plugins/uisp"
        config.vm.box_version = "#{UISP_VERSION}"
    end

    # VirtualBox VM Configuration.
    config.vm.provider "virtualbox" do |vm|
        vm.name = "uisp-dev-#{UISP_VERSION}"

        # NOTE: Set the following to suit your needs and based upon available host resources.
        vm.cpus = 1
        vm.memory = 4096
    end

    config.vm.provision "env", type: "shell", keep_color: true, run: "always", inline: <<-SHELL

            # Crete the shared env folder, if it does not already exist.
            mkdir -p /home/vagrant/env/

            # Remove any stale env files.
            rm -f /home/vagrant/env/{box,unms}.conf

            # Copy over the env information created by UISP.
            cp /home/unms/app/unms.conf /home/vagrant/env/unms.conf

            # Create a env file for box specific information.

            echo 'HOSTNAME="'`hostname`'"' >> /home/vagrant/env/box.conf
            echo 'IP="#{VBOX_ADDRESS}"' >> /home/vagrant/env/box.conf

            # Make sure ownership and permissions are correct.
            chown -R vagrant:vagrant /home/vagrant/env/

            # We also set some env variables for use in the box itself.
            rm -f /etc/profile.d/box.sh
            echo "export UISP_VERSION=\"#{UISP_VERSION}\"" >> /etc/profile.d/box.sh
            echo "export UCRM_VERSION=\"#{UCRM_VERSION}\"" >> /etc/profile.d/box.sh
            echo "export UISP_ENVIRONMENT=\"development\"" >> /etc/profile.d/box.sh
            # ... Add any other system-wide environment variables here!
            chown root:root /etc/profile.d/box.sh
            chmod +x /etc/profile.d/box.sh
            source /etc/profile.d/box.sh

    SHELL

    config.vm.provision "build", type: "shell", keep_color: true, inline: <<-SHELL

        # Builds and runs our custom docker image.
        cd /home/unms/app && UCRM_VERSION=#{UCRM_VERSION} docker-compose -p unms up -d --build ucrm
    SHELL

    config.vm.provision "tools", type: "shell", keep_color: true, inline: <<-SHELL

        # Install PHP for use on the box.
        apt-get update -y
        add-apt-repository -y ppa:ondrej/php
        apt-get install -y php7.4-cli

        # Install Composer for use on the box.
        #cd /home/vagrant && php install-composer.php
        php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
        php composer-setup.php
        php -r "unlink('composer-setup.php');"
        mv composer.phar /usr/local/bin/composer

    SHELL

    config.vm.provision "permissions", type: "shell", keep_color: true, inline: <<-SHELL

        # Take ownership of the UCRM data folder and ALL sub-folders for SFTP access.
        chown vagrant:vagrant -R /home/unms/data/ucrm/

    SHELL


    #config.trigger.after [ :destroy ] do |trigger|
    #
    #    files = [
    #        "./box/vagrant/env/box.conf",
    #        "./box/vagrant/env/unms.conf"
    #    ]
    #
    #    files.each do |file|
    #        File.delete(file) if File.exist?(file)
    #    end
    #
    #end

end
