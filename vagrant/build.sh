#!/usr/bin/env bash

set -e

PROJECT_DIR=$(realpath "$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"/..)
#VAGRANT_DIR="$PROJECT_DIR"/vagrant

# Change to the project directory.
cd "$PROJECT_DIR" || exit

# Update the base box, if needed.
echo "Checking for base box updates..."
vagrant box update

# Build the current box.
echo "Building the box..."
vagrant up
