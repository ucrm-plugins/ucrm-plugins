#!/bin/bash

set -e

PLUGINS_DIR=/data/ucrm/data/plugins
PLUGIN_NAME=$1

if [ ! -d $PLUGINS_DIR/$PLUGIN_NAME ]; then
    echo "Plugin not installed: $PLUGINS_DIR/$PLUGIN_NAME"
    exit 1
fi

cd $PLUGINS_DIR/$PLUGIN_NAME

PLUGIN_CMD=$2
PLUGIN_ARGS=${@:3}

$PLUGIN_CMD $PLUGIN_ARGS
