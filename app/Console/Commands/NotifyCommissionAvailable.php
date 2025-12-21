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
            
            // Check if user already has a recent commission notification (within last hour)
            $recentNotification = Notification::where('user_id', $user->id)
                ->where('type', 'commission_available')
                ->where('created_at', '>=', now()->subHour())
                ->first();
            
            if ($recentNotification) {
                // Update existing notification (luôn update để user thấy thông báo mới mỗi giờ)
                $existingAmount = is_array($recentNotification->data) && isset($recentNotification->data['total_commission']) 
                    ? floatval($recentNotification->data['total_commission']) 
                    : 0;
                    
                // Update notification để user thấy thông báo mới (mark as unread)
                $recentNotification->update([
                    'title' => 'Hoa hồng có sẵn',
                    'message' => "Bạn có " . number_format($totalCommission, 2, ',', '.') . "$ hoa hồng có thể rút. Vui lòng vào màn Hệ thống để rút hoa hồng.",
                    'data' => [
                        'total_commission' => $totalCommission,
                    ],
                    'is_read' => false, // Mark as unread để user thấy thông báo mới
                    'created_at' => now(), // Update created_at để hiển thị như thông báo mới
                ]);
                $notifiedCount++;
                $this->info("Updated notification for user {$user->id} (commission: {$totalCommission})");
            } else {
                // Create new notification
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
        }
        
        $this->info("Sent commission notifications to {$notifiedCount} users.");
        
        return 0;
    }
}

