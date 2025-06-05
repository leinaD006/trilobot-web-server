#!/bin/bash
set -e

echo "Installing example files for Trilobot..."

# Check if examples directory exists, if not, create it
if [ ! -d "$EXAMPLE_DIR" ]; then
    mkdir -p "$EXAMPLE_DIR"
fi

if [ ! -d "$EXAMPLE_DIR" ]; then
    mkdir -p "$EXAMPLE_DIR/trilobot"
fi

# Installs example python files for Trilobot
git clone https://github.com/pimoroni/trilobot-python "$INSTALL_DIR/trilobot-python"

# Copy example files to the examples directory
cp -r "$INSTALL_DIR/trilobot-python/examples"/* "$EXAMPLE_DIR/trilobot/"

# Delete the cloned repository
rm -rf "$INSTALL_DIR/trilobot-python"

if [ ! -d "$EXAMPLE_DIR" ]; then
    mkdir -p "$EXAMPLE_DIR/bme680"
fi

git clone https://github.com/pimoroni/bme680-python "$INSTALL_DIR/bme680-python"
cp -r "$INSTALL_DIR/bme680-python/examples"/* "$EXAMPLE_DIR/bme680/"
rm -rf "$INSTALL_DIR/bme680-python"

if [ ! -d "$EXAMPLE_DIR" ]; then
    mkdir -p "$EXAMPLE_DIR/mas301"
fi

git clone https://github.com/pimoroni/msa301-python "$INSTALL_DIR/msa301-python"
cp -r "$INSTALL_DIR/msa301-python/examples"/* "$EXAMPLE_DIR/msa301"
rm -rf "$INSTALL_DIR/msa301-python"

echo "Example files installed successfully in $EXAMPLE_DIR"