<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserCommission;
use App\Models\Notification;
use Illuminate\Console\Command;

class NotifyCommissionAvailable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commission:notify-available';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify users about available commission every hour';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking available commission for all users...');
        
        $notifiedCount = 0;
        
        // Get all users who have available commission
        $usersWithCommission = UserCommission::where('status', 'available')
            ->selectRaw('user_id, SUM(commission_amount) as total_commission')
            ->groupBy('user_id')
            ->havingRaw('SUM(commission_amount) > 0')
            ->get();
        
        $this->info("Found {$usersWithCommission->count()} users with available commission.");
        
        foreach ($usersWithCommission as $commissionData) {
            $user = User::find($commissionData->user_id);
            
            if (!$user) {
                $this->warn("User ID {$commissionData->user_id} not found, skipping...");
                continue;
            }
            
            $totalCommission = floatval($commissionData->total_commission);
            
            if ($totalCommission <= 0) {
                continue;
            }
            
            // Always create new notification every hour (don't check for recent notification)
            // This ensures users see the notification in dropdown every hour
            Notification::create([
                'user_id' => $user->id,
                'type' => 'commission_available',
                'title' => 'Hoa hồng có sẵn',
                'message' => "Bạn có " . number_format($totalCommission, 2, ',', '.') . "$ hoa hồng có thể rút. Vui lòng vào màn Hệ thống để rút hoa hồng.",
                'data' => [
                    'total_commission' => $totalCommission,
                ],
            ]);
            $notifiedCount++;
            $this->info("Created notification for user {$user->id} (commission: {$totalCommission})");
        }
        
        $this->info("Sent commission notifications to {$notifiedCount} users.");
        
        return 0;
    }
}

