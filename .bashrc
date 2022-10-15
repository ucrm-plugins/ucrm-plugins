#!/usr/bin/env bash

# cspell:ignore pathadd

# Function to prepend a directory to the PATH only if it exists AND is not already included.
path_prepend() {
    if [ -d "$1" ] && [[ ":$PATH:" != *":$1:"* ]]; then
        PATH="$1${PATH:+":$PATH"}"
    fi
}

# Function to append a directory to the PATH only if it exists AND is not already included.
path_append() {
    if [ -d "$1" ] && [[ ":$PATH:" != *":$1:"* ]]; then
        PATH="${PATH:+"$PATH:"}$1"
    fi
}


# Determine the Project's directory in relation to this script.
PROJECT_DIR=$(realpath "$( cd -- "$( dirname -- "$(readlink -f "${BASH_SOURCE[0]}")" )" &> /dev/null && pwd )"/.)

# NOTE: The ENVIRONMENT should already be set on the guest (via /etc/environment), so this is the best way to determine
# whether we're on the host.  It should work with any host (i.e. Windows, Darwin, Linux, etc)!
VIRTUAL_ENV=${VIRTUAL_ENV:-host}

# Set any directories to be added to $PATH...
path_append "$(composer -n config --global home 2> /dev/null)"/vendor/bin
path_append "$(which npm 2> /dev/null)"/vendor/bin
path_append "$PROJECT_DIR"/vendor/bin
path_append "$PROJECT_DIR"/bin

# Export the project directory for use in the terminal.
export PROJECT_DIR=$PROJECT_DIR
export VIRTUAL_ENV=$VIRTUAL_ENV

# shellcheck disable=SC2164
cd "$PROJECT_DIR"
