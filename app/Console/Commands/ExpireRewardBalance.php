<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ExpireRewardBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reward:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Thu hồi tiền thưởng sau 3 giờ nếu không phát sinh cược từ ví thưởng';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Bắt đầu thu hồi tiền thưởng...');

        $users = User::where('reward_balance', '>', 0)
            ->whereNotNull('last_reward_at')
            ->get();

        $expiredCount = 0;
        $totalExpiredAmount = 0;

        foreach ($users as $user) {
            if ($user->shouldExpireRewardBalance()) {
                $expiredAmount = $user->reward_balance ?? 0;
                $user->expireRewardBalance();
                $expiredCount++;
                $totalExpiredAmount += $expiredAmount;
                
                $this->info("User {$user->id}: Đã thu hồi {$expiredAmount} đá quý từ ví thưởng");
                \Log::info("Expired reward balance for user {$user->id}: {$expiredAmount} gems");
            }
        }

        $this->info("Hoàn thành: Đã thu hồi {$expiredCount} users, tổng số tiền: {$totalExpiredAmount} đá quý");

        return 0;
    }
}
