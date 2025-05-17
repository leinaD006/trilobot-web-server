#!/bin/bash

echo "Installing example files for Trilobot..."

# Check if examples directory exists, if not, create it
if [ ! -d "$EXAMPLE_DIR" ]; then
    mkdir -p "$EXAMPLE_DIR"
fi

# Installs example python files for Trilobot
git clone https://github.com/pimoroni/trilobot-python "$INSTALL_DIR/trilobot-python"

# Copy example files to the examples directory
cp -r "$INSTALL_DIR/trilobot-python/examples"/* "$EXAMPLE_DIR"

# Delete the cloned repository
rm -rf "$INSTALL_DIR/trilobot-python"

echo "Example files installed successfully in $EXAMPLE_DIR"