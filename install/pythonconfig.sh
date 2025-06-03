#!/bin/bash
set -e

# Check if python directory exists, if not, create it
if [ ! -d "$PYTHON_DIR" ]; then
    mkdir -p "$PYTHON_DIR"
fi

# Create config file if it doesn't exist
if [ ! -f "$PYTHON_DIR/buttons.conig" ]; then
    touch "$PYTHON_DIR/buttons.config"
    echo "BASE_DIR=$PYTHON_DIR" > "$PYTHON_DIR/buttons.config"
    echo "BUTTON_A=examples/flash_underlights.py" >> "$PYTHON_DIR/buttons.config"
    echo "BUTTON_B=user/test1.py" >> "$PYTHON_DIR/buttons.config"
    echo "BUTTON_X=user/test2.py" >> "$PYTHON_DIR/buttons.config"
    echo "BUTTON_Y=user/test3.py" >> "$PYTHON_DIR/buttons.config"
fi

echo "Python configuration file set up successfully at $PYTHON_DIR/buttons.config."