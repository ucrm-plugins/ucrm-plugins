# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|

    config.vagrant.plugins = [ "vagrant-hostmanager" ]

    # ------------------------------------------------------------------------------------------------------------------
    # CONFIGURATION (STATIC)
    # ------------------------------------------------------------------------------------------------------------------

    # NOTE: The following values can be overridden, as desired:
    BOX_HOSTNAME  = "uisp-dev"
    BOX_ADDRESS   = "192.168.50.10"
    DNS_ALIASES   = [ "vagrant" ]
    ROOT_PASSWORD = "vagrant"
    UISP_VERSION  = "1.4.6"

    # Attempt to automatically determine the UCRM version based on the UISP version provided.
    # WATCH: Currently the UCRM version is ALWAYS exactly 2 major versions ahead.
    UCRM_VERSION = UISP_VERSION.gsub(/^(\d+)/) { |capture| (capture.to_i + 2).to_s }

    # ------------------------------------------------------------------------------------------------------------------
    # NETWORKING
    # ------------------------------------------------------------------------------------------------------------------

    # The hostmanager plugin alters the hosts file on both the host machine and any/all of the guest boxes to include
    # the box hostname and any aliases provided above.
    config.hostmanager.enabled = true
    config.hostmanager.manage_host = true
    config.hostmanager.manage_guest = true
    config.hostmanager.ignore_private_ip = false
    config.hostmanager.include_offline = false

    config.vm.hostname = "#{BOX_HOSTNAME}"
    config.hostmanager.aliases = DNS_ALIASES

    # NOTE: It is preferable to use private networking here for several notable reasons:
    # - Security, especially since we default to insecure passwords on the guest.
    # - UISP does not allow localhost for server name, so we can provide an IP or alias instead for testing public URLs.
    # - Easier configuration of Xdebug communication with the local machine.
    # - Segregation, in cases where developers may have multiple development environments on the same machine.
    # - Also, since hostmanager does not work on reload/halt, this prevents the need for repeated hosts file changes.

    # Set the VM network type to private and assign a static IP address.
    config.vm.network "private_network", ip: "#{BOX_ADDRESS}"

    # Forward the necessary ports to the guest.
    config.vm.network "forwarded_port", guest:   80, host:   80, host_ip: "127.0.0.1"
    config.vm.network "forwarded_port", guest:  443, host:  443, host_ip: "127.0.0.1"
    config.vm.network "forwarded_port", guest: 2055, host: 2055, host_ip: "127.0.0.1"

    # Forward any of our override ports to the guest.
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
    config.vm.synced_folder "./box/vagrant/scripts/ucrm", "/home/vagrant/scripts/ucrm"

    # ------------------------------------------------------------------------------------------------------------------
    # BASE BOX
    # ------------------------------------------------------------------------------------------------------------------

    # When a local package is added to the box cache, the version is always 0.  To alleviate any version issues from
    # this, we simply append the version to the box name when adding it from a local package.
    #
    # The following are examples of boxes added this way:
    # - ucrm-plugins/uisp-1.4.4 (virtualbox, 0)
    # - ucrm-plugins/uisp-1.4.5 (virtualbox, 0)
    # - ucrm-plugins/uisp-1.4.6 (virtualbox, 0)
    #
    # Boxes downloaded from Vagrant Cloud differ in that their names do not contain the version and instead an actual
    # version is provided.
    #
    # The following are examples of boxes added from Vagrant Cloud:
    # - ucrm-plugins/uisp (virtualbox, 1.4.4)
    # - ucrm-plugins/uisp (virtualbox, 1.4.5)
    # - ucrm-plugins/uisp (virtualbox, 1.4.6)
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
    config.vm.provider "virtualbox" do |vm, override|
        vm.name = "uisp-dev-#{UISP_VERSION}"

        # NOTE: Set the following to suit your needs and based upon available host resources.
        vm.cpus = 1
        vm.memory = 4096
    end

    # ------------------------------------------------------------------------------------------------------------------
    # PROVISIONERS
    # ------------------------------------------------------------------------------------------------------------------

    # env: Always run the environment provisioner, to keep changes updated in the ENV and files.
    config.vm.provision "environment", type: "shell", keep_color: true, run: "always",
        path: "./box/vagrant/provisioning/environment.sh",
        env: { "UISP_VERSION" => "#{UISP_VERSION}", "UCRM_VERSION" => "#{UCRM_VERSION}" }

    # build: This provisioner is responsible for building an updated version of the overrides.
    config.vm.provision "build", type: "shell", keep_color: true,
        path: "./box/vagrant/provisioning/build.sh"
        #env: { "UISP_VERSION" => "#{UISP_VERSION}", "UCRM_VERSION" => "#{UCRM_VERSION}" }

    # ------------------------------------------------------------------------------------------------------------------
    # TRIGGERS
    # ------------------------------------------------------------------------------------------------------------------

    config.trigger.after :up do |trigger|
        trigger.info = "Configuring VSSH for Windows"

        trigger.ruby do |env, machine|
            key_file_dir=".vagrant/machines/default/virtualbox"
            if not File.exist?("#{key_file_dir}/private_key")
                if config = /^\s*IdentityFile\s*(?<key_file>.*)$/.match(`vagrant ssh-config`)
                    key_file_name=File.basename(config["key_file"])
                    FileUtils.cp(config["key_file"], key_file_dir)
                    File.rename("#{key_file_dir}/#{key_file_name}", "#{key_file_dir}/private_key")
                end
            end
        end
    end

end
