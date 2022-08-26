# -*- mode: ruby -*-
# vi: set ft=ruby :

# The Vagrant configuration for a UISP Development Box with major changes to the default installation of UISP.
#
# @author Ryan Spaeth <rspaeth@spaethtech.com>
# @copyright 2022 Spaeth Technologies Inc.

# Load Default Config
base = "./box/Vagrantfile"
load base if File.exists?(base)

Vagrant.configure("2") do |config|

    # ------------------------------------------------------------------------------------------------------------------
    # CONFIGURATION
    # ------------------------------------------------------------------------------------------------------------------

    PROJECT_DIR     = "./"
    BOX_HOSTNAME    = "uisp-dev"
    BOX_ADDRESS     = "192.168.56.10"
    DNS_ALIASES     = [ "#{BOX_HOSTNAME}.local" ]
    ROOT_PASSWORD   = "vagrant"
    UISP_VERSION    = "1.4.7"
    UCRM_VERSION    = UISP.getUcrmVersion(UISP_VERSION)

    GIT_USER_NAME   = "Ryan Spaeth"
    GIT_USER_EMAIL  = "rspaeth@spaethtech.com"

    # ------------------------------------------------------------------------------------------------------------------
    # NETWORKING
    # ------------------------------------------------------------------------------------------------------------------

    config.vm.network "private_network", ip: BOX_ADDRESS

    config.vm.hostname = BOX_HOSTNAME
    config.hostmanager.aliases = DNS_ALIASES

    # ------------------------------------------------------------------------------------------------------------------
    # FILE SYSTEM
    # ------------------------------------------------------------------------------------------------------------------

    # Synced folder containing any desired Docker Compose overrides.
    #config.vm.synced_folder "./box/unms/app/overrides", "/home/unms/app/overrides", owner: "unms", group: "root"

    # Synced folder specifically for getting sensitive information back from the guest system.
    #config.vm.synced_folder "./box/vagrant/env", "/home/vagrant/env"
    #config.vm.synced_folder "./box/vagrant/scripts/ucrm", "/home/vagrant/scripts/ucrm"

    config.vm.synced_folder ".", "/src/ucrm-plugins"

    # ------------------------------------------------------------------------------------------------------------------
    # BASE BOX
    # ------------------------------------------------------------------------------------------------------------------

    # When a local package is added to the box cache, the version is always 0.  To alleviate any version issues from
    # this, we simply append the version to the box name when adding it from a local package.
    #
    # The following are examples of boxes added this way:
    # - ucrm-plugins/uisp-1.4.4 (virtualbox, 0)
    # - ucrm-plugins/uisp-1.4.5 (virtualbox, 0)
    #
    # Boxes downloaded from Vagrant Cloud differ in that their names do not contain the version and instead an actual
    # version is provided.
    #
    # The following are examples of boxes added from Vagrant Cloud:
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

    # VirtualBox
    config.vm.provider "virtualbox" do |vm, override|
        # NOTE: Set the following to suit your needs and based upon available host resources.
        vm.name = "#{BOX_HOSTNAME}-#{UISP_VERSION}"
        vm.cpus = 1
        vm.memory = 4096
    end

#     # VMware
#     config.vm.provider "vmware_desktop" do |vm, override|
#         vm.gui = true
#         vm.vmx["displayname"] = "#{BOX_HOSTNAME}-#{UISP_VERSION}"
#         vm.vmx["memsize"] = "4096"
#         vm.vmx["numvcpus"] = "1"
#
#         # Do NOT Change the following unless you know what you're doing!
#         #vm.vmx["ethernet0.pcislotnumber"] = "32"
#         #vm.vmx["ethernet1.pcislotnumber"] = "33"
#
#         #NETWORK_NAME = OS.windows? ? "VMnet1" : "vmnet1"
#         #override.vm.network "private_network", type: "dhcp", name: NETWORK_NAME, adapter: 1
#     end


    # ------------------------------------------------------------------------------------------------------------------
    # PROVISIONERS
    # ------------------------------------------------------------------------------------------------------------------

    # Unset any provisioners used in the base box configuration!
    config.vm.provision "users",    type: "shell", path: nil, inline: ""
    config.vm.provision "network",  type: "shell", path: nil, inline: ""
    config.vm.provision "firewall", type: "shell", path: nil, inline: ""
    config.vm.provision "install",  type: "shell", path: nil, inline: ""

    PROVISION_DIR = "./box/vagrant/provisioning"

    # env: Always run the environment provisioner, to keep changes updated in the ENV and files.
    config.vm.provision "environment", type: "shell", keep_color: true, run: "always",
        path: "#{PROVISION_DIR}/environment.sh",
        env: { "UISP_VERSION" => "#{UISP_VERSION}", "UCRM_VERSION" => "#{UCRM_VERSION}" }

    # build: This provisioner is responsible for building an updated version of the overrides.
    config.vm.provision "build", type: "shell", keep_color: true,
        path: "#{PROVISION_DIR}/build.sh"
        #env: { "UISP_VERSION" => "#{UISP_VERSION}", "UCRM_VERSION" => "#{UCRM_VERSION}" }

    # build: This provisioner is responsible for building an updated version of the overrides.
    config.vm.provision "postgres", type: "shell", keep_color: true,
        path: "#{PROVISION_DIR}/postgres.sh"
        #env: { "UISP_VERSION" => "#{UISP_VERSION}", "UCRM_VERSION" => "#{UCRM_VERSION}" }

    # Provision file/folder permissions...
    config.vm.provision "permissions", type: "shell", keep_color: true,
        path: "#{PROVISION_DIR}/permissions.sh"
        #env: { }

    # Provision PHP...
    config.vm.provision "php", type: "shell", keep_color: true,
        path: "#{PROVISION_DIR}/php.sh"
        env: { "GIT_USER_NAME" => "#{GIT_USER_NAME}", "GIT_USER_EMAIL" => "#{GIT_USER_EMAIL}"  }

    # Provision NodeJS...
    config.vm.provision "node", type: "shell", keep_color: true,
        path: "#{PROVISION_DIR}/node.sh"
        env: {}




end
