#!/usr/bin/env bash

if [[ "$(uname)" != MINGW64_NT* ]]; then
    echo "The included .bashrc script can only be used with Cmder on Windows!"
else
    # WINDOWS...

    # Set the directory of this script.
    _BASHRC_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )

    # Set the project directory.
    PROJECT_DIR=$(realpath "$_BASHRC_DIR"/..)

    # Set any additional directories with executables.
    ARR_BIN_DIR=(
        .dev/node/bin
        .dev/php/bin
        .dev/vssh/bin
        bin
    )

    # Export the project directory for later use.
    export PROJECT_DIR=$PROJECT_DIR

    # Loop through each additional directory...
    for BIN in "${ARR_BIN_DIR[@]}"; do

        # Add the current bin directory to the PATH env.
        export PATH="$PROJECT_DIR/$BIN:$PATH"

        # Loop through each BAT file in the directory
        for i in "$PROJECT_DIR/$BIN"/*.bat; do

            # IF the file does not exist, THEN move on to the next file!
            [ -f "$i" ] || break

            # Get the current file's name.
            file_ext=$(basename -- "$i")
            filename="${file_ext%.*}"

            # And alias the command (by name) to it's BAT file.
            ## shellcheck disable=SC2139,SC2086
            #alias $filename="$filename.bat"

            #cp "$PROJECT_DIR/$BIN/$filename".bat "$PROJECT_DIR/$BIN/$filename"
            #chmod +x "$PROJECT_DIR/$BIN/$filename"
        done
    done
fi
