# PhpStorm Deployment

Configuration of PhpStorm's Deployment feature is the key to making your development simple and enjoyable.

## Configuration

> This is for the first-time setup ONLY!  If you have already configured a Deployment, skip down to `Mapping Additional Plugins`.

### First-Time Setup:

Navigate to `Tools | Deployment | Configuration` via the editor's menu.

Create a new deployment, using any name you like.  I typically use the name `vagrant` when using a
[Local Development Environment (Vagrant)](../vagrant.md) and then configure similar to the following:

#### Connection
- Visible only for this project: [X]
- Type: SFTP
- SSH Configuration (Create Configuration as needed):
  > This assumes the default configuration from the Vagrantfile, adjust as needed. 
  - Host: `192.168.50.10` (or `VBOX_ADDRESS` configured in the Vagrantfile)
  - Port: `22`
  - Username: `vagrant`
  - Local port: `<Dynamic>` (Leave Blank)
  - Authentication type: `Password`
  - Password: `vagrant`
  - Save Password: [X]
  - Parse config file ~/.ssh/config: [X]
  > All remaining settings can be left as is. Use the `Test Connection` button to ensure correct configuration and communication.
- Root path: `/home/vagrant`
- Web server URL: `https://192.168.50.10`
> All remaining settings can be left as is.

#### Mappings
> Substitute `PROJECT_PATH` and `PLUGIN_NAME` with your actual information.
- Local path: `PROJECT_PATH/plugins/PLUGIN_NAME/src/` (the absolute path to the Plugin's `src/` folder)
- Deployment path: `/home/unms/data/ucrm/ucrm/data/plugins/PLUGIN_NAME/`
- Web path: /src/_plugins/PLUGIN_NAME/

#### Excluded Paths
> Substitute `PROJECT_PATH` and `PLUGIN_NAME` with your actual information.
- (Local Path) `PROJECT_PATH/plugins/PLUGIN_NAME/src/vendor/`


### Mapping Additional Plugins:
> Be sure any Plugins you map here have already been created and installed in UNMS before continuing.

Navigate to `Tools | Deployment | Configuration` via the editor's menu.

Choose the deployment you previously created.

#### Connection
No changes need to be made here for additional mappings.

#### Mappings
Click the `Add New Mapping` button (if this is only the second Plugin being mapped) or
the `+` button (when you have already added two or more mappings).

Configure the columns as you did in the `Mappings` step from `First-Time Setup`, being sure to change the `PLUGIN_NAME`
part of the paths for each row.

#### Excluded Paths
Follow the same steps as you did in the `Excluded Paths` step from `First-Time Setup`, being sure to change the `PLUGIN_NAME`
part of the paths for each row.

## Options
The following changes are based on my own preferences and should be changed to suit your own needs.

Navigate to `Tools | Deployment | Options` via the editor's menu.

- `Upload changed files automatically to the default server`: `On explicit save action (Ctrl+S)`
