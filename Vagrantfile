
# The Vagrant configuration for a UISP Base Box with minimal changes to the default installation of UISP.
#
# @author Ryan Spaeth <rspaeth@spaethtech.com>
# @copyright 2022 Spaeth Technologies Inc.

VAGRANT_FILE_VER    = "2"
HOST_PROJECT_DIR    = File.expand_path("./")
VBOX_PROJECT_DIR    = "/src/ucrm-plugins"
HOST_VAGRANT_DIR    = File.expand_path("./vagrant")

require_relative    "#{HOST_VAGRANT_DIR}/modules/os.rb"
require_relative    "#{HOST_VAGRANT_DIR}/modules/ssh.rb"
require_relative    "#{HOST_VAGRANT_DIR}/modules/uisp.rb"

PROVISIONERS_DIR    = "#{HOST_VAGRANT_DIR}/provisioners"
CERTIFICATES_DIR    = "#{HOST_VAGRANT_DIR}/certs"

# ----------------------------------------------------------------------------------------------------------------------
# CONFIGURATION
# ----------------------------------------------------------------------------------------------------------------------

BOX_HOSTNAME        = "uisp"
BOX_ADDRESS         = "192.168.56.10"
DNS_ALIASES         = [ "#{BOX_HOSTNAME}.dev" ]
ROOT_PASSWORD       = "vagrant"
UISP_VERSION        = "1.4.7"
UCRM_VERSION        = UISP.getUcrmVersion(UISP_VERSION)

# ----------------------------------------------------------------------------------------------------------------------
# VAGRANT
# ----------------------------------------------------------------------------------------------------------------------

