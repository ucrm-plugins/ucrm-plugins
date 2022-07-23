# Using UPM Commands

The UCRM Plugin Manager (UPM) is a set of command line tools to simplify Plugin development for UCRM.

## create

Used to scaffold a new Plugin based on any of the included Templates.

```
upm create [OPTIONS] PLUGIN_NAME [PLUGIN_TEMPLATE]
```

### Arguments

Currently, `PLUGIN_TEMPLATE` can only be one of the following:
- The name of a template in the local `templates/` folder
- A valid Git repository

### Options

Valid options include:

| OPTION          | DESCRIPTION                                                                                                |
|-----------------|------------------------------------------------------------------------------------------------------------|
| -f, --force     | Forces the deletion and recreation of an existing Plugin, use with caution!                                |
| -s, --submodule | __[NOT YET AVAILABLE]__ When used with a Git repository template, creates the template as a Git Submodule. |


### Execution

The `create` command performs the following actions, in this order:
1. Deletes any existing (identically named) Plugin from the `plugins/` folder, ONLY when the `--force` option is used.
2. Downloads and extracts (or copies when local) the Plugin Template from the specified location to the `plugins/` folder.
3. Runs the built-in `Templater` on all files (recursively) in the Template's `src/` folder.
4. Installs Composer dependencies, if a `composer.json` file exists.
5. Creates an initial bundle.
6. Provides additional instructions to the developer, including a link here!

See the [PhpStorm | Deployment](../phpstorm/deployment.md) documentation for some additional configuration steps.

## bundle
`upm bundle PLUGIN_NAME`

