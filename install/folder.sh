#!/bin/bash
set -e
echo "$HOME"
# Creates a symlinked folder for students to work in
if [ ! -d "$HOME/trilobot/python"]; then
    echo "making folder
    mkdir -p "$HOME/trilobot/python"
    ln -s "$HOME/trilobot/python" "$WEB_ROOT/python/scripts/user"
fi

echo "Created symlink for student scripts in $HOME/trilobot/python"
