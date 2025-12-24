#!/bin/bash

# Script để kiểm tra log của round:process-loop
# Usage: ./check-round-process-log.sh [options]

PROJECT_PATH="/var/www/micex"
cd "$PROJECT_PATH" || exit 1

echo "=== Checking Round Process Loop Logs ==="
echo ""

# Check if process is running
echo "1. Process Status:"
PROCESS_COUNT=$(pgrep -f "artisan round:process-loop" | wc -l)
if [ "$PROCESS_COUNT" -gt 0 ]; then
    echo "   ✓ Round process loop is running ($PROCESS_COUNT process(es))"
    ps aux | grep "artisan round:process-loop" | grep -v grep
else
    echo "   ✗ Round process loop is NOT running"
fi

echo ""
echo "2. Tmux Session Status:"
if tmux has-session -t micex 2>/dev/null; then
    echo "   ✓ Tmux session 'micex' exists"
    tmux ls | grep micex
else
    echo "   ✗ Tmux session 'micex' not found"
fi

echo ""
echo "3. Laravel Log (Last 50 lines related to round processing):"
tail -100 storage/logs/laravel.log | grep -E "Round|round:process|Processing.*bets|finished" | tail -50

echo ""
echo "4. Recent Round Processing Activity (Last 20 rounds):"
tail -200 storage/logs/laravel.log | grep -E "Round.*finished|Processing.*bets|No pending bets" | tail -20

echo ""
echo "5. Errors in Round Processing (if any):"
tail -500 storage/logs/laravel.log | grep -iE "error.*round|failed.*process|exception.*bet" | tail -20

echo ""
echo "6. Systemd Service Status (if using systemd):"
if systemctl is-active --quiet micex-round-timer 2>/dev/null; then
    echo "   ✓ Systemd service is active"
    sudo systemctl status micex-round-timer --no-pager -l | head -20
else
    echo "   ℹ Systemd service not active (may be using tmux/screen)"
fi

echo ""
echo "=== Done ==="
echo ""
echo "To view live logs:"
echo "  # Laravel log"
echo "  tail -f storage/logs/laravel.log | grep -E 'Round|round:process'"
echo ""
echo "  # Systemd journal (if using systemd)"
echo "  sudo journalctl -u micex-round-timer -f"
echo ""
echo "  # Attach to tmux session"
echo "  tmux attach -t micex"


