#!/bin/bash

echo "Updating root password..."
echo "root:$ROOT_PASSWORD" | sudo chpasswd
