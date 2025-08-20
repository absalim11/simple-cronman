#!/bin/bash

# This script adds a sudoers rule to allow the web server user to run crontab without a password.
# It creates a new file in /etc/sudoers.d/ which is the recommended way to add custom sudo rules.

# IMPORTANT:
# 1. Run this script with sudo: sudo ./unlock.sh
# 2. Verify your web server user. Common users are 'www-data' (Debian/Ubuntu) or 'apache' (CentOS/RHEL).
#    If unsure, check your web server configuration or run 'ps aux | grep apache' or 'ps aux | grep nginx'
#    to see which user your web server processes are running as.

WEB_SERVER_USER="www-data" # <--- CHANGE THIS IF YOUR WEB SERVER USER IS DIFFERENT

SUDO_RULE_FILE="/etc/sudoers.d/web_crontab_access"

# Check if the script is run as root
if [ "$(id -u)" -ne 0 ]; then
   echo "This script must be run as root or with sudo."
   echo "Usage: sudo ./unlock.sh"
   exit 1
fi

echo "Adding sudoers rule for user '$WEB_SERVER_USER' to allow passwordless crontab access..."

# Create the sudoers rule file
echo "$WEB_SERVER_USER ALL=(ALL) NOPASSWD: /usr/bin/crontab" | sudo tee "$SUDO_RULE_FILE" > /dev/null

# Set correct permissions for the new sudoers file
sudo chmod 0440 "$SUDO_RULE_FILE"
sudo chown root:root "$SUDO_RULE_FILE"

echo "Sudoers rule added successfully to $SUDO_RULE_FILE"
echo "Please verify the web server user '$WEB_SERVER_USER' is correct."
echo "You can test by switching to the web server user and trying 'crontab -l':"
echo "  sudo -u $WEB_SERVER_USER crontab -l"
echo "If it works without asking for a password, the setup is correct."
