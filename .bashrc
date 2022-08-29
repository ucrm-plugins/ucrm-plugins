#!/usr/bin/env bash

if [[ "$(uname)" != MINGW64_NT* ]]; then
    echo "The included .bashrc script can only be used with Git Bash on Windows!"
else
    # WINDOWS...
    PROJECT_DIR=$(realpath "$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"/.)

    # Export the project directory for use in the terminal.
    export PROJECT_DIR=$PROJECT_DIR

    # Set any directories to be added to $PATH...
    ARR_BIN_DIR=(
        vendor/bin
        dev/bin
    )

    # Loop through each additional directory...
    for BIN in "${ARR_BIN_DIR[@]}"; do

        # Add the current bin directory to the PATH env.
        # NOTE: Here we prepend it, as to prioritize our commands over existing ones!
        export PATH="$PROJECT_DIR/$BIN:$PATH"

        # Loop through each BAT file in the directory
        # NOTE: This is no longer necessary, as we've moved to pure bash scripts!
        for i in "$PROJECT_DIR/$BIN"/*.bat; do

            # IF the file does not exist, THEN move on to the next file!
            [ -f "$i" ] || break

            # Get the current file's name.
            #file_ext=$(basename -- "$i")
            #filename="${file_ext%.*}"

            # And alias the command (by name) to its BAT file.
            ## shellcheck disable=SC2139,SC2086
            #alias $filename="$filename.bat"

            #cp "$PROJECT_DIR/$BIN/$filename".bat "$PROJECT_DIR/$BIN/$filename"
            #chmod +x "$PROJECT_DIR/$BIN/$filename"
        done
    done
fi
