#!/bin/bash

# Script để quản lý tmux session cho Micex Round Timer
# Usage: ./tmux-micex.sh [start|stop|restart|status|attach|logs]

SESSION_NAME="micex"
PROJECT_PATH="/var/www/micex"
COMMAND="php artisan round:process-loop"

case "$1" in
    start)
        echo "=== Starting tmux session: $SESSION_NAME ==="
        
        # Kiểm tra xem session đã tồn tại chưa
        if tmux has-session -t "$SESSION_NAME" 2>/dev/null; then
            echo "Session $SESSION_NAME already exists!"
            echo "Use './tmux-micex.sh attach' to attach or './tmux-micex.sh restart' to restart"
            exit 1
        fi
        
        # Tạo session mới và chạy command
        tmux new-session -d -s "$SESSION_NAME" "cd $PROJECT_PATH && $COMMAND"
        
        if [ $? -eq 0 ]; then
            echo "✓ Session $SESSION_NAME started successfully!"
            echo "Use './tmux-micex.sh attach' to view logs"
        else
            echo "✗ Failed to start session"
            exit 1
        fi
        ;;
    
    stop)
        echo "=== Stopping tmux session: $SESSION_NAME ==="
        
        if tmux has-session -t "$SESSION_NAME" 2>/dev/null; then
            tmux kill-session -t "$SESSION_NAME"
            echo "✓ Session $SESSION_NAME stopped"
        else
            echo "Session $SESSION_NAME not found"
        fi
        ;;
    
    restart)
        echo "=== Restarting tmux session: $SESSION_NAME ==="
        
        # Stop nếu đang chạy
        if tmux has-session -t "$SESSION_NAME" 2>/dev/null; then
            tmux kill-session -t "$SESSION_NAME"
            sleep 1
        fi
        
        # Kill any orphaned processes (không trong tmux)
        echo "Killing any orphaned round:process-loop processes..."
        pkill -f "artisan round:process-loop" 2>/dev/null
        sleep 2
        
        # Double check - kill force nếu vẫn còn
        PIDS=$(pgrep -f "artisan round:process-loop" 2>/dev/null)
        if [ ! -z "$PIDS" ]; then
            echo "Force killing remaining processes: $PIDS"
            kill -9 $PIDS 2>/dev/null
            sleep 1
        fi
        
        # Start lại
        tmux new-session -d -s "$SESSION_NAME" "cd $PROJECT_PATH && $COMMAND"
        
        if [ $? -eq 0 ]; then
            echo "✓ Session $SESSION_NAME restarted successfully!"
            sleep 1
            echo "Current processes:"
            ps aux | grep "round:process-loop" | grep -v grep
        else
            echo "✗ Failed to restart session"
            exit 1
        fi
        ;;
    
    status)
        echo "=== Tmux Sessions Status ==="
        tmux ls
        
        echo ""
        echo "=== Round Process Status ==="
        if pgrep -f "artisan round:process-loop" > /dev/null; then
            echo "✓ Round process is running"
            ps aux | grep "artisan round:process-loop" | grep -v grep
        else
            echo "✗ Round process is NOT running"
        fi
        ;;
    
    attach)
        if tmux has-session -t "$SESSION_NAME" 2>/dev/null; then
            echo "Attaching to session $SESSION_NAME..."
            echo "Press Ctrl+B then D to detach"
            tmux attach -t "$SESSION_NAME"
        else
            echo "Session $SESSION_NAME not found"
            echo "Use './tmux-micex.sh start' to create it"
            exit 1
        fi
        ;;
    
    logs)
        if tmux has-session -t "$SESSION_NAME" 2>/dev/null; then
            echo "Viewing logs from session $SESSION_NAME..."
            echo "Press Ctrl+B then D to detach"
            tmux attach -t "$SESSION_NAME"
        else
            echo "Session $SESSION_NAME not found"
            echo "Checking Laravel logs instead..."
            tail -f "$PROJECT_PATH/storage/logs/laravel.log"
        fi
        ;;
    
    *)
        echo "Usage: $0 {start|stop|restart|status|attach|logs}"
        echo ""
        echo "Commands:"
        echo "  start   - Create and start tmux session"
        echo "  stop    - Stop tmux session"
        echo "  restart - Restart tmux session"
        echo "  status  - Show session and process status"
        echo "  attach  - Attach to tmux session"
        echo "  logs    - View logs (attach to session or Laravel log)"
        exit 1
        ;;
esac

