#!/bin/bash

FILE_TO_WATCH="/opt/callmigrate/cronwatch/cron.now"

#!/bin/bash

# Your service logic here
function log_watch {
  CURRENT_HASH=$(sha256sum $FILE_TO_WATCH | awk '{print $1}')
  if [ -z "$LAST_HASH" ]; then
    LAST_HASH="$CURRENT_HASH"
  fi

  if [ "$CURRENT_HASH" != "$LAST_HASH" ]; then
    /bin/php /opt/callmigrate/scripts/cron.php
    LAST_HASH="$CURRENT_HASH"
  fi

  sleep 1 # Check every 1 second
}

while true; do
  log_watch
done