#!/bin/bash
# Set up python environment
python3 -m venv env
source env/bin/activate
pip install -r requirements.txt
echo "Python environment set up successfully."