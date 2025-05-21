#!/bin/bash

# Main install script

# Set the install directory relative to this script
SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
export SCRIPT_DIR
export INSTALL_DIR="$SCRIPT_DIR/install"
export EXAMPLE_DIR="$SCRIPT_DIR/python/examples"
export HTML_DIR="$SCRIPT_DIR/html"
export PYTHON_DIR="$SCRIPT_DIR/python"
export WEB_DIR="/var/www

# Function to display usage information
show_usage() {
    echo "Usage: $0 [options]"
    echo "Options:"
    echo "  (no arguments)   Run all install scripts"
    echo "  --help           Show this help message"
    echo "  <key1> <key2>... Run specific install scripts by their keys"
    echo ""
    echo "Available install scripts:"
    
    # List all available install scripts with their keys
    for script in "$INSTALL_DIR"/*.sh; do
        if [ -f "$script" ]; then
            key=$(basename "$script" .sh)
            echo "  $key"
        fi
    done
}

# Check if help is requested
if [[ "$1" == "--help" ]]; then
    show_usage
    exit 0
fi

# Check if install directory exists
if [ ! -d "$INSTALL_DIR" ]; then
    echo "Error: Install directory not found at $INSTALL_DIR"
    exit 1
fi

# Function to run a specific install script
run_script() {
    local script="$INSTALL_DIR/$1.sh"
    if [ -f "$script" ]; then
        echo "Running install script: $1"
        bash "$script"
        local status=$?
        if [ $status -ne 0 ]; then
            echo "Error: Script $1 failed with exit code $status"
            return 1
        fi
    else
        echo "Warning: Install script $1 not found"
        return 1
    fi
    return 0
}

# Run scripts based on arguments
if [ $# -eq 0 ]; then
    # No arguments provided, run all scripts in alphabetical order
    echo "Running all install scripts..."
    
    # Find all .sh files in install directory and sort them
    scripts=$(find "$INSTALL_DIR" -name "*.sh" | sort)
    
    # Track if any script fails
    failed=0
    
    # Run each script
    for script in $scripts; do
        key=$(basename "$script" .sh)
        run_script "$key" || failed=1
    done
    
    if [ $failed -eq 1 ]; then
        echo "One or more install scripts failed"
        exit 1
    fi
else
    # Specific scripts requested
    failed=0
    for key in "$@"; do
        run_script "$key" || failed=1
    done
    
    if [ $failed -eq 1 ]; then
        echo "One or more install scripts failed"
        exit 1
    fi
fi

echo "Installation complete!"
exit 0