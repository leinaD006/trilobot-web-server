#!/bin/bash

# Activate the Python virtual environment
# Check if the virtual environment directory exists
if [ ! -d "env" ]; then
    echo "Error: Virtual environment directory 'env' not found. Please run the install script first."
    exit 1
fi

# Activate the virtual environment
source env/bin/activate
echo "Virtual environment activated. You can now run your Python scripts."
echo "To deactivate the virtual environment, run 'deactivate'."