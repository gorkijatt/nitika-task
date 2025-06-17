#!/bin/bash

# Get the absolute path to the current directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CRON_PHP_FILE="$SCRIPT_DIR/cron.php"

# Create cron job entry to run every hour
CRON_JOB="0 * * * * /usr/bin/php $CRON_PHP_FILE"

# Add the cron job to current user's crontab
(crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -

echo "CRON job added successfully!"
echo "The task reminder will run every hour at minute 0"
echo "CRON job: $CRON_JOB"
