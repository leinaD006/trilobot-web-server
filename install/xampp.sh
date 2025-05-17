#!/bin/bash

# Dowload and install XAMPP
echo "Downloading XAMPP..."
wget -P $INSTALL_DIR https://sourceforge.net/projects/xampp/files/XAMPP%20Linux/8.2.12/xampp-linux-x64-8.2.12-0-installer.run 
echo "Installing XAMPP..."
chmod +x $INSTALL_DIR/xampp-linux-x64-8.2.12-0-installer.run
sudo ./$INSTALL_DIR/xampp-linux-x64-8.2.12-0-installer.run
echo "XAMPP installed successfully."

# Start XAMPP
echo "Starting XAMPP..."
sudo /opt/lampp/lampp start
echo "XAMPP started successfully."