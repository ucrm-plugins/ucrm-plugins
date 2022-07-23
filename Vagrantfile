# -*- mode: ruby -*-
# vi: set ft=ruby :

#require "rbconfig"

Vagrant.configure("2") do |config|

    # ------------------------------------------------------------------------------------------------------------------
    # CONFIGURATION
    # ------------------------------------------------------------------------------------------------------------------

    # NOTE: The following values can be overridden, as desired:
    VBOX_ADDRESS        = "192.168.50.10"
    VBOX_ROOT_PASSWORD  = "vagrant"
    UISP_VERSION        = "1.4.5"

    # Attempt to automatically determine the UCRM version based on the UISP version provided.
    # NOTE: Currently the UCRM version is ALWAYS exactly 2 major versions ahead.
    UCRM_VERSION = UISP_VERSION.gsub(/^(\d+)/) { |capture| (capture.to_i + 2).to_s }

    # Change the UCRM_VERSION in static files, using this file as the source of truth.
    override_file = "./box/unms/app/overrides/docker-compose.override.yml"
    changed = false

    override_data = File.read(override_file).gsub(/^(\s*UCRM_VERSION)\s*:\s*([\d\.]+)$/) do
        changed = true unless $2 == "#{UCRM_VERSION}"; $1 + ": #{UCRM_VERSION}"
    end

    # Only write the contents to the file if they are actually changed...
    if changed
        File.open(override_file, "w") do |out|
            out << override_data
            puts "Changed UCRM_VERSION to #{UCRM_VERSION} in #{override_file}"
        end
    end

    # ------------------------------------------------------------------------------------------------------------------
    # NETWORKING
    # ------------------------------------------------------------------------------------------------------------------

    # NOTE: We prefer to use Private networking here for several notable reasons:
    # - Security, especially since we default to insecure passwords on the guest.
    # - UISP does not allow localhost as the server name, so we can provide an IP instead for testing public URLs.
    # - Easier configuration of Xdebug communication with the local machine.
    # - Separation, in cases where developers may have multiple development environments on the same machine.

    # Set the VM network type to private and assign a static IP address.
    config.vm.network "private_network", ip: "#{VBOX_ADDRESS}"

    # Forward the necessary ports to the guest.
    config.vm.network "forwarded_port", guest: 80, host: 80, host_ip: "127.0.0.1"
    config.vm.network "forwarded_port", guest: 443, host: 443, host_ip: "127.0.0.1"
    config.vm.network "forwarded_port", guest: 2055, host: 2055, host_ip: "127.0.0.1"

    # Forward override ports PostgreSQL port.
    config.vm.network "forwarded_port", guest: 5432, host: 5432, host_ip: "127.0.0.1"

    # ------------------------------------------------------------------------------------------------------------------
    # FILE SYSTEM
    # ------------------------------------------------------------------------------------------------------------------

    # Disable the default folder.
    config.vm.synced_folder ".", "/vagrant", disabled: true

    # Synced folder containing any desired Docker Compose overrides.
    config.vm.synced_folder "./box/unms/app/overrides", "/home/unms/app/overrides", owner: "unms", group: "root"

    # Synced folder specifically for getting sensitive information back from the guest system.
    config.vm.synced_folder "./box/vagrant/env", "/home/vagrant/env"

    # ------------------------------------------------------------------------------------------------------------------
    # BASE BOX
    # ------------------------------------------------------------------------------------------------------------------

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
        config.vm.box = "ucrm-plugins/uisp-#{UISP_VERSION}"
    else
        config.vm.box = "ucrm-plugins/uisp"
        config.vm.box_version = "#{UISP_VERSION}"
    end

    # ------------------------------------------------------------------------------------------------------------------
    # PROVIDERS
    # ------------------------------------------------------------------------------------------------------------------

    # VirtualBox VM Configuration.
    config.vm.provider "virtualbox" do |vm|
        vm.name = "uisp-dev-#{UISP_VERSION}"

        # NOTE: Set the following to suit your needs and based upon available host resources.
        vm.cpus = 1
        vm.memory = 4096
    end

    # FUTURE: Consider adding support for other providers?
    #config.vm.provider "vmware_desktop" do |vm|
    #    vm.gui = false
    #    vm.vmx["displayname"] = "uisp-dev-#{UISP_VERSION}"
    #
    #    # NOTE: Set the following to suit your needs and based upon available host resources.
    #    #vm.cpus = 1
    #    #vm.memory = 4096
    #    vm.vmx["memsize"] = "4096"
    #    vm.vmx["numvcpus"] = "1"
    #end


    # ------------------------------------------------------------------------------------------------------------------
    # PROVISIONERS
    # ------------------------------------------------------------------------------------------------------------------

    # env: Always run the environment provisioner, to keep changes updated in the ENV and files.
    config.vm.provision "env", type: "shell", keep_color: true, run: "always", inline: <<-SHELL

            # Crete the shared env folder, if it does not already exist.
            mkdir -p /home/vagrant/env/

            # Remove any stale env files.
            rm -f /home/vagrant/env/{box,unms}.conf

            # Copy over the env information created by UISP.
            cp /home/unms/app/unms.conf /home/vagrant/env/unms.conf

            # Create an ENV file for box specific information.

            echo 'HOSTNAME="'`hostname`'"' >> /home/vagrant/env/box.conf
            echo 'IP="#{VBOX_ADDRESS}"' >> /home/vagrant/env/box.conf

            # Make sure ownership and permissions are correct.
            chown -R vagrant:vagrant /home/vagrant/env/

            # We also set some ENV variables for use on the guest itself.
            rm -f /etc/profile.d/box.sh
            echo "export UISP_VERSION=\"#{UISP_VERSION}\"" >> /etc/profile.d/box.sh
            echo "export UCRM_VERSION=\"#{UCRM_VERSION}\"" >> /etc/profile.d/box.sh
            echo "export UISP_ENVIRONMENT=\"development\"" >> /etc/profile.d/box.sh
            echo "export COMPOSER_ALLOW_SUPERUSER=1"       >> /etc/profile.d/box.sh
            # ... Add any other system-wide environment variables here!

            # Double check permissions and source the new values.
            chown root:root /etc/profile.d/box.sh
            chmod +x /etc/profile.d/box.sh
            source /etc/profile.d/box.sh

    SHELL

    # build: This provisioner is responsible for building an updated version of the overrides.
    config.vm.provision "build", type: "shell", keep_color: true, inline: <<-SHELL

        cd /home/unms/app
        rm -f docker-compose.override.yml
        ln -s ./overrides/docker-compose.override.yml docker-compose.override.yml

        # Builds and runs our custom docker image.
        docker-compose -p unms up -d --build ucrm

    SHELL

    # permissions: This provisioner checks and sets ownerships and permissions as needed.
    config.vm.provision "permissions", type: "shell", keep_color: true, inline: <<-SHELL

        chmod 775 -R /home/unms/

        # chown unms:vagrant -R /home/unms/

        # Take ownership of the UCRM data folder and ALL sub-folders for SFTP access.
        # NOTE: This folder maps directly to /data/ inside the UCRM container.
        chown vagrant:vagrant -R /home/unms/data/ucrm/

    SHELL

    # tools: This provisioner is only run upon request, but installs optional tools on the guest.
    config.vm.provision "tools", type: "shell", keep_color: true, run: "never", inline: <<-SHELL

        # Install PHP for use on the guest.
        apt-get update -y
        add-apt-repository -y ppa:ondrej/php
        apt-get install -y php7.4-cli

        # Install Composer for use on the guest.
        #cd /home/vagrant && php install-composer.php
        php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
        php composer-setup.php
        php -r "unlink('composer-setup.php');"
        mv composer.phar /usr/local/bin/composer

    SHELL

end
