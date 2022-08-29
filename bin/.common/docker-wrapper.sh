#!/usr/bin/env bash

# IF we are running this script from within Git Bash for Windows...
if [[ "$(uname)" = MINGW64_NT* ]]; then
    # ...THEN disable automatic path prefixing
    # NOTE: Otherwise paths starting with / are prefixed with "C:\Program Files\Git"
    export MSYS_NO_PATHCONV=1
fi

# Set the project directory.
PROJECT_DIR=$(realpath "$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"/../..)
CURRENT_DIR=$(pwd)

LOWER_CMD=$(echo "$1" | tr "[:upper:]" "[:lower:]")
#UPPER_CMD=$(echo "$1" | tr "[:lower:]" "[:upper:]")

IMAGE_ORG=
IMAGE_CMD=
IMAGE_TAG=

shopt -s dotglob
read -ra DIRS -d "" <<<"$(ls -d "$PROJECT_DIR"/bin/*/)"
for i in "${DIRS[@]}"
do
    DIR="${i%/}"
    CMD=$(basename "$DIR")

    if [ "$CMD" == ".$LOWER_CMD" ] ; then

        ENV="$DIR"/environment.ini
        if [ -f "$ENV" ]; then
            source <(grep "=" "$ENV")
        else
            echo "Could not find an environment.ini for the $CMD command!"
            exit
        fi
    fi
done

if [ "$IMAGE_ORG" == ""  ] || [ "$IMAGE_CMD" == ""  ] || [ "$IMAGE_TAG" == ""  ]; then
    echo "The $LOWER_CMD command is not currently supported via Docker and must be installed on the local machine!"
    exit
fi

cd "$PROJECT_DIR/bin/.$IMAGE_CMD" || exit

DOCKER_NAME="$IMAGE_ORG/$IMAGE_CMD:$IMAGE_TAG"

docker image inspect "$DOCKER_NAME" >/dev/null 2>&1 || \
    docker build -t "$DOCKER_NAME" --build-arg "VERSION=$IMAGE_TAG" .
# "$PROJECT_DIR/.dev/$IMAGE_CMD"


## declare an array variable
declare -a ARGS=(
    --interactive
    --tty
    --rm
    --env "PROJECT_DIR=$PROJECT_DIR"
    --env "CURRENT_DIR=$CURRENT_DIR"
    --env "WORKING_DIR=$MOUNT_DIR"
    --volume "$PROJECT_DIR:$MOUNT_DIR"
    --workdir "$MOUNT_DIR"
    --name "$IMAGE_CMD-$IMAGE_TAG"
)

DOCKER_ARGS=
for i in "${ARGS[@]}"
do
   DOCKER_ARGS="$DOCKER_ARGS $i"
done
