#!/usr/bin/env bash

set -e

PROJECT_DIR=$(realpath "$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"/..)
VAGRANT_DIR="$PROJECT_DIR/vagrant"

# Working Directory
cd "$PROJECT_DIR" || exit

UISP_VERSION=$(cat "${VAGRANT_DIR}/build_version")

ARGS=()
if [ -f .env ]; then
    TOKEN=$(awk -F "=" '/VAGRANTCLOUD_TOKEN/ {print $2}' .env)
    [ "$TOKEN" != "" ] && ARGS+=("--token" "$TOKEN")
fi

# Authenticate to Vagrant Cloud.  Appending '--token TOKEN' bypasses interactive login.
vagrant cloud auth login "${ARGS[@]}"

# Publish the updated version.
# NOTE: The time to upload/publish could take some time, as the file size is roughly 4GB.
vagrant cloud publish \
    --version-description "UISP $UISP_VERSION running on Ubuntu 20.04" \
    --release \
    --force \
    ucrm-plugins/uisp "$UISP_VERSION" \
    virtualbox \
    "$VAGRANT_DIR/uisp-$UISP_VERSION.box"

#vagrant cloud auth logout

# Remove the running "build" box.
vagrant destroy --force

# And optionally delete the package file and metadata.
rm -f "$VAGRANT_DIR/uisp-$UISP_VERSION.box"
