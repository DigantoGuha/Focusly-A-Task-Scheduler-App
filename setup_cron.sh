#!/bin/bash

# Get the full path to the cron.php file
CRON_FILE_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/cron.php"
LOG_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/cron.log"

# Prepare CRON line
CRON_JOB="0 * * * * php $CRON_FILE_PATH >> $LOG_PATH 2>&1"

# Check if the cron job already exists
(crontab -l 2>/dev/null | grep -v -F "$CRON_FILE_PATH"; echo "$CRON_JOB") | crontab -

echo "✅ CRON job registered to run every hour for: $CRON_FILE_PATH"
