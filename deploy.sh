#!/bin/bash

# Using rsync to deploy the files for the web server
SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
WEB_ROOT="/var/www"
WEB_DIR="$SCRIPT_DIR/web"
PYTHON_DIR="$SCRIPT_DIR/python"
LOG_FILE="$SCRIPT_DIR/deploy.log"

# Create log file if it doesn't exist
touch "$LOG_FILE"

log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" | tee -a "$LOG_FILE"
}

# Check if the web directory exists
if [ ! -d "$WEB_ROOT/python" ]; then
    log_message "ERROR: Web root path does not exist: $WEB_ROOT. Please run the install script first."
    exit 1
fi

log_message "Deploying files to web server..."

# Sync files
rsync -av "$SCRIPT_DIR/web/" "$WEB_ROOT/html/" 2>&1 | tee -a "$LOG_FILE"
rsync -av "$SCRIPT_DIR/python/" "$WEB_ROOT/python/" 2>&1 | tee -a "$LOG_FILE"

# Check rsync result
if [ ${PIPESTATUS[0]} -eq 0 ]; then
    log_message "Deployment completed successfully"
else
    log_message "ERROR: Deployment failed"
    exit 1
fi

# Set appropriate permissions for web files
log_message "Setting appropriate permissions on web files"
find "$WEB_ROOT/*" -type f -exec chmod 644 {} \;
find "$WEB_ROOT/*" -type d -exec chmod 755 {} \;

log_message "Deployment process completed"
exit 0