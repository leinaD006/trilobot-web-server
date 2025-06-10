#!/bin/bash
set -e

# Sets up basic requirements for install scripts to be atomic

sudo apt update
sudo apt upgrade -y

# Add web document roots
if [ ! -d "/var/www/html" ]; then
    sudo mkdir -p /var/www/html
    sudo chown -R $USER:$USER /var/www/html
    sudo chmod -R 0755 /var/www/html
fi
if [ ! -d "/var/www/python" ]; then
    sudo mkdir -p /var/www/python
    sudo chown -R $USER:$USER /var/www/python
    sudo chmod -R 0755 /var/www/python
fi
# Install packages

# List of packages to install
packages=(
    "rsync"
    "python3-venv"
    "python3-dev"
    "python3-libcamera"
    "libcap-dev"
    "avformat"
    "avcodec"
    "avdevice"
    "avutil"
    "avfilter"
    "swscale"
    "swresample"
)
# Loop through the packages and install them
for package in "${packages[@]}"; do
    if ! dpkg -l | grep -q "$package"; then
        echo "Installing $package..."
        sudo apt install -y "$package"
    else
        echo "$package is already installed"
    fi
done