#!/usr/bin/env bash

if [[ "$(uname)" != MINGW64_NT* ]]; then
    echo "The included .bashrc script can only be used with Git Bash on Windows!"
else
    # WINDOWS...
    PROJECT_DIR=$(realpath "$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"/..)

    # Export the project directory for use in the terminal.
    export PROJECT_DIR=$PROJECT_DIR

    # Set any directories to be added to $PATH...
    ARR_BIN_DIR=(
        dev/bin
        vendor/bin
    )

    # Loop through each additional directory...
    for BIN in "${ARR_BIN_DIR[@]}"; do

        # Add the current bin directory to the PATH env.
        # NOTE: Here we prepend it, as to prioritize our commands over existing ones!
        export PATH="$PROJECT_DIR/$BIN:$PATH"


    done

    USER_BIN=$HOME/bin

    if [ ! -f "$USER_BIN"/mkcert.exe ]; then
        echo "Installing mkcert into User's Git Bash..."
        mkdir -p "$USER_BIN"
        curl -JL https://dl.filippo.io/mkcert/latest?for=windows/amd64 -o "$USER_BIN"/mkcert.exe
    fi

# https://github.com/facebook/zstd/releases/download/v1.5.2/zstd-v1.5.2-win64.zip
# https://mirror.msys2.org/msys/x86_64/rsync-3.2.3-2-x86_64.pkg.tar.zst

#    rm -f .bash_installed
#
#    for i in /usr/bin/*; do
#        if [[ $i =~ /usr/bin/([^\.]*)$ ]] || [[ $i =~ /usr/bin/(.*).exe$ ]]; then
#            echo "${BASH_REMATCH[1]}" >> .bash_installed
#        fi
#    done


fi
