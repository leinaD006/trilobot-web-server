#!/bin/bash
set -e

# Creates a symlinked folder for students to work in
if [ ! -d "$HOME/trilobot/python" ]; then
    mkdir -p "$HOME/trilobot/python"
fi

if [ ! -d "$WEB_ROOT/python/scripts" ]; then
        mkdir -p "$WEB_ROOT/python/scripts"
fi

if [ ! -L "$WEB_ROOT/python/scripts/user" ]; then
    ln -s "$HOME/trilobot/python" "$WEB_ROOT/python/scripts/user"
fi

echo "Created symlink for student scripts in $HOME/trilobot/python"
