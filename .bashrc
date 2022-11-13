#!/usr/bin/env bash

# Prepend the specified directory to the PATH only if it exists AND is not already included.
path_prepend_exists() {
    if [ -d "$1" ] && [[ ":$PATH:" != *":$1:"* ]]; then
        PATH="$1${PATH:+":$PATH"}"
    fi
}

# Prepend the specified directory to the PATH only if is not already included.
path_prepend() {
    if [[ ":$PATH:" != *":$1:"* ]]; then
        PATH="$1${PATH:+":$PATH"}"
    fi
}

# Append the specified directory to the PATH only if it exists AND is not already included.
path_append_exists() {
    if [ -d "$1" ] && [[ ":$PATH:" != *":$1:"* ]]; then
        PATH="${PATH:+"$PATH:"}$1"
    fi
}

# Append the specified directory to the PATH only if is not already included.
path_append() {
    if [[ ":$PATH:" != *":$1:"* ]]; then
        PATH="${PATH:+"$PATH:"}$1"
    fi
}

# Converts the specified path from Windows to Linux.
win_path() {
    local path
    path=$(sed -E "s/([A-Za-z]):/\/\L\1/g" <<< "${1//\\//}")
    echo "$path"
}

# Determine the Project's directory in relation to this script.
PROJECT_DIR=$(realpath "$( cd -- "$( dirname -- "$(readlink -f "${BASH_SOURCE[0]}")" )" &> /dev/null && pwd )"/.)

# NOTE: The VIRTUAL_ENV should already be set on the guest (via /etc/environment), so this is the best way to determine
# whether we're on the host.  It should work with any host (i.e. Windows, Darwin, Linux, etc)!
VIRTUAL_ENV=${VIRTUAL_ENV:-host}

#COMPOSER_HOME=$(composer -n config --global home 2> /dev/null)
#COMPOSER_HOME=${COMPOSER_HOME//C://c}
COMPOSER_HOME=$(win_path "$APPDATA")/Composer
NPM_HOME=$(win_path "$APPDATA")/npm

# Set any directories to be added to $PATH...
#path_append "$COMPOSER_HOME/vendor/bin"
#path_append "$(which npm 2> /dev/null)"/vendor/bin
path_append "$COMPOSER_HOME/vendor/bin"
path_append "$NPM_HOME/node_modules"
path_append "$PROJECT_DIR/vendor/bin"
path_append "$PROJECT_DIR/bin"

# Export the project directory for use in the terminal.
export PROJECT_DIR=$PROJECT_DIR
export VIRTUAL_ENV=$VIRTUAL_ENV

# shellcheck disable=SC2164
cd "$PROJECT_DIR"
