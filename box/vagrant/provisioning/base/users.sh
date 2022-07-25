#!/bin/bash

echo "Updating the root password..."
echo "root:$ROOT_PASSWORD" | sudo chpasswd
