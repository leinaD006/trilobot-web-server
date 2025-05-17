#!/bin/bash
# Set up python environment
python3 -m venv venv
source venv/bin/activate
pip install -r $INSTALL_DIR/requirements.txt
echo "Python environment set up successfully."