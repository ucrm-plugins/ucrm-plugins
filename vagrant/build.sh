#!/usr/bin/env bash

set -e

PROJECT_DIR=$(realpath "$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"/..)
VAGRANT_DIR="$PROJECT_DIR"/vagrant

# Change to the box's working directory.
cd "$VAGRANT_DIR" || exit

# Update the base box, if needed.
echo "Checking for base box updates..."
vagrant box update

# Build the current box.
echo "Building box..."
vagrant up

# Get the UISP version from the built box.
UISP_VERSION=$(cat "${VAGRANT_DIR}"/build_version)

# Package for use as a base box.
echo "Packaging box: uisp-${UISP_VERSION}..."
vagrant package --output "uisp-${UISP_VERSION}.box"

# Add the box to the local repo, so there is no need to download it later.
echo "Adding box: uisp-${UISP_VERSION}..."
vagrant box add uisp-"$UISP_VERSION".box --name ucrm-plugins/uisp-"$UISP_VERSION" --force

# Clean up...
echo "Cleaning up..."
vagrant destroy --force
rm -rf .vagrant/
