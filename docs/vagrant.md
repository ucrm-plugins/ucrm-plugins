# Local Development Environment (Vagrant)

To help improve the Plugin development experience, the following guide can be used to deploy and configure a local 
development environment using [Vagrant](https://www.vagrantup.com/).

> This guide was written for use with the [PhpStorm](https://www.jetbrains.com/phpstorm/), but should be easily modified
> for any IDE or text editor.  Some features like debugging may not be available in all editors, but feel free to
> customize the environment to suit your needs.
> 
> This guide was also composed while using a Windows development machine, so some assumptions were made. However, these
> instructions will likely work (with little or no modification) on other platforms.

## Requirements

The following will need to be installed for the system to work correctly:
- [Vagrant](https://www.vagrantup.com/)
- [Virtualbox](https://www.virtualbox.org/)
- [PHP](https://windows.php.net/) (UCRM currently uses version 7.4 x64 NTS)

## PHP Extensions

Here is the current list of [PHP Extensions](./extensions.md) included with UCRM.  Be certain any extensions your Plugin
needs are included in this list.

## Configuration

Modifications can be made to the `Vagrantfile`, but will require a `vagrant reload` or at the very least a `vagrant provision` to apply the changes.

### Vagrantfile
`./Vagrantfile`

In the Vagrantfile the following variables can be changed as desired:
- `VBOX_ADDRESS`: The IP address you wish to have assigned to the Guest VM, defaults to `192.168.50.10`
- `VBOX_ROOT_PASSWORD`: The password used for root access inside the Guest VM, defaults to `vagrant`
- `UISP_VERSION`: The desired version of UISP to install, should default to the latest stable version

> _**NOTE:** Changing to a new version immediately after it's release will require that an updated version of the UISP
> Box on Vagrant Cloud be available.  The process will also require that the box be destroyed and recreated if a
> previous version was already installed._
> 
> To build (or customize) the base box yourself, see [Building the Base Box Manually](./vagrant/basebox.md)
> 
> **Updates done via the built-in UISP update system could cause unexpected results.**

Lower in the Vagrantfile, you will also see the following, which can also be configured based on your available hardware:
- `vm.cpus`: The number of CPU cored to allocate to the VM, defaults to `1`
- `vm.memory`: The amount of memory (in Megabytes) to be allocated to the VM, defaults to `4096`

> _**NOTE:** Be certain your development machine can handle the hardware allocations, as you will also still need to run
> all of your development applications and normal processes._

### Dockerfile
`./box/unms/app/overrides/xdebug/Dockerfile`

Changes should no longer be made in the Dockerfile itself, as the build arguments pull from ENV variables of the
currently running Guest. 

### xdebug.ini
`./box/unms/app/overrides/xdebug/xdebug.ini`

> See the [Xdebug Documentation](https://xdebug.org/docs/all_settings) for all the available settings.
>
> _**NOTE:** The `xdebug.client_host` setting uses the address `10.0.2.2` to point to the development machine hosting
> the Vagrant box._  

### xdebug_params
`./box/unms/app/overrides/xdebug/xdebug_params`

The `PHP_IDE_CONFIG "serverName=vagrant"` setting will set the server name (used for path mapping) during the debug
sessions and can be changed as desired.

## Installation

Now that all the desired modifications have been made, it is time to deploy the Vagrant box.

Open a terminal at the project root (the same directory as the Vagrantfile) and run the `vagrant up` command.

After a few minutes, the installation should be complete and the Vagrant box should be started.  You can open the
VirtualBox application and verify the VM is running.

## Accessing the VM (SSH)

Vagrant provided the `vagrant ssh` command to allow for easy SSH access into the VM.

## Accessing the VM (HTTP)

Within minute or two of installation, the standard UISP on-boarding process should also be available at
https://192.168.50.10 (unless otherwise changed in the Vagrantfile) from your development machine.

## Next Steps

- [Using UPM](./upm/using.md)

