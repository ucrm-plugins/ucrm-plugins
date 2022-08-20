#!/usr/bin/env bash

resolve_relative_path() (
    # If the path is a directory, we just need to 'cd' into it and print the new path.
    if [ -d "$1" ]; then
        cd "$1" || return 1
        pwd
    # If the path points to anything else, like a file or FIFO
    elif [ -e "$1" ]; then
        # Strip '/file' from '/dir/file'
        # We only change the directory if the name doesn't match for the cases where
        # we were passed something like 'file' without './'
        if [ ! "${1%/*}" = "$1" ]; then
            cd "${1%/*}" || return 1
        fi
        # Strip all leading slashes upto the filename
        echo "$(pwd)/${1##*/}"
    else
        return 1 # Failure, neither file nor directory exists.
    fi
)


SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
export PROJECT_DIR=$( resolve_relative_path "$SCRIPT_DIR"/.. )
export PATH="$PROJECT_DIR/.php/bin:$PATH"
export PATH="$PROJECT_DIR/bin:$PATH"
#export PATH="$PROJECT_DIR/vendor/bin:$PATH"
