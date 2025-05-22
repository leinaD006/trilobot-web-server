#!/bin/bash

# Symlink the web files to the web directory
# Check if the web directory exists
if [ ! -d "$WEB_ROOT" ]; then
    sudo mkdir -p "$WEB_ROOT"
fi

# Symlink the web files
sudo ln -s "$WEB_DIR" "$WEB_ROOT/html"

# Symlink the Python files
sudo ln -s "$PYTHON_DIR" "$WEB_ROOT/python"

echo "Symlinks created successfully in $WEB_ROOT"