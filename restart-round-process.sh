#!/bin/bash

# Script to stop and restart round:process-loop on VPS

echo "=== Stopping round:process-loop ==="

# Method 1: Check if running via Supervisor
if supervisorctl status round-process-loop &>/dev/null; then
    echo "Found in Supervisor, stopping..."
    supervisorctl stop round-process-loop
    sleep 2
    echo "Starting..."
    supervisorctl start round-process-loop
    supervisorctl status round-process-loop
    exit 0
fi

# Method 2: Check if running via Systemd
if systemctl is-active --quiet round-process-loop.service 2>/dev/null; then
    echo "Found in Systemd, stopping..."
    sudo systemctl stop round-process-loop.service
    sleep 2
    echo "Starting..."
    sudo systemctl start round-process-loop.service
    sudo systemctl status round-process-loop.service
    exit 0
fi

# Method 3: Check if running via screen
SCREEN_PID=$(pgrep -f "round:process-loop" | head -1)
if [ ! -z "$SCREEN_PID" ]; then
    echo "Found process running (PID: $SCREEN_PID), killing..."
    kill $SCREEN_PID
    sleep 2
    echo "Starting in new screen session..."
    screen -dmS round-process-loop php artisan round:process-loop
    echo "Started in screen. Use 'screen -r round-process-loop' to attach."
    exit 0
fi

# Method 4: Check if running via nohup or directly
ROUND_PID=$(pgrep -f "artisan round:process-loop" | head -1)
if [ ! -z "$ROUND_PID" ]; then
    echo "Found process running (PID: $ROUND_PID), killing..."
    kill $ROUND_PID
    sleep 2
fi

# Start new process
echo "=== Starting round:process-loop ==="
echo "Choose method to start:"
echo "1. Screen (recommended for testing)"
echo "2. Nohup (background)"
echo "3. Supervisor (if configured)"
echo "4. Systemd (if configured)"
read -p "Enter choice (1-4): " choice

case $choice in
    1)
        screen -dmS round-process-loop php artisan round:process-loop
        echo "Started in screen. Use 'screen -r round-process-loop' to attach."
        ;;
    2)
        nohup php artisan round:process-loop > storage/logs/round-process.log 2>&1 &
        echo "Started with nohup. PID: $!"
        echo "Logs: storage/logs/round-process.log"
        ;;
    3)
        supervisorctl start round-process-loop
        supervisorctl status round-process-loop
        ;;
    4)
        sudo systemctl start round-process-loop.service
        sudo systemctl status round-process-loop.service
        ;;
    *)
        echo "Invalid choice. Starting with screen..."
        screen -dmS round-process-loop php artisan round:process-loop
        ;;
esac

echo "Done!"

