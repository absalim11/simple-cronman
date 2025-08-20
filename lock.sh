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
    echo "File aturan sudoers '$SUDO_RULE_FILE' berhasil dihapus."
else
    echo "File aturan sudoers '$SUDO_RULE_FILE' tidak ditemukan. Tidak ada yang perlu dihapus."
fi

echo "Aplikasi web tidak akan lagi dapat mengelola cron job."
echo "Anda dapat memverifikasi dengan mencoba menambahkan cron job dari UI; seharusnya sekarang gagal dengan error izin."
