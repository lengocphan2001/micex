#!/bin/bash

# Simple script to stop and restart round:process-loop on VPS

echo "=== Finding and stopping round:process-loop ==="

# Find the process
PID=$(pgrep -f "artisan round:process-loop" | head -1)

if [ ! -z "$PID" ]; then
    echo "Found process with PID: $PID"
    echo "Stopping process..."
    kill $PID
    sleep 2
    
    # Check if still running, force kill if needed
    if ps -p $PID > /dev/null 2>&1; then
        echo "Process still running, force killing..."
        kill -9 $PID
        sleep 1
    fi
    echo "Process stopped."
else
    echo "No running process found."
fi

# Check if running in screen
SCREEN_SESSION=$(screen -ls | grep round-process-loop | awk '{print $1}')
if [ ! -z "$SCREEN_SESSION" ]; then
    echo "Found screen session: $SCREEN_SESSION"
    screen -X -S $SCREEN_SESSION quit
    echo "Screen session closed."
fi

echo ""
echo "=== Starting round:process-loop ==="

# Get the project path (assuming script is in project root)
PROJECT_PATH=$(pwd)

# Start in screen session (recommended for background)
screen -dmS round-process-loop bash -c "cd $PROJECT_PATH && php artisan round:process-loop"

echo "Started in screen session: round-process-loop"
echo ""
echo "To attach to the session: screen -r round-process-loop"
echo "To detach: Press Ctrl+A then D"
echo "To view logs: screen -r round-process-loop"

