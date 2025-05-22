#!/bin/bash

# Main install script

# Set the install directory relative to this script
SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
export SCRIPT_DIR
export INSTALL_DIR="$SCRIPT_DIR/install"
export EXAMPLE_DIR="$SCRIPT_DIR/python/examples"
export WEB_DIR="$SCRIPT_DIR/web"
export PYTHON_DIR="$SCRIPT_DIR/python"
export WEB_ROOT="/var/www"

failed_scripts=()
successful_scripts=()
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
    local script="$1"
    local script_key=$(basename "$script" .sh)
    
    if [ -f "$script" ]; then
        echo "Running install script: $script_key"
        if bash "$script"; then
            echo "✓ Script $script_key completed successfully"
            successful_scripts+=("$script_key")
            return 0
        else
            local status=$?
            echo "✗ Script $script_key failed with exit code $status"
            failed_scripts+=("$script_key")
            return 1
        fi
    else
        echo "✗ Install script $script_key not found"
        failed_scripts+=("$script_key (not found)")
        return 1
    fi
}

# Function to display execution summary
show_summary() {
    echo ""
    echo "======================================="
    echo "INSTALLATION SUMMARY"
    echo "======================================="
    
    if [ ${#successful_scripts[@]} -gt 0 ]; then
        echo "✓ Successful scripts (${#successful_scripts[@]}):"
        for script in "${successful_scripts[@]}"; do
            echo "  - $script"
        done
    fi
    
    if [ ${#failed_scripts[@]} -gt 0 ]; then
        echo ""
        echo "✗ Failed scripts (${#failed_scripts[@]}):"
        for script in "${failed_scripts[@]}"; do
            echo "  - $script"
        done
        echo ""
        echo "Please check the output above for specific error details."
    fi
    
    echo "======================================="
}

# Run scripts based on arguments
if [ $# -eq 0 ]; then
    # No arguments provided, run all scripts in alphabetical order
    echo "Running all install scripts..."
    echo ""
    
    # Find all .sh files in install directory and sort them
    scripts=$(find "$INSTALL_DIR" -name "*.sh" | sort)
    
    # Check if any scripts exist
    if [ -z "$scripts" ]; then
        echo "No install scripts found in $INSTALL_DIR"
        exit 1
    fi

    # Run preinstall.sh first
    if [ -f "$INSTALL_DIR/preinstall.sh" ]; then
        if !run_script "$INSTALL_DIR/preinstall.sh"; then
            echo "Pre-install script failed. Exiting."
            exit 1
        fi
    else
        echo "Warning: Pre-install script not found"
        exit 1
    fi
    
    # Run each script
    for script in $scripts; do
        # Skip preinstall.sh as it's already run
        if [[ "$script" == *"/preinstall.sh" ]]; then
            continue
        fi
        
        run_script "$key"
        echo "" # Add spacing between scripts
    done

    # Run deploy.sh last
    run_script "$SCRIPT_DIR/deploy.sh" || failed=1
    
    if [ $failed -eq 1 ]; then
        echo "One or more install scripts failed"
        exit 1
    fi
else
    # Specific scripts requested
    echo "Running specified install scripts..."
    echo ""
    
    for key in "$@"; do
        run_script "$key"
        echo "" # Add spacing between scripts
    done
fi

# Show summary
show_summary

# Exit with appropriate code
if [ ${#failed_scripts[@]} -gt 0 ]; then
    echo "Installation completed with ${#failed_scripts[@]} failure(s)."
    exit 1
else
    echo "Installation completed successfully!"
    exit 0
fi