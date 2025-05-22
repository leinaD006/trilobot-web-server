#!/bin/bash

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
# Install rsync

if ! command -v rsync &> /dev/null
then
    echo "rsync could not be found, installing..."
    sudo apt-get install rsync -y
else
    echo "rsync is already installed"
fi

# Install python3-venv
if ! command -v python3-venv &> /dev/null
then
    echo "python3-venv could not be found, installing..."
    sudo apt-get install python3-venv -y
else
    echo "python3-venv is already installed"
fi