#!/bin/bash
set -e

# Check if working directory exists
if [ ! -d "/opt/ipaddress" ]; then
    sudo mkdir -p /opt/ipaddress
fi

sudo cp $INSTALL_DIR/files/ipaddress.service /etc/systemd/system/ipaddress.service

sudo systemctl daemon-reload
sudo systemctl enable ipaddress.service

