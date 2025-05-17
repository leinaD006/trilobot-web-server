#!/bin/bash

# Activate the Python virtual environment
echo "Activating the Python virtual environment..."
# Check if the virtual environment directory exists
if [ ! -d "venv" ]; then
    echo "Error: Virtual environment directory 'venv' not found. Please run the install script first."
    exit 1
fi

# Activate the virtual environment
source venv/bin/activate
echo "Virtual environment activated. You can now run your Python scripts."
echo "To deactivate the virtual environment, run 'deactivate'."