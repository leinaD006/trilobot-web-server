#!/bin/bash
set -e

# Check if python directory exists, if not, exit with an error
if [ ! -d "$PYTHON_DIR" ]; then
    echo "Error: Python directory $PYTHON_DIR does not exist."
    exit 1
fi

# Create config file if it doesn't exist
if [ ! -f "$PYTHON_DIR/buttons.conf" ]; then
    touch "$PYTHON_DIR/buttons.conf"
    echo "BASE_DIR=$PYTHON_DIR" > "$PYTHON_DIR/buttons.conf"
    echo "BUTTON_A=examples/flash_underlights.py" >> "$PYTHON_DIR/buttons.conf"
    echo "BUTTON_B=user/test1.py" >> "$PYTHON_DIR/buttons.conf"
    echo "BUTTON_X=user/test2.py" >> "$PYTHON_DIR/buttons.conf"
    echo "BUTTON_Y=user/test3.py" >> "$PYTHON_DIR/buttons.conf"
fi

echo "Python configuration file set up successfully at $PYTHON_DIR/buttons.config."