

Creating an updated package
> Used to publish releases to Vagrant Cloud

```bash
# Working Directory
cd ${PROJECT_DIR}/vagrant

# Build the box
vagrant up

# Package for use as a base box
vagrant package --output uisp-1.4.5.box

# Add the box to the local repo, so there is no need to download it later.
vagrant box add uisp-1.4.5-PROVIDER.box --name ucrm-plugins/uisp-1.4.5

# Authenticate to Vagrant Cloud.  Appending '--token TOKEN' bypasses interactive login.
vagrant cloud auth login

# Publish the updated version.
# NOTE: The time to upload/publish could take some time, as the file size is roughly 4GB.
vagrant cloud publish --version-description "UISP 1.4.5 running on Ubuntu 20.04" \
    --release --force ucrm-plugins/uisp 1.4.5 PROVIDER uisp-1.4.5-PROVIDER.box

vagrant cloud provider upload uisp-plugins/uisp PROVIDER 1.4.5 uisp-1.4.5-PROVIDER.box

# Remove the running "build" box.
vagrant destroy --force

# And optionally delete the package file and metadata.
rm -f uisp-1.4.5-PROVIDER.box
rm -rf .vagrant/
```
