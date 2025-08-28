#!/bin/bash

# This script removes the sudoers rule created by unlock.sh,
# effectively revoking the web server user's passwordless crontab access.

# IMPORTANT:
# 1. Run this script with sudo: sudo ./lock.sh
# 2. This will prevent the web application from adding, deleting, or modifying cron jobs.

SUDO_RULE_FILE="/etc/sudoers.d/web_crontab_access"
WEB_SERVER_USER="www-data" # Ensure this matches the user in unlock.sh

# Check if the script is run as root
if [ "$(id -u)" -ne 0 ]; then
   echo "This script must be run as root or with sudo."
   echo "Usage: sudo ./lock.sh"
   exit 1
fi

echo "Removing sudoers rule for user '$WEB_SERVER_USER'..."

if [ -f "$SUDO_RULE_FILE" ]; then
    sudo rm "$SUDO_RULE_FILE"
    echo "The sudoers rule file '$SUDO_RULE_FILE' has been successfully removed."
else
    echo "The sudoers rule file '$SUDO_RULE_FILE' was not found. Nothing to remove."
fi

echo "The web application will no longer be able to manage cron jobs."
echo "You can verify this by trying to add a cron job from the UI; it should now fail with a permission error."