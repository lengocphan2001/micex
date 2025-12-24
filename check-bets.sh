#!/bin/bash

# Script để kiểm tra bets trong round
# Usage: ./check-bets.sh [round_number]

PROJECT_PATH="/var/www/micex"
cd "$PROJECT_PATH" || exit 1

if [ -z "$1" ]; then
    # Nếu không có round_number, lấy round gần nhất
    ROUND_NUM=$(php artisan tinker --execute="\$r = \App\Models\Round::orderBy('round_number', 'desc')->first(); echo \$r ? \$r->round_number : 'N/A';")
    if [ "$ROUND_NUM" = "N/A" ]; then
        echo "No rounds found in database"
        exit 1
    fi
    echo "=== Checking bets for latest round: $ROUND_NUM ==="
else
    ROUND_NUM=$1
    echo "=== Checking bets for round: $ROUND_NUM ==="
fi

echo ""
echo "1. Round information:"
php artisan tinker --execute="
\$round = \App\Models\Round::where('round_number', $ROUND_NUM)->first();
if (\$round) {
    echo \"Round #\$round->round_number:\n\";
    echo \"  ID: \$round->id\n\";
    echo \"  Status: \$round->status\n\";
    echo \"  Final Result: \" . (\$round->final_result ?? 'N/A') . \"\n\";
    echo \"  Admin Result: \" . (\$round->admin_set_result ?? 'N/A') . \"\n\";
    echo \"  Started At: \" . (\$round->started_at ? \$round->started_at->format('Y-m-d H:i:s') : 'N/A') . \"\n\";
    echo \"  Ended At: \" . (\$round->ended_at ? \$round->ended_at->format('Y-m-d H:i:s') : 'N/A') . \"\n\";
} else {
    echo \"Round #$ROUND_NUM not found\n\";
    exit(1);
}
"

echo ""
echo "2. All bets in this round:"
php artisan tinker --execute="
\$round = \App\Models\Round::where('round_number', $ROUND_NUM)->first();
if (\$round) {
    \$bets = \$round->bets()->orderBy('created_at', 'desc')->get();
    if (\$bets->isEmpty()) {
        echo \"✗ No bets found in round #$ROUND_NUM\n\";
    } else {
        echo \"Total bets: \" . \$bets->count() . \"\n\";
        echo \"ID | User ID | Gem Type | Amount | Status | Payout Amount | Created At\n\";
        echo str_repeat('-', 100) . \"\n\";
        foreach (\$bets as \$bet) {
            echo sprintf(\"%3d | %7d | %-10s | %8.2f | %-7s | %13s | %-19s\n\",
                \$bet->id,
                \$bet->user_id,
                \$bet->gem_type,
                \$bet->amount,
                \$bet->status,
                \$bet->payout_amount ? number_format(\$bet->payout_amount, 2) : 'N/A',
                \$bet->created_at->format('Y-m-d H:i:s')
            );
        }
    }
}
"

echo ""
echo "3. Bet status summary:"
php artisan tinker --execute="
\$round = \App\Models\Round::where('round_number', $ROUND_NUM)->first();
if (\$round) {
    \$pending = \$round->bets()->where('status', 'pending')->count();
    \$won = \$round->bets()->where('status', 'won')->count();
    \$lost = \$round->bets()->where('status', 'lost')->count();
    \$total = \$round->bets()->count();
    echo \"Pending: \$pending\n\";
    echo \"Won: \$won\n\";
    echo \"Lost: \$lost\n\";
    echo \"Total: \$total\n\";
    
    if (\$pending > 0 && \$round->status === 'finished') {
        echo \"\n⚠️  WARNING: Round is finished but has \$pending pending bets!\n\";
        echo \"   This means bets were not processed. Run processBets() manually.\n\";
    }
}
"

echo ""
echo "4. Recent bets (last 10):"
php artisan tinker --execute="
\$bets = \App\Models\Bet::orderBy('created_at', 'desc')->limit(10)->get(['id', 'round_id', 'user_id', 'gem_type', 'amount', 'status', 'payout_amount', 'created_at']);
echo \"ID | Round# | User ID | Gem Type | Amount | Status | Payout | Created At\n\";
echo str_repeat('-', 100) . \"\n\";
foreach (\$bets as \$bet) {
    echo sprintf(\"%3d | %7s | %7d | %-10s | %8.2f | %-7s | %7s | %-19s\n\",
        \$bet->id,
        \$bet->round ? \$bet->round->round_number : 'N/A',
        \$bet->user_id,
        \$bet->gem_type,
        \$bet->amount,
        \$bet->status,
        \$bet->payout_amount ? number_format(\$bet->payout_amount, 2) : 'N/A',
        \$bet->created_at->format('Y-m-d H:i:s')
    );
}
"

echo ""
echo "=== Done ==="
echo ""
echo "If round is finished but has pending bets, manually process:"
echo "php artisan tinker --execute=\"\$round = \\\App\\\Models\\\Round::where('round_number', $ROUND_NUM)->first(); \$round->processBets();\""

