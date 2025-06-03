#!/bin/bash
set -e

# Set up the ipaddress service

mkdir -p /opt/ipaddress

cp $INSTALL_DIR/ipaddress.service /etc/systemd/system/ipaddress.service

systemctl daemon-reload
systemctl enable ipaddress.service