Vagrant.configure(VAGRANT_FILE_VER) do |config|

    config.vagrant.plugins = [ "vagrant-vbguest", "vagrant-hostmanager" ]

    config.vbguest.auto_update = false

    # ------------------------------------------------------------------------------------------------------------------
    # NETWORKING
    # ------------------------------------------------------------------------------------------------------------------

    # The hostmanager plugin alters the hosts file on both the host machine and any/all of the guest boxes to include
    # the box hostname and any aliases provided above.
    config.hostmanager.enabled              = true
    config.hostmanager.manage_host          = true
    config.hostmanager.manage_guest         = true
    config.hostmanager.ignore_private_ip    = false
    config.hostmanager.include_offline      = false

    config.vm.hostname                      = BOX_HOSTNAME
    config.hostmanager.aliases              = DNS_ALIASES

    # NOTE: It is preferable to use private networking here for several notable reasons:
    # - Security, especially since we default to insecure passwords on the guest.
    # - UISP does not allow localhost for server name, so we can provide an IP or alias instead for testing public URLs.
    # - Easier configuration of Xdebug communication with the local machine.
    # - Segregation, in cases where developers may have multiple development environments on the same machine.
    # - Also, since hostmanager does not work on reload/halt, this prevents the need for repeated hosts file changes.

    config.vm.network "private_network", ip: BOX_ADDRESS

    # ------------------------------------------------------------------------------------------------------------------
    # FILE SYSTEM
    # ------------------------------------------------------------------------------------------------------------------

    # Disable the default synced folder.
    config.vm.synced_folder ".", "/vagrant", disabled: true

    # And sync our entire project.
    config.vm.synced_folder "#{HOST_PROJECT_DIR}", "#{VBOX_PROJECT_DIR}"

    # ------------------------------------------------------------------------------------------------------------------
    # BASE BOX
    # ------------------------------------------------------------------------------------------------------------------

    config.vm.box = "bento/ubuntu-20.04"

    # ------------------------------------------------------------------------------------------------------------------
    # PROVIDERS
    # ------------------------------------------------------------------------------------------------------------------

    # VirtualBox
    config.vm.provider :virtualbox do |vm, override|
        vm.name = "#{BOX_HOSTNAME}-#{UISP_VERSION}"
        vm.cpus = 1
        vm.memory = 4096
        # vb.customize [
        #     "setextradata",
        #     :id,
        #     "VBoxInternal2/SharedFoldersEnableSymlinksCreate#{VBOX_PROJECT_DIR}",
        #     "1"
        # ]
    end

    # NOTE: Both Hyper-V and VMware have numerous issues preventing a completely functioning system, so they have been
    # abandoned for the time being!

    # ------------------------------------------------------------------------------------------------------------------
    # PROVISIONERS
    # ------------------------------------------------------------------------------------------------------------------

    # Provision the Users...
    config.vm.provision :users,
        type: :shell,
        keep_color: true,
        path: "#{PROVISIONERS_DIR}/users.sh",
        env: { "ROOT_PASSWORD" => "#{ROOT_PASSWORD}" }

    # Provision the bash environment...
    config.vm.provision :bash,
        type: :shell,
        keep_color: true,
        path: "#{PROVISIONERS_DIR}/bash.sh",
        env: { }

    # Provision the Network...
    config.vm.provision :network,
        type: :shell,
        keep_color: true,
        path: "#{PROVISIONERS_DIR}/network.sh",
        env: { "IPV6_DISABLE" => "all,default,lo,eth0,eth1" }

    # Provision the Firewall...
    config.vm.provision :firewall,
        type: :shell,
        keep_color: true,
        path: "#{PROVISIONERS_DIR}/firewall.sh",
        env: {}

    # Provision the UISP installation...
    config.vm.provision :uisp,
        type: :shell,
        keep_color: true,
        path: "#{PROVISIONERS_DIR}/uisp.sh",
        env: {
            "UISP_VERSION" => "#{UISP_VERSION}",
            "BOX_HOSTNAME" => "#{BOX_HOSTNAME}",
            "BOX_CERT_DIR" => "#{VBOX_PROJECT_DIR}/vagrant/certs"
        }

    # env: Always run the environment provisioner, to keep changes updated in the ENV and files.
    config.vm.provision :environment,
        type: :shell,
        keep_color: true,
        run: :always,
        path: "#{PROVISIONERS_DIR}/environment.sh",
        env: {
            "UISP_VERSION" => "#{UISP_VERSION}",
            "UCRM_VERSION" => "#{UCRM_VERSION}"
            #"PROJECT_DIR" => "#{VBOX_PROJECT_DIR}"
        }

    # build: This provisioner is responsible for building an updated version of the overrides.
    config.vm.provision :overrides,
        type: :shell,
        keep_color: true,
        path: "#{PROVISIONERS_DIR}/overrides.sh",
        env: {}

    # build: This provisioner is responsible for building an updated version of the overrides.
    config.vm.provision :postgres,
        type: :shell,
        keep_color: true,
        run: :never,
        path: "#{PROVISIONERS_DIR}/postgres.sh",
        env: {}

    # Provision PHP...
    config.vm.provision :php,
        type: :shell,
        keep_color: true,
        path: "#{PROVISIONERS_DIR}/php.sh",
        env: {
            "PROJECT_DIR" => "#{VBOX_PROJECT_DIR}"
        }

    # Provision NodeJS...
    config.vm.provision :node,
        type: :shell,
        keep_color: true,
        #run: :never,
        path: "#{PROVISIONERS_DIR}/node.sh",
        env: {}

        # Provision NodeJS...
    config.vm.provision :permissions,
        type: :shell,
        keep_color: true,
        #run: :never,
        path: "#{PROVISIONERS_DIR}/permissions.sh",
        env: {}

    # Provision Code Server...
    config.vm.provision :code_server,
        type: :shell,
        keep_color: true,
        run: :never,
        path: "#{PROVISIONERS_DIR}/code-server.sh",
        env: {
            "BOX_HOSTNAME" => "#{BOX_HOSTNAME}",
            "WORKSPACE" => "#{VBOX_PROJECT_DIR}",
            "BIND_HOST" => "0.0.0.0",
            "BIND_PORT" => "8080",
            "EXTENSIONS" => [
                "natizyskunk.sftp",
                "editorconfig.editorconfig",
                "ms-azuretools.vscode-docker",
                "streetsidesoftware.code-spell-checker",
                "ikappas.composer",
                "bmewburn.vscode-intelephense-client",
                "ionutvmi.path-autocomplete",
                "neilbrayfield.php-docblocker",
                "marcostazi.vs-code-vagrantfile",
                "felixfbecker.php-debug"
            ]
        }

    # ------------------------------------------------------------------------------------------------------------------
    # TRIGGERS
    # ------------------------------------------------------------------------------------------------------------------

    require 'fileutils'

    config.trigger.before :up do |trigger|
        trigger.info = "Configuring SSL for #{BOX_HOSTNAME}"
        trigger.ruby do |env, machine|
            # NOTE: The conditional command should result in one of the following conditions:
            # - /usr/bin/mkcert on linux or Windows (via the Git Bash shell)
            # - /c/HashiCorp/Vagrant/embedded/usr/bin/mkcert on Windows (via Vagrant's embedded shell)
            # - OR an empty string which should trigger the installation of mkcert
            if `which mkcert 2>/dev/null` == ""
                if OS.windows?
                    puts "Installing mkcert on Windows (in Vagrant's embedded shell)..."
                    MKCERT_URL = "https://dl.filippo.io/mkcert/latest?for=windows/amd64"
                    `wget -q --show-progress #{MKCERT_URL} -O /usr/bin/mkcert.exe`
                else
                    puts "Installing mkcert on Linux..."
                    MKCERT_URL = "https://dl.filippo.io/mkcert/latest?for=linux/amd64"
                    `wget -q --show-progress #{MKCERT_URL} -O /usr/bin/mkcert`
                end
            end

            # Install the local CA in the trust store.
            `mkcert -install`

            CRT_SANS=DNS_ALIASES.join(" ")
            CRT_FILE="#{CERTIFICATES_DIR}/#{BOX_HOSTNAME}.crt"
            KEY_FILE="#{CERTIFICATES_DIR}/#{BOX_HOSTNAME}.key"

            if (not File.exists?(CRT_FILE)) or (not File.exists?(KEY_FILE))
                puts "Generating SSL Certificates for local development..."
                `mkcert -cert-file #{CRT_FILE} -key-file #{KEY_FILE} #{BOX_HOSTNAME} #{CRT_SANS}`
            end
        end
    end

    config.trigger.after :destroy do |trigger|
        trigger.info = "Configuring SSL for #{BOX_HOSTNAME}"
        trigger.ruby do |env, machine|
            #`rm #{CERTIFICATES_DIR}/*.{crt,key}`
        end
    end

    config.trigger.after :up, :reload do |trigger|
        trigger.info = "Configuring VSSH for Windows"
        trigger.ruby do |env, machine|
            SSH.setMachine(machine)
            SSH.updateConfig(BOX_HOSTNAME, DNS_ALIASES.join(" "), BOX_ADDRESS, "vagrant", "22")
            #SSH.updateScript("#{HOST_PROJECT_DIR}/dev/bin/vssh", "SSH_PATH", "~/.ssh/config")
            SSH.updateScript("#{HOST_PROJECT_DIR}/dev/bin/vssh", "SSH_HOST", BOX_HOSTNAME)

            File.open("#{HOST_VAGRANT_DIR}/build_version", "wb") { |f| f.puts UISP_VERSION }
        end
    end

    config.trigger.after :halt, :destroy do |trigger|
        trigger.info = "Configuring VSSH for Windows"
        trigger.ruby do |env, machine|
            SSH.setMachine(machine)
            SSH.deleteConfig(BOX_HOSTNAME)
            FileUtils.rm_rf("#{HOST_VAGRANT_DIR}/env")
        end
    end

end
