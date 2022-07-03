#### environment/env

Maps to `/home/unms/app/` inside the Vagrant machine.

> _**IMPORTANT:** This folder contains a `docker-compose.override.yml` that makes changes to the default installation of UISP.
> These changes are for improving the development experience and should **NEVER** be used on a production installation._ 

Some of these changes include:
- Exposing the PostgreSQL port 5432 to the development host for easy access.
- Adding and configuring the Xdebug extension to the UCRM container.
- Mounting additional volumes to expose key Plugin folders to the development host.