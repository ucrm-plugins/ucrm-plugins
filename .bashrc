#!/usr/bin/env bash

# Function to prepend a directory to the PATH only if it exists AND is not already included.
pathadd() {
    if [ -d "$1" ] && [[ ":$PATH:" != *":$1:"* ]]; then
        PATH="$1${PATH:+":$PATH"}"
    fi
}

# Determine the Project's directory in relation to this script.
PROJECT_DIR=$(realpath "$( cd -- "$( dirname -- "$(readlink -f "${BASH_SOURCE[0]}")" )" &> /dev/null && pwd )"/.)

# NOTE: The ENVIRONMENT should already be set on the guest (via /etc/environment), so this is the best way to determine
# whether we're on the host.  It should work with any host (i.e. Windows, Darwin, Linux, etc)!
VIRTUAL_ENV=${VIRTUAL_ENV:-host}

# Set any directories to be added to $PATH...
pathadd "$(composer -n config --global home 2> /dev/null)"/vendor/bin
pathadd "$(which npm 2> /dev/null)"/vendor/bin
pathadd "$PROJECT_DIR"/vendor/bin
pathadd "$PROJECT_DIR"/bin

# Export the project directory for use in the terminal.
export PROJECT_DIR=$PROJECT_DIR
export VIRTUAL_ENV=$VIRTUAL_ENV

# shellcheck disable=SC2164
cd "$PROJECT_DIR"
