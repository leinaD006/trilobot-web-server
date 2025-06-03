#!/bin/bash
set -e

# Set up the ipaddress service

sudo mkdir -p /opt/ipaddress

sudo cp $INSTALL_DIR/ipaddress.service /etc/systemd/system/ipaddress.service

sudo systemctl daemon-reload
sudo systemctl enable ipaddress.service

