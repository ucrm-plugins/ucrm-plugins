# Local Development Environment (Vagrant)

To help improve the Plugin development experience, the following guide can be used to deploy and configure a local 
development environment using [Vagrant](https://www.vagrantup.com/).

> This guide was written for use with the [PhpStorm](https://www.jetbrains.com/phpstorm/), but can be easily modified
> for any IDE or text editor.  Some features like debugging may not be available in all editors, but feel free to
> customize the environment to suit your needs.
> 
> This guide was also composed while using a Windows development machine, so some assumptions were made. However, these
> instructions will likely work (with little or no modification) on other platforms.

### Requirements

The following will need to be installed for the system to work correctly:
- [Vagrant](https://www.vagrantup.com/)
- [Virtualbox](https://www.virtualbox.org/)
- [PHP](https://windows.php.net/) (UCRM currently uses version 7.4 NTS)

### PHP

The following extensions come installed and enabled with UCRM. It is possible that some of these extensions are not
included with your installed version of PHP.  For everything to work perfectly, you will need to install any missing
extensions that your Plugin(s) may use.
```
[PHP Modules]
apcu
bcmath
bz2
Core
ctype
curl
date
dom
ds
exif
fileinfo
filter
ftp
gd
gmp
hash
iconv
intl
json
libxml
mbstring
mysqlnd
openssl
pcre
PDO
pdo_pgsql
pdo_sqlite
Phar
posix
readline
Reflection
session
SimpleXML
soap
sockets
sodium
SPL
sqlite3
standard
sysvmsg
sysvsem
sysvshm
tokenizer
xdebug
xml
xmlreader
xmlwriter
Zend OPcache
zip
zlib

[Zend Modules]
Xdebug
Zend OPcache
```

> _**NOTE:** The Xdebug extension is **NOT** installed by default, but added and enabled during our custom deployment._

### Configuration

Modifications can be made to the system configuration, but they must precede the `vagrant up` command. Many of the below
modifications will require the VM be destroyed and recreated if changes are later made.

#### Vagrantfile

In the `Vagrantfile` the following variables can be changed as desired:
- `VBOX_ADDRESS`: The IP address you wish to have assigned to the UISP VM, defaults to `192.168.50.10`
- `VBOX_ROOT_PASSWORD`: The password used for root access inside the UISP VM, defaults to `vagrant`
- `UISP_VERSION`: The desired version of UISP to install, defaults to `1.4.3`

> _**NOTE:** Changing to a new version immediately after it's release will require that an updated version of the UISP
> Box on Vagrant Cloud be available.  The process will also require that the box be destroyed and recreated if a
> previous version was already installed._

Lower in the file, you will also see the following, which can also be configured based on your available hardware:
- `vm.cpus`: The number of CPU cored to allocate to the VM, defaults to `1`
- `vm.memory`: The amount of memory (in Megabytes) to be allocated to the VM, defaults to `4096`

> _**NOTE:** Be certain your development machine can handle the hardware allocations, as you will also still need to run
> all of your development applications and normal processes._

#### environment/app/xdebug/Dockerfile

Change the version on the first line `FROM ubnt/unms-crm:3.4.3` according to your needs.

> _**NOTE:** The major version number of the UCRM docker image is not the same as the UISP version._
> 
> _For example, UISP `v1.4.3` uses UCRM `v3.4.3`_

#### environment/app/xdebug/xdebug.ini

> See the [Xdebug Documentation](https://xdebug.org/docs/all_settings) for all the available settings.
>
> _**NOTE:** The `xdebug.client_host` setting uses the address `10.0.2.2` to point to the development machine hosting
> the Vagrant box._  

#### environment/app/xdebug/xdebug_params

The `PHP_IDE_CONFIG "serverName=localhost"` setting will set the server name (used for path mapping) during the debug
sessions and can be changed as desired.


### Installation

Now that all the desired modifications have been made, it is time to deploy the Vagrant box.

Open a terminal at the project root (the same directory as the Vagrantfile) and run the `vagrant up` command.

> _**NOTE:** On Windows, you will be prompted to enter your Windows credentials at the command line. You will then see a
> UAC notification about creating the shares, and you will be required to accept in order to proceed._

After a few minutes, the installation should be complete and the Vagrant box should be started.

If you installed VirtualBox, you can open the application and verify the VM is running.




### Accessing the VM (SSH)

Vagrant provided the `vagrant ssh` command to allow for easy SSH access into the VM.

### Accessing the VM (HTTP)

The standard UISP on-boarding process should now also be available at https://localhost from your development machine.

