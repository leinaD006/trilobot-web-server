#!/bin/bash

# Symlink the web files to the web directory
# Check if the web directory exists
if [ ! -d "$WEB_DIR" ]; then
    mkdir -p "$WEB_DIR"
fi

# Symlink the web files
ln -s "$HTML_DIR" "$WEB_DIR/html"

# Symlink the Python files
ln -s "$PYTHON_DIR" "$WEB_DIR/python"

echo "Symlinks created successfully in $WEB_DIR"