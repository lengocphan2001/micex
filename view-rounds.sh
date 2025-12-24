#!/bin/bash

# Script để xem danh sách rounds trong database
# Usage: ./view-rounds.sh [options]

PROJECT_PATH="/var/www/micex"
cd "$PROJECT_PATH" || exit 1

echo "=== Viewing Rounds in Database ==="
echo ""

# Default: show last 20 rounds
LIMIT=${1:-20}

echo "1. Last $LIMIT rounds:"
php artisan tinker --execute="
\$rounds = \App\Models\Round::orderBy('round_number', 'desc')->limit($LIMIT)->get(['id', 'round_number', 'seed', 'status', 'current_second', 'final_result', 'admin_set_result', 'started_at', 'ended_at', 'created_at']);
echo \"ID | Round# | Seed | Status | Current Sec | Final Result | Admin Result | Started At | Ended At\n\";
echo str_repeat('-', 120) . \"\n\";
foreach (\$rounds as \$r) {
    echo sprintf(\"%3d | %7d | %-15s | %-8s | %-12s | %-11s | %-12s | %-19s | %-19s\n\",
        \$r->id,
        \$r->round_number,
        \$r->seed ?? 'N/A',
        \$r->status ?? 'N/A',
        \$r->current_second ?? 'N/A',
        \$r->final_result ?? 'N/A',
        \$r->admin_set_result ?? 'N/A',
        \$r->started_at ? \$r->started_at->format('Y-m-d H:i:s') : 'N/A',
        \$r->ended_at ? \$r->ended_at->format('Y-m-d H:i:s') : 'N/A'
    );
}
"

echo ""
echo "2. Round statistics:"
php artisan tinker --execute="
\$total = \App\Models\Round::count();
\$running = \App\Models\Round::where('status', 'running')->count();
\$finished = \App\Models\Round::where('status', 'finished')->count();
\$pending = \App\Models\Round::where('status', 'pending')->count();
\$latest = \App\Models\Round::orderBy('round_number', 'desc')->first();
echo \"Total rounds: \$total\n\";
echo \"Running: \$running\n\";
echo \"Finished: \$finished\n\";
echo \"Pending: \$pending\n\";
if (\$latest) {
    echo \"Latest round: #\$latest->round_number (Status: \$latest->status)\n\";
}
"

echo ""
echo "3. Recent finished rounds with results:"
php artisan tinker --execute="
\$rounds = \App\Models\Round::where('status', 'finished')
    ->whereNotNull('final_result')
    ->orderBy('round_number', 'desc')
    ->limit(10)
    ->get(['round_number', 'final_result', 'admin_set_result', 'ended_at']);
echo \"Round# | Final Result | Admin Result | Ended At\n\";
echo str_repeat('-', 60) . \"\n\";
foreach (\$rounds as \$r) {
    echo sprintf(\"%7d | %-12s | %-12s | %-19s\n\",
        \$r->round_number,
        \$r->final_result ?? 'N/A',
        \$r->admin_set_result ?? 'N/A',
        \$r->ended_at ? \$r->ended_at->format('Y-m-d H:i:s') : 'N/A'
    );
}
"

echo ""
echo "4. Check specific round (if provided as second argument):"
if [ ! -z "$2" ]; then
    ROUND_NUM=$2
    php artisan tinker --execute="
    \$round = \App\Models\Round::where('round_number', $ROUND_NUM)->first();
    if (\$round) {
        echo \"Round #\$round->round_number:\n\";
        echo \"  ID: \$round->id\n\";
        echo \"  Seed: \$round->seed\n\";
        echo \"  Status: \$round->status\n\";
        echo \"  Current Second: \$round->current_second\n\";
        echo \"  Final Result: \" . (\$round->final_result ?? 'N/A') . \"\n\";
        echo \"  Admin Set Result: \" . (\$round->admin_set_result ?? 'N/A') . \"\n\";
        echo \"  Started At: \" . (\$round->started_at ? \$round->started_at->format('Y-m-d H:i:s') : 'N/A') . \"\n\";
        echo \"  Ended At: \" . (\$round->ended_at ? \$round->ended_at->format('Y-m-d H:i:s') : 'N/A') . \"\n\";
        echo \"  Created At: \" . \$round->created_at->format('Y-m-d H:i:s') . \"\n\";
    } else {
        echo \"Round #$ROUND_NUM not found\n\";
    }
    "
fi

echo ""
echo "=== Done ==="
echo ""
echo "Usage examples:"
echo "  ./view-rounds.sh          # Show last 20 rounds"
echo "  ./view-rounds.sh 50      # Show last 50 rounds"
echo "  ./view-rounds.sh 20 515287  # Show last 20 rounds + details of round 515287"

