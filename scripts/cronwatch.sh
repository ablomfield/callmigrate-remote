#!/bin/bash

while true; do
    inotifywait -e modify "/opt/callmigrate/cronwatch/cron.now" |
    /usr/bin/php /opt/callmigrate/scripts/cron.php
done