#!/usr/bin/env bash

set -e

PROJECT_DIR=$(realpath "$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"/..)
VAGRANT_DIR="$PROJECT_DIR/vagrant"

# Change to the box's working directory.
cd "$PROJECT_DIR" || exit

# Get the UISP version from the built box.
UISP_VERSION=$(cat "$VAGRANT_DIR/build_version")

if [ -f "$VAGRANT_DIR/uisp-$UISP_VERSION.box" ]; then
    echo "Removing existing package..."
    rm -f "$VAGRANT_DIR/uisp-$UISP_VERSION.box"
fi

# Package for use as a base box.
echo "Packaging box: uisp-$UISP_VERSION..."
vagrant package --output "$VAGRANT_DIR/uisp-$UISP_VERSION.box"

# Add the box to the local repo, so there is no need to download it later.
echo "Adding box: uisp-${UISP_VERSION}..."
vagrant box add "$VAGRANT_DIR/uisp-$UISP_VERSION.box" --name "ucrm-plugins/uisp-$UISP_VERSION" --force

# Clean up...
echo "Cleaning up..."
vagrant destroy --force
rm -rf "$PROJECT_DIR/.vagrant"
