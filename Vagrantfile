# -*- mode: ruby -*-
# vi: set ft=ruby :

#require "rbconfig"

Vagrant.configure("2") do |config|

    config.vagrant.plugins = [ "vagrant-hostmanager" ]

    # ------------------------------------------------------------------------------------------------------------------
    # CONFIGURATION (STATIC)
    # ------------------------------------------------------------------------------------------------------------------

    # NOTE: The following values can be overridden, as desired:
    BOX_HOSTNAME    = "uisp-dev"
    BOX_ADDRESS     = "192.168.50.10"
    DNS_ALIASES     = [ "vagrant" ]
    ROOT_PASSWORD   = "vagrant"
    UISP_VERSION    = "1.4.6"

    # ------------------------------------------------------------------------------------------------------------------
    # CONFIGURATION (DYNAMIC)
    # ------------------------------------------------------------------------------------------------------------------

    # Attempt to automatically determine the UCRM version based on the UISP version provided.
    # NOTE: Currently the UCRM version is ALWAYS exactly 2 major versions ahead.
    UCRM_VERSION = UISP_VERSION.gsub(/^(\d+)/) { |capture| (capture.to_i + 2).to_s }

    # ------------------------------------------------------------------------------------------------------------------
    # STATIC FILES
    # ------------------------------------------------------------------------------------------------------------------

    # Change the UCRM_VERSION in static files, using this file as the source of truth.
    #override_file = "./box/unms/app/overrides/docker-compose.override.yml"
    #changed = false

    #override_data = File.read(override_file).gsub(/^(\s*UCRM_VERSION)\s*:\s*([\d\.]+)$/) do
    #    changed = true unless $2 == "#{UCRM_VERSION}"; $1 + ": #{UCRM_VERSION}"
    #end

    # Only write the contents to the file if they are actually changed...
    #if changed
    #    File.open(override_file, "w") do |out|
    #        out << override_data
    #        puts "Changed UCRM_VERSION to #{UCRM_VERSION} in #{override_file}"
    #    end
    #end

    # ------------------------------------------------------------------------------------------------------------------
    # NETWORKING
    # ------------------------------------------------------------------------------------------------------------------

    config.hostmanager.enabled = true
    config.hostmanager.manage_host = true
    config.hostmanager.manage_guest = true
    config.hostmanager.ignore_private_ip = false
    config.hostmanager.include_offline = false

    config.vm.hostname = "#{BOX_HOSTNAME}"
    config.hostmanager.aliases = DNS_ALIASES

    # NOTE: We prefer to use Private networking here for several notable reasons:
    # - Security, especially since we default to insecure passwords on the guest.
    # - UISP does not allow localhost as the server name, so we can provide an IP instead for testing public URLs.
    # - Easier configuration of Xdebug communication with the local machine.
    # - Separation, in cases where developers may have multiple development environments on the same machine.

    # Set the VM network type to private and assign a static IP address.
    config.vm.network "private_network", ip: "#{BOX_ADDRESS}"

    # Forward the necessary ports to the guest.
    config.vm.network "forwarded_port", guest:   80, host:   80, host_ip: "127.0.0.1"
    config.vm.network "forwarded_port", guest:  443, host:  443, host_ip: "127.0.0.1"
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
    config.vm.provider "virtualbox" do |vm, override|
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

    # FUTURE: Hyper-V has some issues that will need to be addressed!
    #config.vm.provider "hyperv" do |vm, override|
    #    vm.auto_start_action = "StartIfRunning"
    #    vm.auto_stop_action = "Save"
    #    vm.enable_virtualization_extensions = true
    #    vm.enable_checkpoints = true
    #
    #    vm.vmname = "uisp-dev-#{UISP_VERSION}"
    #    vm.cpus = "1"
    #    vm.memory = "4096"
    #    #vm.maxmemory = "8192"
    #
    #    # Hyper-V Skip Switch Prompt?
    #    override.vm.network "private_network", bridge: "Default Switch"
    #
    #    #override.vm.synced_folder "./box/unms/app/overrides", "/home/unms/app/overrides", owner: "unms", group: "root", type: "smb"
    #    #override.vm.synced_folder "./box/vagrant/env", "/home/vagrant/env", type: "smb"
    #end

    # ------------------------------------------------------------------------------------------------------------------
    # PROVISIONERS
    # ------------------------------------------------------------------------------------------------------------------

    # env: Always run the environment provisioner, to keep changes updated in the ENV and files.
    config.vm.provision "environment", type: "shell", keep_color: true, run: "always",
        path: "./box/vagrant/provisioning/environment.sh",
        env: { "UISP_VERSION" => "#{UISP_VERSION}", "UCRM_VERSION" => "#{UCRM_VERSION}" }

    # build: This provisioner is responsible for building an updated version of the overrides.
    config.vm.provision "build", type: "shell", keep_color: true,
        path: "./box/vagrant/provisioning/build.sh",
        env: { "UISP_VERSION" => "#{UISP_VERSION}", "UCRM_VERSION" => "#{UCRM_VERSION}" }

end
