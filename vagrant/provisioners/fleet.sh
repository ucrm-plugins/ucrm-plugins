#!/bin/bash

#cd /tmp
curl -sSL "https://download.jetbrains.com/product?code=FLL&release.type=eap&platform=linux_x64" --output fleet
chmod +x fleet

./fleet launch workspace -- --auth=accept-everyone --enableSmartMode --projectDir=/src/ucrm-plugins
