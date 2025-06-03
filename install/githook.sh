#!/bin/bash
set -e

# Add post-merge hook to .git/hooks
HOOK_DIR="$SCRIPT_DIR/.git/hooks"
if [ ! -d "$HOOK_DIR" ]; then
    mkdir -p "$HOOK_DIR"
fi
cp "$SCRIPT_DIR/install/files/post-merge" "$HOOK_DIR/post-merge"
chmod +x "$HOOK_DIR/post-merge"
echo "Post-merge hook installed successfully at $HOOK_DIR/post-merge."