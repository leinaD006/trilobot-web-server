#!/bin/bash
set -e

echo "Installing example files for Trilobot..."

# Check if examples directory exists, if not, create it
if [ ! -d "$EXAMPLE_DIR" ]; then
    mkdir -p "$EXAMPLE_DIR"
fi

#!/bin/bash

# List of repositories to install (name:repo-suffix format)
REPOS=(
    "trilobot"
    "bme680" 
    "msa301"
    "icm20948"
)

# Function to install a single repository
install_repo() {
    local name="$1"
    local repo_suffix="$2"
    local repo_url="https://github.com/pimoroni/$repo_suffix"
    local clone_dir="$INSTALL_DIR/$repo_suffix"
    
    echo "Installing $name examples..."
    
    # Create example directory if it doesn't exist
    if [ ! -d "$EXAMPLE_DIR/$name" ]; then
        mkdir -p "$EXAMPLE_DIR/$name"
    fi
    
    # Clone repository
    if git clone "$repo_url" "$clone_dir"; then
        # Copy examples (use trap to ensure cleanup on any exit)
        trap "rm -rf '$clone_dir'" EXIT ERR
        
        if cp -r "$clone_dir/examples"/* "$EXAMPLE_DIR/$name/"; then
            echo "Successfully installed $name examples"
        else
            echo "Warning: Failed to copy $name examples"
        fi
        
        # Remove trap and clean up manually for success case
        trap - EXIT ERR
        rm -rf "$clone_dir"
    else
        echo "Error: Failed to clone $name repository"
        # Cleanup in case partial clone occurred
        rm -rf "$clone_dir"
    fi
}

# Main installation loop
for repo in "${REPOS[@]}"; do
    # Split name:repo-suffix
    name="$repo"
    repo_suffix="$repo-python"
    
    install_repo "$name" "$repo_suffix"
done