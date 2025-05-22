#!/bin/bash
set -e

# Check if python directory exists, if not, create it
if [ ! -d "$PYTHON_DIR" ]; then
    mkdir -p "$PYTHON_DIR"
fi

# Set up python environment
python3 -m venv python/venv

# Switch to environment
source venv/bin/activate

# Install required packages
pip install -r $INSTALL_DIR/requirements.txt

echo "Python environment set up successfully."