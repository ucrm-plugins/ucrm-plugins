#!/bin/bash

echo "Installing UISP..."
curl -fsSL https://uisp.ui.com/v1/install > /tmp/uisp_inst.sh
bash /tmp/uisp_inst.sh --version $UISP_VERSION

#chmod 775 -R /home/unms/
