# Commands

The following commands are currently supported:
- [upm](#upm)
- [vssh](#vssh)


## upm

The UCRM Plugin Manager contains some useful commands to help expedite Plugin development.

The following sub-commands are currently supported:
- [bundle](#bundle)
- [create](#create)
- [exec](#exec)

---

### bundle
`upm bundle <name>`

Can be used as an alternative to the `pack-plugin.php` script.  The main reason for the alternative is to allow for a
list of exclusions.

| Option | Description                                                                 |
|--------|-----------------------------------------------------------------------------|
| name   | The name of the Plugin, also used as the folder name.                       |

Bundles an existing Plugin using the following steps:
1. Validates the Plugin `manifest.json` and file/folder structure.
2. Executes `composer install` if a composer.json file is found.
3. Creates a bundle using the `composer archive` command.
4. Updates the `plugins.json` file.

composer.json
```json
{
    "config": {
        "archive-dir": "../"
    },
    "archive": {
        "exclude": [
            "data/plugin.log",
            "ucrm.json",
            ".ucrm*"
        ]
    }
}
```
| Node               | Description                                                                               |
|--------------------|-------------------------------------------------------------------------------------------|
| config.archive-dir | The path to output the bundle, can be absolute or relative to the Plugin's `src/` folder. |
| archive.exclude    | An array of glob patterns, indicating items that should be excluded from the bundle.      |

> _**NOTE:** The `composer archive` command is executed with the `--format=zip` flag automatically and will override
> the `config.archive-format` even if it is set in the `composer.json` file._

---

### create
`upm create [options] <name> <template>`

Creates a new Plugin, based upon the specified Template. For more information, see the documentation for
[Templates](../templates/README.md)

| Option / Argument | Alias | Description                                                                  |
|-------------------|-------|------------------------------------------------------------------------------|
| --force           | -f    | Forces deletion and re-creation of an existing Plugin, **USE WITH CAUTION**. |
| --map             | -m    | Additionally runs the `upm map <name>` command after creation.               |
| name              |       | The name of the Plugin, also used as the folder name.                        |
| template          |       | The name of a Template from the `template/` folder or a git URL.             |

Creates a new Plugin using the following steps:
1. Deletes any identically named Plugin if it exists AND the `--force` option is specified.
2. Copies (from `template/<template>`) or downloads (from the specified Git URL) and extracts the contents of the
Template to the `plugins/<name>` folder.
3. Runs the simple Templater.  For more information, see the documentation for [Templates](../templates/README.md)
4. Creates the `www/` folder and associated router file, when a public.php file is included in the Template.
5. Creates PhpStorm specific server mappings, if the `--map` option is specified.
6. Displays some Plugin specific information.

---

### exec
`upm exec <name> <exec> [...<args>]`

Remotely executes a command inside the UCRM container and from within the specified Plugin's folder.

> _**IMPORTANT:** The functionality of the `upm exec` command assumes you are using the
> [Local Development Environment (Vagrant)](../docs/vagrant.md). If you are NOT, then the command will likely fail._

| Option / Argument | Description                                           |
|-------------------|-------------------------------------------------------|
| name              | The name of the Plugin, also used as the folder name. |
| exec              | The command to execute.                               |
| args              | Any optional arguments to pass to the command.        |

Executes the command using the following steps:
1. Creates a TTY inside the UCRM container.
2. Change directory to the Plugin's source folder.
3. Execute the command, given all the provided arguments.

> _**NOTE: This is most useful for things like `upm exec <name> composer install` after dependencies are added/removed
> from the `composer.json` file.  We do NOT typically want to synchronize the entire `vendor/` folder between our IDE
> and the web server, so this is a simple alternative._


## vssh

> _**IMPORTANT:** The functionality of the `vssh` command assumes you are using the
> [Local Development Environment (Vagrant)](../docs/vagrant.md). If you are NOT, then the command will likely fail._

On Windows, the built-in `vagrant ssh` command is VERY slow.  As an alternative, you can use this `vssh` command as an
alias that runs much more quickly.  It can be called using simply `vssh` to open an interactive secure shell or as
`vssh <command> [...args]` where the command (with optional arguments) are executed directly using SSH.

> _**NOTE:** This command will not function until after a successful `vagrant up` creates the box and triggers the
> VSSH Configuration._
