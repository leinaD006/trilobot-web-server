#!/bin/bash
set -e

# Set up python environment
python3 -m venv venv

# Switch to environment
source venv/bin/activate

# Install required packages
pip install -r $INSTALL_DIR/requirements.txt

echo "Python environment set up successfully."