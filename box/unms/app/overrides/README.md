#### ./box/unms/app/overrides

This folder is bi-directionally synchronized with `/home/unms/app/overrides` inside your Vagrant box.

> _**IMPORTANT:** This folder contains a `docker-compose.override.yml` that makes changes to the default installation of UISP.
> These changes are for improving the development experience and should **NEVER** be used on a production installation._ 

These overrides include:
- Exposing the PostgreSQL port 5432 to the host for easy access.
- Adding and configuring the Xdebug extension in the UCRM container.

> _**NOTE:** The file `docker-compose.override.yml` is symlinked at `/home/unms/app/` to allow for the standard UISP
> `docker-compose` commands to function without any changes.  For example, when running a normal Compose command like
> `docker-compose -p unms up -d`, the overrides will be automatically included._
