#!/bin/bash

echo "========================================"
echo "   TRADING GAME - QUICK START"
echo "========================================"
echo ""

echo "[1/3] Starting Laravel Server..."
php artisan serve > /dev/null 2>&1 &
LARAVEL_PID=$!

sleep 2

echo "[2/3] Starting Laravel Scheduler..."
php artisan schedule:work > /dev/null 2>&1 &
SCHEDULER_PID=$!

sleep 2

echo "[3/3] Trading Game is running!"
echo ""
echo "Laravel Server: http://localhost:8000"
echo "Trading Game: http://localhost:8000/games/trading"
echo ""
echo "Press Ctrl+C to stop..."
echo ""

# Wait for user interrupt
trap "kill $LARAVEL_PID $SCHEDULER_PID 2>/dev/null; exit" INT TERM

wait
