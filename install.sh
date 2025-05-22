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

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

failed_scripts=()
successful_scripts=()
# Function to display usage information
show_usage() {
    echo -e "Usage: $0 [options]"
    echo -e "Options:"
    echo -e "  (no arguments)   Run all install scripts"
    echo -e "  --help           Show this help message"
    echo -e "  <key1> <key2>... Run specific install scripts by their keys"
    echo -e ""
    echo -e "Available install scripts:"
    
    # List all available install scripts with their keys
    for script in "$INSTALL_DIR"/*.sh; do
        if [ -f "$script" ]; then
            key=$(basename "$script" .sh)
            echo -e "  $key"
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
    echo -e "${RED}Error: Install directory not found at $INSTALL_DIR${NC}"
    exit 1
fi

# Function to run a specific install script
run_script() {
    local script="$1"
    local script_key=$(basename "$script" .sh)
    
    if [ -f "$script" ]; then
        echo -e "Running install script: $script_key"
        if bash "$script"; then
            echo -e "${GREEN}✓ Script $script_key completed successfully${NC}"
            successful_scripts+=("$script_key")
            return 0
        else
            local status=$?
            echo -e "${RED}✗ Script $script_key failed with exit code $status${NC}"
            failed_scripts+=("$script_key")
            return 1
        fi
    else
        echo -e "${RED}✗ Install script $script_key not found${NC}"
        failed_scripts+=("$script_key (not found)")
        return 1
    fi
}

# Function to display execution summary
show_summary() {
    echo -e ""
    echo -e "======================================="
    echo -e "INSTALLATION SUMMARY"
    echo -e "======================================="
    
    if [ ${#successful_scripts[@]} -gt 0 ]; then
        echo -e "${GREEN}✓ Successful scripts (${#successful_scripts[@]}):"
        for script in "${successful_scripts[@]}"; do
            echo -e "  - $script${NC}"
        done
    fi
    
    if [ ${#failed_scripts[@]} -gt 0 ]; then
        echo -e ""
        echo -e "${RED}✗ Failed scripts (${#failed_scripts[@]}):"
        for script in "${failed_scripts[@]}"; do
            echo -e "  - $script${NC}"
        done
        echo -e ""
        echo -e "Please check the output above for specific error details."
    fi
    
    echo -e "======================================="
}

# Run scripts based on arguments
if [ $# -eq 0 ]; then
    # No arguments provided, run all scripts in alphabetical order
    echo -e "Running all install scripts..."
    echo -e ""
    
    # Find all .sh files in install directory and sort them
    scripts=$(find "$INSTALL_DIR" -name "*.sh" | sort)
    
    # Check if any scripts exist
    if [ -z "$scripts" ]; then
        echo -e "${RED}No install scripts found in $INSTALL_DIR${NC}"
        exit 1
    fi

    # Run preinstall.sh first
    if [ -f "$INSTALL_DIR/preinstall.sh" ]; then
        if ! run_script "$INSTALL_DIR/preinstall.sh"; then
            echo -e "${RED}Pre-install script failed. Exiting.${NC}"
            exit 1
        fi
    else
        echo -e "${RED}Error: Pre-install script not found${NC}"
        exit 1
    fi
    
    # Run each script
    for script in $scripts; do
        # Skip preinstall.sh as it's already run
        if [[ "$script" == *"/preinstall.sh" ]]; then
            continue
        fi
        
        run_script "$script"
        echo -e "" # Add spacing between scripts
    done

    # Run deploy.sh last
    run_script "$SCRIPT_DIR/deploy.sh"
    
else
    # Specific scripts requested
    echo -e "Running specified install scripts..."
    echo -e ""
    
    for key in "$@"; do
        run_script "$key"
        echo -e "" # Add spacing between scripts
    done
fi

# Show summary
show_summary

# Exit with appropriate code
if [ ${#failed_scripts[@]} -gt 0 ]; then
    echo -e "${RED}Installation completed with ${#failed_scripts[@]} failure(s).${NC}"
    exit 1
else
    echo -e "${GREEN}Installation completed successfully!${NC}"
    exit 0
fi