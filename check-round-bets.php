<?php

// Script PHP đơn giản để kiểm tra bets trong round
// Usage: php check-round-bets.php [round_number]

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$roundNumber = $argv[1] ?? null;

if (!$roundNumber) {
    // Lấy round gần nhất
    $round = \App\Models\Round::orderBy('round_number', 'desc')->first();
    if (!$round) {
        echo "No rounds found in database\n";
        exit(1);
    }
    $roundNumber = $round->round_number;
    echo "=== Checking bets for latest round: $roundNumber ===\n\n";
} else {
    echo "=== Checking bets for round: $roundNumber ===\n\n";
}

$round = \App\Models\Round::where('round_number', $roundNumber)->first();

if (!$round) {
    echo "✗ Round #$roundNumber not found\n";
    exit(1);
}

echo "1. Round Information:\n";
echo "   Round #{$round->round_number}\n";
echo "   ID: {$round->id}\n";
echo "   Status: {$round->status}\n";
echo "   Final Result: " . ($round->final_result ?? 'N/A') . "\n";
echo "   Admin Result: " . ($round->admin_set_result ?? 'N/A') . "\n";
echo "   Started At: " . ($round->started_at ? $round->started_at->format('Y-m-d H:i:s') : 'N/A') . "\n";
echo "   Ended At: " . ($round->ended_at ? $round->ended_at->format('Y-m-d H:i:s') : 'N/A') . "\n";

echo "\n2. All Bets in This Round:\n";
$bets = $round->bets()->orderBy('created_at', 'desc')->get();

if ($bets->isEmpty()) {
    echo "   ✗ No bets found in round #$roundNumber\n";
} else {
    echo "   Total bets: {$bets->count()}\n";
    echo "   ID | User ID | Gem Type | Amount | Status | Payout Amount | Created At\n";
    echo "   " . str_repeat('-', 100) . "\n";
    foreach ($bets as $bet) {
        printf("   %3d | %7d | %-10s | %8.2f | %-7s | %13s | %-19s\n",
            $bet->id,
            $bet->user_id,
            $bet->gem_type,
            $bet->amount,
            $bet->status,
            $bet->payout_amount ? number_format($bet->payout_amount, 2) : 'N/A',
            $bet->created_at->format('Y-m-d H:i:s')
        );
    }
}

echo "\n3. Bet Status Summary:\n";
$pending = $round->bets()->where('status', 'pending')->count();
$won = $round->bets()->where('status', 'won')->count();
$lost = $round->bets()->where('status', 'lost')->count();
$total = $round->bets()->count();

echo "   Pending: $pending\n";
echo "   Won: $won\n";
echo "   Lost: $lost\n";
echo "   Total: $total\n";

if ($pending > 0 && $round->status === 'finished') {
    echo "\n   ⚠️  WARNING: Round is finished but has $pending pending bets!\n";
    echo "      This means bets were not processed.\n";
}

echo "\n4. Recent Bets (Last 10):\n";
$recentBets = \App\Models\Bet::orderBy('created_at', 'desc')->limit(10)->get();
echo "   ID | Round# | User ID | Gem Type | Amount | Status | Payout | Created At\n";
echo "   " . str_repeat('-', 100) . "\n";
foreach ($recentBets as $bet) {
    printf("   %3d | %7s | %7d | %-10s | %8.2f | %-7s | %7s | %-19s\n",
        $bet->id,
        $bet->round ? $bet->round->round_number : 'N/A',
        $bet->user_id,
        $bet->gem_type,
        $bet->amount,
        $bet->status,
        $bet->payout_amount ? number_format($bet->payout_amount, 2) : 'N/A',
        $bet->created_at->format('Y-m-d H:i:s')
    );
}

echo "\n=== Done ===\n";

if ($pending > 0 && $round->status === 'finished') {
    echo "\nTo manually process pending bets:\n";
    echo "php artisan tinker --execute=\"\$round = \\\\App\\\\Models\\\\Round::where('round_number', $roundNumber)->first(); if (\$round && \$round->final_result) { \$round->processBets(); echo 'Processed bets for round #' . \$round->round_number; }\"\n";
}


