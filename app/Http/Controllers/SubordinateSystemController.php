<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserCommission;
use App\Models\CommissionRate;
use App\Models\Bet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubordinateSystemController extends Controller
{
    /**
     * Get downline statistics for a user
     */
    public function index()
    {
        $user = Auth::guard('web')->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Calculate F1, F2, F3... (downline levels)
        $downlineStats = $this->calculateDownlineStats($user);
        
        // Get transaction volumes (tính từ khối lượng bet thực tế)
        $transactionVolumes = $this->getTransactionVolumes($user, $downlineStats);

        // Get commission statistics
        $availableCommission = UserCommission::getAvailableCommission($user->id);
        $totalCommission = UserCommission::where('user_id', $user->id)->sum('commission_amount');
        $withdrawnCommission = UserCommission::where('user_id', $user->id)
            ->where('status', 'withdrawn')
            ->sum('commission_amount');
        
        // Get commission by level
        $commissionByLevel = UserCommission::where('user_id', $user->id)
            ->select('level', DB::raw('SUM(commission_amount) as total'))
            ->groupBy('level')
            ->pluck('total', 'level')
            ->toArray();
        
        // Get recent commissions
        $recentCommissions = UserCommission::where('user_id', $user->id)
            ->with(['fromUser', 'bet'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get referral list with level, transaction volume, and commission
        $referralList = $this->getReferralList($user, $downlineStats);
        
        // Calculate total traders (sum of all levels)
        $totalTraders = array_sum(array_column($downlineStats, 'count'));
        
        // Get referrer info
        $referrer = $user->referrer;

        return view('subordinate-system', compact(
            'user', 
            'downlineStats', 
            'transactionVolumes',
            'availableCommission',
            'totalCommission',
            'withdrawnCommission',
            'commissionByLevel',
            'recentCommissions',
            'referralList',
            'totalTraders',
            'referrer'
        ));
    }

    /**
     * Withdraw commission
     */
    public function withdrawCommission(Request $request)
    {
        $user = Auth::guard('web')->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if user can withdraw (must be at least 1 hour since last withdrawal)
        $lastWithdraw = UserCommission::where('user_id', $user->id)
            ->where('status', 'withdrawn')
            ->whereNotNull('withdrawn_at')
            ->orderBy('withdrawn_at', 'desc')
            ->first();
        
        if ($lastWithdraw && $lastWithdraw->withdrawn_at) {
            $timeSinceLastWithdraw = now()->diffInSeconds($lastWithdraw->withdrawn_at);
            $oneHourInSeconds = 3600;
            
            if ($timeSinceLastWithdraw < $oneHourInSeconds) {
                $remainingSeconds = $oneHourInSeconds - $timeSinceLastWithdraw;
                $remainingMinutes = ceil($remainingSeconds / 60);
                return response()->json([
                    'error' => "Bạn chỉ có thể rút hoa hồng mỗi tiếng. Vui lòng thử lại sau {$remainingMinutes} phút.",
                ], 400);
            }
        }

        $availableCommission = UserCommission::getAvailableCommission($user->id);
        
        if ($availableCommission <= 0) {
            return response()->json([
                'error' => 'Bạn không có hoa hồng để rút.',
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Get all available commissions
            $commissions = UserCommission::where('user_id', $user->id)
                ->where('status', 'available')
                ->get();
            
            // Add commission to user balance
            $user->balance += $availableCommission;
            $user->save();
            
            // Mark all commissions as withdrawn
            foreach ($commissions as $commission) {
                $commission->withdraw();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Rút hoa hồng thành công!',
                'amount' => $availableCommission,
                'new_balance' => $user->balance,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Có lỗi xảy ra khi rút hoa hồng: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check withdraw status (for frontend countdown)
     */
    public function checkWithdrawStatus()
    {
        $user = Auth::guard('web')->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $lastWithdraw = UserCommission::where('user_id', $user->id)
            ->where('status', 'withdrawn')
            ->whereNotNull('withdrawn_at')
            ->orderBy('withdrawn_at', 'desc')
            ->first();
        
        return response()->json([
            'last_withdraw_time' => $lastWithdraw && $lastWithdraw->withdrawn_at 
                ? $lastWithdraw->withdrawn_at->toIso8601String() 
                : null,
        ]);
    }

    /**
     * Get test data for visualization
     */
    private function getTestData()
    {
        return [
            'F1' => [
                'count' => 168,
                'users' => collect()
            ],
            'F2' => [
                'count' => 453,
                'users' => collect()
            ],
            'F3' => [
                'count' => 453,
                'users' => collect()
            ],
            'F4' => [
                'count' => 453,
                'users' => collect()
            ],
            'F5' => [
                'count' => 453,
                'users' => collect()
            ],
            'F6' => [
                'count' => 453,
                'users' => collect()
            ],
        ];
    }

    /**
     * Calculate downline statistics (F1, F2, F3...)
     * F1 = Direct referrals
     * F2 = Referrals of F1
     * F3 = Referrals of F2
     * etc.
     */
    private function calculateDownlineStats(User $user)
    {
        $stats = [];
        $currentLevelUserIds = [$user->id];
        
        // Calculate each level (F1 to F6)
        for ($level = 1; $level <= 6; $level++) {
            // Get users at current level (referred by users from previous level)
            $currentLevelUsers = User::whereIn('referred_by', $currentLevelUserIds)->get();
            
            $stats['F' . $level] = [
                'count' => $currentLevelUsers->count(),
                'users' => $currentLevelUsers
            ];
            
            // Prepare for next level
            $currentLevelUserIds = $currentLevelUsers->pluck('id')->toArray();
            
            // If no users at this level, break early
            if (empty($currentLevelUserIds)) {
                // Fill remaining levels with 0
                for ($remaining = $level + 1; $remaining <= 6; $remaining++) {
                    $stats['F' . $remaining] = [
                        'count' => 0,
                        'users' => collect()
                    ];
                }
                break;
            }
        }

        return $stats;
    }

    /**
     * Get transaction volumes for each level
     * Tính khối lượng bet thực tế từ bảng bets của các user trong network
     * Chỉ tính cho các hệ có downline (count > 0)
     */
    private function getTransactionVolumes(User $user, array $downlineStats)
    {
        $volumes = [];
        
        // Tính khối lượng bet cho từng hệ (chỉ các hệ có downline)
        foreach ($downlineStats as $level => $stats) {
            // Chỉ tính cho các hệ có downline (count > 0)
            if ($stats['count'] > 0 && !empty($stats['users'])) {
                $userIds = $stats['users']->pluck('id')->toArray();
                
                // Tính tổng khối lượng bet của tất cả user trong hệ này
                $volume = Bet::whereIn('user_id', $userIds)
                    ->sum('amount');
                
                // Chỉ thêm vào nếu có volume > 0
                if ($volume > 0) {
                    $volumes[$level] = $volume;
                }
            }
        }

        // Total system volume (chỉ tính tổng các hệ có volume)
        $volumes['total'] = array_sum(array_filter($volumes, function($key) {
            return $key !== 'total';
        }, ARRAY_FILTER_USE_KEY));

        return $volumes;
    }

    /**
     * Get referral list with level, transaction volume, and commission
     */
    private function getReferralList(User $user, array $downlineStats)
    {
        $list = [];
        
        // Get users from each level with their stats
        foreach ($downlineStats as $level => $stats) {
            if ($stats['count'] > 0 && !empty($stats['users'])) {
                foreach ($stats['users'] as $downlineUser) {
                    // Get transaction volume for this user
                    $userVolume = Bet::where('user_id', $downlineUser->id)->sum('amount');
                    
                    // Get commission received from this user
                    $userCommission = UserCommission::where('user_id', $user->id)
                        ->where('from_user_id', $downlineUser->id)
                        ->sum('commission_amount');
                    
                    $list[] = [
                        'user' => $downlineUser,
                        'level' => $level,
                        'transaction_volume' => $userVolume,
                        'commission' => $userCommission,
                    ];
                }
            }
        }
        
        // Sort by level (F1, F2, F3...) then by transaction volume desc
        usort($list, function($a, $b) {
            $levelOrder = ['F1' => 1, 'F2' => 2, 'F3' => 3, 'F4' => 4, 'F5' => 5, 'F6' => 6];
            $levelCompare = ($levelOrder[$a['level']] ?? 999) <=> ($levelOrder[$b['level']] ?? 999);
            if ($levelCompare !== 0) {
                return $levelCompare;
            }
            return $b['transaction_volume'] <=> $a['transaction_volume'];
        });
        
        return $list;
    }
}

