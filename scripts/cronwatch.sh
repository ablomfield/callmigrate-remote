#!/bin/bash

DIRECTORY_TO_WATCH="/opt/callmigrate/cronwatch"

while true; do
  CURRENT_HASH=$(ls -lR "$DIRECTORY_TO_WATCH" | sha256sum | awk '{print $1}')
  if [ -z "$LAST_HASH" ]; then
    LAST_HASH="$CURRENT_HASH"
  fi

  if [ "$CURRENT_HASH" != "$LAST_HASH" ]; then
    echo "Directory changed at $(date)"
    # Do something here when a change is detected, e.g., run a command
    LAST_HASH="$CURRENT_HASH"
  fi

  sleep 1 # Check every 1 second
done