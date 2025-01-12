#!/bin/bash

while true; do
    # Watch for modifications to the file
    inotifywait -e modify "/opt/callmigrate/cronwatch/cron.now"

    # Run cron.php script
    echo "Manual cron triggered"
    /usr/bin/php /opt/callmigrate/scripts/cron.php
done