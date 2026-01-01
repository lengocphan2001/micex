<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DepositRequest;
use App\Models\SystemSetting;
use App\Models\Promotion;
use App\Models\Giftcode;
use App\Models\Notification;
use App\Models\Round;
use App\Models\CommissionRate;
use App\Models\BettingContribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function dashboard()
    {
        // Calculate statistics from real data
        try {
            // Total deposit from approved deposit requests
            $totalDeposit = DepositRequest::where('status', 'approved')
                ->sum('gem_amount') ?? 0;
            
            // Total withdraw from approved withdraw requests
            $totalWithdraw = \App\Models\WithdrawRequest::where('status', 'approved')
                ->sum('gem_amount') ?? 0;
            
            // Get user balance sum
            $totalOnExchange = User::sum('balance') ?? 0;
            
            // Calculate system profit: total deposit - total withdraw - total balance on exchange
            $systemProfit = $totalDeposit - $totalWithdraw - $totalOnExchange;
            
            // Commission paid from user_commissions table
            $commissionPaid = \App\Models\UserCommission::where('status', 'withdrawn')
                ->sum('commission_amount') ?? 0;
            
            // Promotion bonus: Calculate from deposit requests that have promotion bonus
            // Check each approved deposit to see if it was approved during an active promotion
            $promotionBonus = 0;
            $approvedDeposits = DepositRequest::where('status', 'approved')
                ->whereNotNull('approved_at')
                ->get();
            
            foreach ($approvedDeposits as $deposit) {
                // Check if there was an active promotion at the time of approval
                $promotion = Promotion::getActivePromotion($deposit->approved_at);
                if ($promotion) {
                    $baseAmount = $deposit->gem_amount ?? ($deposit->amount / SystemSetting::getVndToGemRate());
                    $promotionBonus += $baseAmount * ($promotion->deposit_percentage / 100);
                }
            }
            
            // First deposit bonus: Calculate from first approved deposit per user
            $firstDepositBonus = 0;
            $usersWithFirstDeposit = DB::table('deposit_requests')
                ->select('user_id', DB::raw('MIN(approved_at) as first_approved_at'))
                ->where('status', 'approved')
                ->groupBy('user_id')
                ->get();
            
            foreach ($usersWithFirstDeposit as $userDeposit) {
                $firstDeposit = DepositRequest::where('user_id', $userDeposit->user_id)
                    ->where('status', 'approved')
                    ->where('approved_at', $userDeposit->first_approved_at)
                    ->first();
                
                if ($firstDeposit) {
                    // Check if there's a first deposit bonus setting
                    $firstDepositBonusRate = SystemSetting::getValue('first_deposit_bonus_rate', '0');
                    if ($firstDepositBonusRate > 0) {
                        $baseAmount = $firstDeposit->gem_amount ?? ($firstDeposit->amount / SystemSetting::getVndToGemRate());
                        $firstDepositBonus += $baseAmount * ($firstDepositBonusRate / 100);
                    }
                }
            }
            
            // Manual bonus: This would need to be tracked separately, for now set to 0
            $manualBonus = 0;
        } catch (\Exception $e) {
            // If tables don't exist, set default values
            $systemProfit = 0;
            $totalDeposit = 0;
            $totalWithdraw = 0;
            $totalOnExchange = 0;
            $commissionPaid = 0;
            $promotionBonus = 0;
            $firstDepositBonus = 0;
            $manualBonus = 0;
        }

        // Get active promotion
        $activePromotion = Promotion::getActivePromotion();

        return view('admin.dashboard', compact(
            'systemProfit',
            'totalDeposit',
            'totalWithdraw',
            'totalOnExchange',
            'commissionPaid',
            'promotionBonus',
            'firstDepositBonus',
            'manualBonus',
            'activePromotion'
        ));
    }

    /**
     * Intervene results page
     */
    public function interveneResults()
    {
        // Get current payout rates from database (3 đá thường)
        $payoutRates = [
            'kcxanh' => (float) SystemSetting::getValue('gem_payout_rate_kcxanh', SystemSetting::getValue('gem_payout_rate_thachanh', '1.95')),
            'daquy' => (float) SystemSetting::getValue('gem_payout_rate_daquy', '5.95'),
            'kcdo' => (float) SystemSetting::getValue('gem_payout_rate_kcdo', SystemSetting::getValue('gem_payout_rate_kimcuong', '1.95')),
        ];
        
        // Get payout rates for 3 đá nổ hũ (chỉ admin set, user không thể cược)
        $jackpotRates = [
            'thachanhtim' => (float) SystemSetting::getValue('gem_payout_rate_thachanhtim', '10.00'),
            'ngusac' => (float) SystemSetting::getValue('gem_payout_rate_ngusac', '20.00'),
            'cuoc' => (float) SystemSetting::getValue('gem_payout_rate_cuoc', '50.00'),
        ];
        
        // Get current round (không tạo mới, chỉ lấy từ database)
        $currentRound = Round::getCurrentRound();
        
        // Nếu round chưa tồn tại, tạo round rỗng để view không bị lỗi
        if (!$currentRound) {
            $currentRound = null;
        }
        
        return view('admin.intervene-results', compact('payoutRates', 'jackpotRates', 'currentRound'));
    }
    
    /**
     * Update payout rates
     */
    public function updatePayoutRates(Request $request)
    {
        $validated = $request->validate([
            'kcxanh' => 'required|numeric|min:1',
            'daquy' => 'required|numeric|min:1',
            'kcdo' => 'required|numeric|min:1',
            'thachanhtim' => 'nullable|numeric|min:1',
            'ngusac' => 'nullable|numeric|min:1',
            'cuoc' => 'nullable|numeric|min:1',
        ]);
        
        // Save each rate to database (3 đá thường)
        SystemSetting::setValue('gem_payout_rate_kcxanh', (string) $validated['kcxanh'], 'Tỉ lệ ăn cho kim cương xanh');
        SystemSetting::setValue('gem_payout_rate_daquy', (string) $validated['daquy'], 'Tỉ lệ ăn cho đá quý');
        SystemSetting::setValue('gem_payout_rate_kcdo', (string) $validated['kcdo'], 'Tỉ lệ ăn cho kim cương đỏ');
        
        // Save payout rates for 3 đá nổ hũ (nếu có)
        if (isset($validated['thachanhtim'])) {
            SystemSetting::setValue('gem_payout_rate_thachanhtim', (string) $validated['thachanhtim'], 'Tỉ lệ ăn cho thạch anh tím (nổ hũ)');
        }
        if (isset($validated['ngusac'])) {
            SystemSetting::setValue('gem_payout_rate_ngusac', (string) $validated['ngusac'], 'Tỉ lệ ăn cho ngũ sắc (nổ hũ)');
        }
        if (isset($validated['cuoc'])) {
            SystemSetting::setValue('gem_payout_rate_cuoc', (string) $validated['cuoc'], 'Tỉ lệ ăn cho cuốc (nổ hũ)');
        }
        
        // If AJAX request, return JSON response
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật tỉ lệ ăn thành công.',
                'payout_rates' => [
                    'kcxanh' => (float) $validated['kcxanh'],
                    'daquy' => (float) $validated['daquy'],
                    'kcdo' => (float) $validated['kcdo'],
                ],
            ]);
        }
        
        return back()->with('success', 'Đã cập nhật tỉ lệ ăn thành công.');
    }
    
    /**
     * Set round result (admin intervention)
     * Admin sets the result but round continues running until it naturally ends
     */
    public function setRoundResult(Request $request)
    {
        $validated = $request->validate([
            'round_id' => 'required|exists:rounds,id',
            'final_result' => 'required|in:kcxanh,daquy,kcdo,thachanhtim,ngusac,cuoc',
        ]);
        
        $round = Round::findOrFail($validated['round_id']);
        
        // Save admin-set result
        $round->admin_set_result = $validated['final_result'];
        
        // Nếu round đã finish, cập nhật final_result và re-process bets
        if ($round->status === 'finished') {
            // Cập nhật final_result
            $round->final_result = $validated['final_result'];
            $round->save();
            
            // Check if this is a jackpot result (nổ hũ)
            $jackpotTypes = ['thachanhtim', 'ngusac', 'cuoc'];
            $isJackpot = in_array($validated['final_result'], $jackpotTypes);
            
            // Re-process bets với final_result mới
            // Chỉ re-process các bets đã được xử lý (won/lost) với final_result cũ
            $bets = $round->bets()->whereIn('status', ['won', 'lost'])->get();
            
            if ($isJackpot) {
                // Nổ hũ: Tất cả bets đều thắng với tỉ lệ của hũ
                // Default rates for each jackpot type
                $defaultRates = [
                    'thachanhtim' => 10.00,
                    'ngusac' => 20.00,
                    'cuoc' => 50.00,
                ];
                $defaultRate = $defaultRates[$validated['final_result']] ?? 10.00;
                $jackpotPayoutRate = (float) SystemSetting::getValue('gem_payout_rate_' . $validated['final_result'], (string) $defaultRate);
                
                foreach ($bets as $bet) {
                    $expectedPayout = $bet->amount * $jackpotPayoutRate;
                    
                    if ($bet->status === 'lost') {
                        // Nếu trước đó thua, bây giờ thắng (nổ hũ)
                        $user = $bet->user;
                        $user->balance += $expectedPayout;
                        $user->save();
                        
                        $bet->update([
                            'status' => 'won',
                            'payout_amount' => $expectedPayout,
                            'payout_rate' => $jackpotPayoutRate,
                        ]);
                        
                        \Log::info("Bet {$bet->id}: Re-processed JACKPOT from lost to won, user {$user->id} received {$expectedPayout}");
                    } else if ($bet->status === 'won') {
                        // Nếu đã thắng, cập nhật payout với tỉ lệ jackpot
                        if ($bet->payout_amount != $expectedPayout) {
                            $user = $bet->user;
                            $user->balance -= $bet->payout_amount; // Trừ payout cũ
                            $user->balance += $expectedPayout; // Cộng payout mới (jackpot)
                            $user->save();
                            
                            $bet->update([
                                'payout_amount' => $expectedPayout,
                                'payout_rate' => $jackpotPayoutRate,
                            ]);
                            
                            \Log::info("Bet {$bet->id}: Re-processed JACKPOT payout, user {$user->id} adjusted from {$bet->payout_amount} to {$expectedPayout}");
                        }
                    }
                }
            } else {
                // Normal result: chỉ bets đúng loại đá mới thắng
                foreach ($bets as $bet) {
                    // Kiểm tra lại với final_result mới
                    if ($bet->gem_type === $round->final_result) {
                        // User thắng với kết quả mới
                        if ($bet->status === 'lost') {
                            // Nếu trước đó thua, bây giờ thắng
                            $payoutAmount = $bet->amount * $bet->payout_rate;
                            
                            // Cộng tiền thắng (tiền đặt cược đã bị trừ khi đặt, không cần hoàn lại)
                            $user = $bet->user;
                            $user->balance += $payoutAmount; // Thêm tiền thắng
                            $user->save();
                            
                            $bet->update([
                                'status' => 'won',
                                'payout_amount' => $payoutAmount,
                            ]);
                            
                            \Log::info("Bet {$bet->id}: Re-processed from lost to won, user {$user->id} received {$payoutAmount}");
                        } else if ($bet->status === 'won') {
                            // Nếu đã thắng rồi, kiểm tra payout_amount có đúng không
                            $expectedPayout = $bet->amount * $bet->payout_rate;
                            if ($bet->payout_amount != $expectedPayout) {
                                // Cập nhật lại payout_amount nếu sai
                                $user = $bet->user;
                                $user->balance -= $bet->payout_amount; // Trừ payout cũ
                                $user->balance += $expectedPayout; // Cộng payout mới
                                $user->save();
                                
                                $bet->update([
                                    'payout_amount' => $expectedPayout,
                                ]);
                            }
                        }
                    } else {
                        // User thua với kết quả mới
                        if ($bet->status === 'won') {
                            // Nếu trước đó thắng, bây giờ thua
                            // Trừ lại số tiền đã thắng (tiền đặt cược đã bị trừ khi đặt, không cần trừ lại)
                            $user = $bet->user;
                            $user->balance -= $bet->payout_amount; // Trừ tiền thắng đã nhận
                            $user->save();
                            
                            $bet->update([
                                'status' => 'lost',
                                'payout_amount' => null,
                            ]);
                            
                            \Log::info("Bet {$bet->id}: Re-processed from won to lost, user {$user->id} refunded {$bet->payout_amount}");
                        }
                        // Nếu đã thua rồi, không cần làm gì
                    }
                }
            }
            
            // Process các bets pending (nếu có)
            $round->processBets();
            
            return back()->with('success', 'Đã cập nhật kết quả phiên cược và xử lý lại các cược.');
        } else if ($round->status === 'running') {
            // Round đang chạy, chỉ lưu admin_set_result
            // Kết quả này sẽ được dùng khi round finish
        $round->save();
        
        return back()->with('success', 'Đã đặt kết quả phiên cược. Phiên sẽ tiếp tục chạy và kết quả này sẽ là kết quả cuối cùng.');
        } else {
            return back()->with('error', 'Chỉ có thể đặt kết quả cho phiên đang chạy hoặc đã kết thúc.');
        }
    }
    
    /**
     * Get realtime round data for admin (current result and bet amounts)
     */
    public function getRealtimeRoundData()
    {
        $round = Round::getCurrentRound();
        
        if (!$round) {
            return response()->json([
                'round' => null,
                'message' => 'Round not found. Please wait for ProcessRoundTimer to create it.',
            ]);
        }
        
        // Calculate current phase and second
        $phase = 'break';
        $currentSecond = 0;
        $currentResult = null;
        
        if ($round->status === 'running' && $round->started_at) {
            $elapsedSeconds = now()->diffInSeconds($round->started_at);
            $currentSecond = min(60, $elapsedSeconds + 1);
            
            if ($currentSecond <= 30) {
                $phase = 'betting';
            } else {
                $phase = 'result';
            }
            
            // Get current result using the same logic as client
            $exploreController = new \App\Http\Controllers\ExploreController();
            
            if ($currentSecond <= 30) {
                $currentResult = null; // Radar phase
            } else {
                $currentResult = $exploreController->getGemForSecond($round->seed, $currentSecond, $round);
            }
        }
        
        // Get total bet amounts for each gem type (only pending bets count)
        $betAmounts = \App\Models\Bet::where('round_id', $round->id)
            ->where('status', 'pending') // Only count pending bets
            ->select('gem_type', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as bet_count'))
            ->groupBy('gem_type')
            ->get()
            ->pluck('total_amount', 'gem_type')
            ->toArray();
        
        // Initialize all gem types with 0
        $allBetAmounts = [
            'kcxanh' => isset($betAmounts['kcxanh']) ? (float) $betAmounts['kcxanh'] : (isset($betAmounts['thachanh']) ? (float) $betAmounts['thachanh'] : 0),
            'daquy' => isset($betAmounts['daquy']) ? (float) $betAmounts['daquy'] : 0,
            'kcdo' => isset($betAmounts['kcdo']) ? (float) $betAmounts['kcdo'] : (isset($betAmounts['kimcuong']) ? (float) $betAmounts['kimcuong'] : 0),
        ];
        
        return response()->json([
            'round' => [
                'id' => $round->id,
                'round_number' => $round->round_number,
                'status' => $round->status,
                'phase' => $phase,
                'current_second' => $currentSecond,
                'current_result' => $currentResult,
                'admin_set_result' => $round->admin_set_result,
                'final_result' => $round->final_result,
            ],
            'bet_amounts' => $allBetAmounts,
        ]);
    }
    
    /**
     * Get bet amounts only (for realtime update every 2 seconds)
     */
    public function getBetAmounts()
    {
        $round = Round::getCurrentRound();
        
        if (!$round) {
            return response()->json(['bet_amounts' => []]);
        }
        
        // Get total bet amounts for each gem type (only pending bets count)
        $betAmounts = \App\Models\Bet::where('round_id', $round->id)
            ->where('status', 'pending') // Only count pending bets
            ->select('gem_type', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as bet_count'))
            ->groupBy('gem_type')
            ->get()
            ->pluck('total_amount', 'gem_type')
            ->toArray();
        
        // Initialize all gem types with 0
        $allBetAmounts = [
            'kcxanh' => isset($betAmounts['kcxanh']) ? (float) $betAmounts['kcxanh'] : (isset($betAmounts['thachanh']) ? (float) $betAmounts['thachanh'] : 0),
            'daquy' => isset($betAmounts['daquy']) ? (float) $betAmounts['daquy'] : 0,
            'kcdo' => isset($betAmounts['kcdo']) ? (float) $betAmounts['kcdo'] : (isset($betAmounts['kimcuong']) ? (float) $betAmounts['kimcuong'] : 0),
        ];
        
        return response()->json([
            'bet_amounts' => $allBetAmounts,
        ]);
    }

    /**
     * Member list page
     */
    public function memberList(Request $request)
    {
        $query = User::where('role', 'user');

        // Search by username or referral_code
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('referral_code', 'like', "%{$searchTerm}%")
                  ->orWhere('name', 'like', "%{$searchTerm}%")
                  ->orWhere('display_name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('phone_number', 'like', "%{$searchTerm}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.member-list', compact('users'));
    }

    /**
     * View user detail with subordinates
     */
    public function viewUserDetail($id)
    {
        $user = User::with(['referrals', 'referrer'])->findOrFail($id);
        
        // Get total deposit and withdraw for this user
        $userTotalDeposit = DepositRequest::where('user_id', $id)
            ->where('status', 'approved')
            ->sum('gem_amount') ?? 0;
        
        $userTotalWithdraw = \App\Models\WithdrawRequest::where('user_id', $id)
            ->where('status', 'approved')
            ->sum('gem_amount') ?? 0;
        
        // Get all subordinate IDs (recursive)
        $subordinateIds = $this->getAllSubordinateIds($id);
        
        // Get total deposit and withdraw for all subordinates
        $subordinatesTotalDeposit = DepositRequest::whereIn('user_id', $subordinateIds)
            ->where('status', 'approved')
            ->sum('gem_amount') ?? 0;
        
        $subordinatesTotalWithdraw = \App\Models\WithdrawRequest::whereIn('user_id', $subordinateIds)
            ->where('status', 'approved')
            ->sum('gem_amount') ?? 0;
        
        // Get subordinates with pagination
        $subordinates = User::where('referred_by', $id)
            ->withCount(['referrals'])
            ->paginate(20);
        
        return view('admin.user-detail', compact(
            'user',
            'userTotalDeposit',
            'userTotalWithdraw',
            'subordinatesTotalDeposit',
            'subordinatesTotalWithdraw',
            'subordinates'
        ));
    }

    /**
     * Get all subordinate IDs recursively
     */
    private function getAllSubordinateIds($userId)
    {
        $ids = [];
        $directSubordinates = User::where('referred_by', $userId)->pluck('id')->toArray();
        
        foreach ($directSubordinates as $subId) {
            $ids[] = $subId;
            $ids = array_merge($ids, $this->getAllSubordinateIds($subId));
        }
        
        return $ids;
    }

    /**
     * View user network tree
     */
    public function viewUserNetwork($id)
    {
        $user = User::findOrFail($id);
        
        // Build network tree recursively
        $networkTree = $this->buildNetworkTree($id);
        
        // Calculate statistics for network
        $networkStats = $this->calculateNetworkStats($id);
        
        return view('admin.user-network', compact('user', 'networkTree', 'networkStats'));
    }

    /**
     * Build network tree structure recursively
     */
    private function buildNetworkTree($userId, $level = 0, $maxLevel = 5)
    {
        if ($level > $maxLevel) {
            return null;
        }
        
        $user = User::find($userId);
        if (!$user) {
            return null;
        }
        
        // Get user statistics
        $totalDeposit = DepositRequest::where('user_id', $userId)
            ->where('status', 'approved')
            ->sum('gem_amount') ?? 0;
        
        $totalWithdraw = \App\Models\WithdrawRequest::where('user_id', $userId)
            ->where('status', 'approved')
            ->sum('gem_amount') ?? 0;
        
        $subordinatesCount = User::where('referred_by', $userId)->count();
        
        $node = [
            'user' => $user,
            'level' => $level,
            'total_deposit' => $totalDeposit,
            'total_withdraw' => $totalWithdraw,
            'subordinates_count' => $subordinatesCount,
            'children' => []
        ];
        
        // Get direct subordinates
        $directSubordinates = User::where('referred_by', $userId)->get();
        
        foreach ($directSubordinates as $subordinate) {
            $childNode = $this->buildNetworkTree($subordinate->id, $level + 1, $maxLevel);
            if ($childNode) {
                $node['children'][] = $childNode;
            }
        }
        
        return $node;
    }

    /**
     * Calculate network statistics
     */
    private function calculateNetworkStats($userId)
    {
        $allSubordinateIds = $this->getAllSubordinateIds($userId);
        
        // Count by level
        $levelCounts = [];
        $currentLevelUserIds = [$userId];
        
        for ($level = 1; $level <= 6; $level++) {
            $currentLevelUsers = User::whereIn('referred_by', $currentLevelUserIds)->get();
            $levelCounts['F' . $level] = $currentLevelUsers->count();
            $currentLevelUserIds = $currentLevelUsers->pluck('id')->toArray();
            
            if (empty($currentLevelUserIds)) {
                break;
            }
        }
        
        // Total deposit and withdraw
        $totalDeposit = DepositRequest::whereIn('user_id', $allSubordinateIds)
            ->where('status', 'approved')
            ->sum('gem_amount') ?? 0;
        
        $totalWithdraw = \App\Models\WithdrawRequest::whereIn('user_id', $allSubordinateIds)
            ->where('status', 'approved')
            ->sum('gem_amount') ?? 0;
        
        return [
            'level_counts' => $levelCounts,
            'total_subordinates' => count($allSubordinateIds),
            'total_deposit' => $totalDeposit,
            'total_withdraw' => $totalWithdraw,
        ];
    }

    /**
     * Update user login password
     */
    public function updateUserPassword(Request $request, $id)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::findOrFail($id);
        $user->password = Hash::make($validated['password']);
        $user->save();

        return back()->with('success', 'Đã cập nhật mật khẩu đăng nhập thành công.');
    }

    /**
     * Update user fund password
     */
    public function updateUserFundPassword(Request $request, $id)
    {
        $validated = $request->validate([
            'fund_password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::findOrFail($id);
        $user->fund_password = Hash::make($validated['fund_password']);
        $user->save();

        return back()->with('success', 'Đã cập nhật mật khẩu quỹ thành công.');
    }

    /**
     * Add balance (đá quý) to user
     */
    public function addBalance(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = User::findOrFail($id);
        $admin = Auth::guard('admin')->user();
        $amount = (float) $validated['amount'];
        $oldBalance = $user->balance;
        
        DB::beginTransaction();
        try {
            // Lock user row to prevent race condition
            $user = User::where('id', $id)->lockForUpdate()->first();
            
            // Add balance
            $user->balance += $amount;
            $user->save();
            
            // Log the action
            \Log::info('Admin added balance to user', [
                'admin_id' => $admin->id,
                'admin_name' => $admin->name,
                'user_id' => $user->id,
                'user_phone' => $user->phone_number,
                'amount' => $amount,
                'old_balance' => $oldBalance,
                'new_balance' => $user->balance,
                'notes' => $validated['notes'] ?? null,
            ]);
            
            DB::commit();
            
            return back()->with('success', "Đã cộng {$amount} đá quý cho thành viên. Số dư mới: " . number_format($user->balance, 2) . " đá quý.");
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error adding balance to user', [
                'user_id' => $id,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
            
            return back()->with('error', 'Có lỗi xảy ra khi cộng đá quý: ' . $e->getMessage());
        }
    }

    /**
     * Deposit page - List all deposit requests
     */
    public function deposit()
    {
        $depositRequests = DepositRequest::with(['user', 'approver'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Get active promotion
        $activePromotion = Promotion::getActivePromotion();
        
        return view('admin.deposit', compact('depositRequests', 'activePromotion'));
    }

    /**
     * Approve a deposit request
     */
    public function approveDeposit(Request $request, $id)
    {
        $depositRequest = DepositRequest::findOrFail($id);
        
        if ($depositRequest->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý rồi.');
        }

        DB::beginTransaction();
        try {
            // Update deposit request status
            $depositRequest->status = 'approved';
            $depositRequest->approved_by = Auth::guard('admin')->id();
            $depositRequest->approved_at = now();
            $depositRequest->notes = $request->input('notes');
            $depositRequest->save();

            // Update user balance
            $user = $depositRequest->user;
            $baseGemAmount = $depositRequest->gem_amount ?? ($depositRequest->amount / SystemSetting::getVndToGemRate());
            
            // Apply promotion bonus if available
            $promotion = Promotion::getActivePromotion();
            $promotionBonus = 0;
            if ($promotion) {
                $promotionBonus = $baseGemAmount * ($promotion->deposit_percentage / 100);
            }
            
            // Total amount to add to balance
            $totalGemAmount = $baseGemAmount + $promotionBonus;
            
            $user->balance += $totalGemAmount;
            
            // Calculate betting requirement:
            // deposit + (promotion_bonus * betting_multiplier)
            $bettingRequirementIncrease = $baseGemAmount;
            if ($promotion && $promotionBonus > 0) {
                $bettingMultiplier = $promotion->betting_multiplier ?? 1;
                $bettingRequirementIncrease += $promotionBonus * $bettingMultiplier;
            } else {
                // If no promotion, just add promotion bonus (if any) with 1x multiplier
                $bettingRequirementIncrease += $promotionBonus;
            }
            
            $user->betting_requirement = ($user->betting_requirement ?? 0) + $bettingRequirementIncrease;
            
            $user->save();

            // Create betting contribution for promotion bonus (if any)
            // This counts towards betting requirement for withdrawal
            if ($promotionBonus > 0) {
                BettingContribution::create([
                    'user_id' => $user->id,
                    'giftcode_usage_id' => null, // Not from giftcode
                    'amount' => $promotionBonus,
                    'source' => 'promotion',
                ]);
            }

            // Create notification for user
            Notification::createDepositApproved($user, $depositRequest->amount, $totalGemAmount, $depositRequest->id);

            DB::commit();

            return back()->with('success', 'Đã duyệt yêu cầu nạp tiền và cập nhật số dư cho user.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Reject a deposit request
     */
    public function rejectDeposit(Request $request, $id)
    {
        $depositRequest = DepositRequest::findOrFail($id);
        
        if ($depositRequest->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý rồi.');
        }

        $depositRequest->status = 'rejected';
        $depositRequest->approved_by = Auth::guard('admin')->id();
        $depositRequest->approved_at = now();
        $depositRequest->notes = $request->input('notes');
        $depositRequest->save();

        // Create notification for user
        Notification::createDepositRejected($depositRequest->user, $depositRequest->amount, $depositRequest->id, $request->input('notes'));

        return back()->with('success', 'Đã từ chối yêu cầu nạp tiền.');
    }

    /**
     * Withdraw page - List all withdraw requests
     */
    public function withdraw()
    {
        $withdrawRequests = \App\Models\WithdrawRequest::with(['user', 'approver'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('admin.withdraw', compact('withdrawRequests'));
    }

    /**
     * Approve a withdraw request
     */
    public function approveWithdraw(Request $request, $id)
    {
        $withdrawRequest = \App\Models\WithdrawRequest::findOrFail($id);
        
        if ($withdrawRequest->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý rồi.');
        }

        DB::beginTransaction();
        try {
            // Update withdraw request status
            // Note: Balance was already deducted when user submitted the withdraw request (pending status)
            // So we don't need to deduct balance again here
            $withdrawRequest->status = 'approved';
            $withdrawRequest->approved_by = Auth::guard('admin')->id();
            $withdrawRequest->approved_at = now();
            $withdrawRequest->notes = $request->input('notes');
            $withdrawRequest->save();

            // Create notification for user
            $user = $withdrawRequest->user;
            $vndAmount = $withdrawRequest->gem_amount * SystemSetting::getVndToGemRate();
            Notification::createWithdrawApproved($user, $vndAmount, $withdrawRequest->gem_amount, $withdrawRequest->id);

            DB::commit();

            return back()->with('success', 'Đã duyệt yêu cầu rút tiền thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Reject a withdraw request
     */
    public function rejectWithdraw(Request $request, $id)
    {
        $withdrawRequest = \App\Models\WithdrawRequest::findOrFail($id);
        
        if ($withdrawRequest->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý rồi.');
        }

        DB::beginTransaction();
        try {
            // Update withdraw request status
            $withdrawRequest->status = 'rejected';
            $withdrawRequest->approved_by = Auth::guard('admin')->id();
            $withdrawRequest->approved_at = now();
            $withdrawRequest->notes = $request->input('notes');
            $withdrawRequest->save();

            // Refund balance to user because balance was deducted when user submitted withdraw request
            // When admin rejects, we need to return the money back to user
            $user = $withdrawRequest->user;
            $user->balance += $withdrawRequest->gem_amount;
            $user->save();

            // Create notification for user
            $vndAmount = $withdrawRequest->gem_amount * SystemSetting::getVndToGemRate();
            Notification::createWithdrawRejected($user, $vndAmount, $withdrawRequest->gem_amount, $withdrawRequest->id, $request->input('notes'));

            DB::commit();

            return back()->with('success', 'Đã từ chối yêu cầu rút tiền và hoàn lại số dư cho user.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Agent page
     */
    public function agent(Request $request)
    {
        // Get users who have at least one F1 (direct referral)
        $query = User::whereHas('referrals')
            ->withCount('referrals')
            ->orderBy('created_at', 'desc');

        // Search by username (referral_code, name, email, phone_number)
        if ($request->filled('username')) {
            $searchTerm = $request->username;
            $query->where(function($q) use ($searchTerm) {
                $q->where('referral_code', 'like', "%{$searchTerm}%")
                  ->orWhere('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('phone_number', 'like', "%{$searchTerm}%");
            });
        }

        // Filter by date
        $dateFilter = $request->get('date_filter', 'all');
        if ($dateFilter === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($dateFilter === '7days') {
            $query->where('created_at', '>=', now()->subDays(7));
        }

        $agents = $query->get();

        // Calculate total first deposit for each agent's F1 only (not entire network)
        foreach ($agents as $agent) {
            // Get only F1 IDs (direct referrals)
            $f1Ids = $agent->referrals()->pluck('id');
            
            if ($f1Ids->isEmpty()) {
                $agent->total_first_deposit = 0;
                continue;
            }
            
            // Get first deposit for each F1 user
            $firstDeposits = DepositRequest::whereIn('user_id', $f1Ids)
                ->where('status', 'approved')
                ->select('user_id', DB::raw('MIN(approved_at) as first_approved_at'))
                ->groupBy('user_id')
                ->get();

            $totalFirstDeposit = 0;
            foreach ($firstDeposits as $firstDeposit) {
                $deposit = DepositRequest::where('user_id', $firstDeposit->user_id)
                    ->where('status', 'approved')
                    ->where('approved_at', $firstDeposit->first_approved_at)
                    ->first();
                
                if ($deposit) {
                    $totalFirstDeposit += $deposit->gem_amount ?? ($deposit->amount / SystemSetting::getVndToGemRate());
                }
            }
            
            $agent->total_first_deposit = $totalFirstDeposit;
        }

        return view('admin.agent', compact('agents'));
    }

    /**
     * Give reward to agent
     */
    public function giveAgentReward(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ], [
            'amount.required' => 'Vui lòng nhập số lượng.',
            'amount.numeric' => 'Số lượng phải là số.',
            'amount.min' => 'Số lượng phải lớn hơn 0.',
        ]);

        $agent = User::findOrFail($id);

        DB::beginTransaction();
        try {
            // Add balance to agent
            $agent->balance += $validated['amount'];
            $agent->save();
            
            // Create notification for agent
            Notification::create([
                'user_id' => $agent->id,
                'type' => 'agent_reward',
                'title' => 'Nhận thưởng đại lý',
                'message' => "Bạn đã nhận được " . number_format($validated['amount'], 2, ',', '.') . " đá quý từ hệ thống. Cảm ơn bạn đã đóng góp cho hệ thống!",
                'data' => [
                    'amount' => $validated['amount'],
                    'agent_id' => $agent->id,
                ],
            ]);
            
            DB::commit();
            
            return back()->with('success', 'Đã thưởng ' . number_format($validated['amount'], 2) . ' đá quý cho đại lý thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error giving agent reward: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi thưởng cho đại lý.');
        }
    }

    /**
     * Get F1 details for an agent
     */
    public function getAgentF1Details($id)
    {
        $agent = User::findOrFail($id);
        
        // Get F1 users (direct referrals)
        $f1Users = $agent->referrals()->get();
        
        $f1Details = [];
        foreach ($f1Users as $f1User) {
            // Get first deposit amount
            $firstDeposit = DepositRequest::where('user_id', $f1User->id)
                ->where('status', 'approved')
                ->orderBy('approved_at', 'asc')
                ->first();
            
            $firstDepositAmount = 0;
            if ($firstDeposit) {
                $firstDepositAmount = $firstDeposit->gem_amount ?? ($firstDeposit->amount / SystemSetting::getVndToGemRate());
            }
            
            // Get total betting amount (khối lượng giao dịch)
            $totalBetting = $f1User->getTotalBettingSinceLastDeposit();
            
            // Check bank account status
            $hasBankAccount = !empty($f1User->bank_name) && !empty($f1User->bank_account);
            
            $f1Details[] = [
                'id' => $f1User->id,
                'username' => $f1User->referral_code ?? $f1User->name ?? $f1User->email,
                'display_name' => $f1User->display_name ?? '-',
                'balance' => $f1User->balance ?? 0,
                'first_deposit_amount' => $firstDepositAmount,
                'total_betting' => $totalBetting,
                'has_bank_account' => $hasBankAccount,
                'bank_status' => $hasBankAccount ? 'Đã liên kết' : 'Chưa liên kết',
            ];
        }
        
        return response()->json([
            'success' => true,
            'agent' => [
                'id' => $agent->id,
                'name' => $agent->referral_code ?? $agent->name ?? $agent->email,
            ],
            'f1_details' => $f1Details,
        ]);
    }

    /**
     * Banner page
     */
    public function banner()
    {
        return view('admin.banner');
    }

    /**
     * Promotion & Giftcode page
     */
    public function promotionGiftcode()
    {
        $promotions = Promotion::orderBy('created_at', 'desc')->get();
        $giftcodes = Giftcode::orderBy('created_at', 'desc')->get();
        $activePromotion = Promotion::getActivePromotion();
        
        return view('admin.promotion-giftcode', compact('promotions', 'giftcodes', 'activePromotion'));
    }

    /**
     * Get giftcode usage history (AJAX)
     */
    public function getGiftcodeHistory(Request $request, $id)
    {
        $giftcode = Giftcode::findOrFail($id);
        
        $usages = \App\Models\GiftcodeUsage::where('giftcode_id', $id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        // Format data for JSON response
        $formattedUsages = $usages->map(function($usage) {
            return [
                'id' => $usage->id,
                'user' => [
                    'display_name' => $usage->user->display_name ?? null,
                    'name' => $usage->user->name ?? null,
                    'email' => $usage->user->email ?? null,
                ],
                'amount' => $usage->amount,
                'created_at_formatted' => $usage->created_at->format('d/m/Y H:i:s'),
                'created_at_human' => $usage->created_at->diffForHumans(),
            ];
        });
        
        return response()->json([
            'success' => true,
            'usages' => [
                'data' => $formattedUsages,
                'current_page' => $usages->currentPage(),
                'last_page' => $usages->lastPage(),
                'per_page' => $usages->perPage(),
                'total' => $usages->total(),
                'from' => $usages->firstItem(),
                'to' => $usages->lastItem(),
            ],
        ]);
    }

    /**
     * Create promotion
     * Only one promotion can be active at a time
     */
    public function createPromotion(Request $request)
    {
        $validated = $request->validate([
            'deposit_percentage' => 'required|numeric|min:0|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        // Deactivate all other promotions before creating a new one
        Promotion::where('is_active', true)->update(['is_active' => false]);

        // Create new promotion as active
        $validated['is_active'] = true;
        $promotion = Promotion::create($validated);

        // Debug: Log promotion creation
        \Log::info('Promotion created', [
            'id' => $promotion->id,
            'deposit_percentage' => $promotion->deposit_percentage,
            'start_date' => $promotion->start_date,
            'end_date' => $promotion->end_date,
            'is_active' => $promotion->is_active,
            'now' => now()->format('Y-m-d'),
        ]);

        // Send notification to all users about new promotion
        $users = User::all();
        foreach ($users as $user) {
            Notification::createPromotionNotification($user, $promotion);
        }

        return back()->with('success', 'Tạo sự kiện khuyến mãi thành công và đã gửi thông báo cho tất cả users.');
    }

    /**
     * Update active promotion
     */
    public function updatePromotion(Request $request, $id)
    {
        $promotion = Promotion::findOrFail($id);
        
        $validated = $request->validate([
            'deposit_percentage' => 'required|numeric|min:0|max:100',
            'betting_multiplier' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $promotion->update($validated);

        return back()->with('success', 'Cập nhật sự kiện khuyến mãi thành công.');
    }

    /**
     * Create giftcodes
     * Tạo 1 mã code duy nhất với quantity = số lượng (số lần có thể sử dụng)
     */
public function createGiftcodes(Request $request)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:10000',
            'value' => 'required|numeric|min:0',
            'expires_at' => 'nullable|date',
        ]);

        // Generate unique code
        do {
            $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 7));
        } while (Giftcode::where('code', $code)->exists());

        // Tạo 1 mã code duy nhất với quantity = số lượng nhập vào
        Giftcode::create([
            'code' => $code,
            'quantity' => $validated['quantity'], // Số lần có thể sử dụng
            'value' => $validated['value'],
            'expires_at' => $validated['expires_at'],
            'is_active' => true,
        ]);

        return back()->with('success', "Đã tạo giftcode {$code} thành công với số lượng {$validated['quantity']} lần sử dụng.");
    }

    /**
     * Cancel/Deactivate giftcode
     */
    public function cancelGiftcode($id)
    {
        $giftcode = Giftcode::findOrFail($id);
        $giftcode->is_active = false;
        $giftcode->save();

        return back()->with('success', 'Đã huỷ bỏ giftcode thành công.');
    }

    /**
     * System Settings page
     */
    public function settings()
    {
        $vndToGemRate = SystemSetting::getValue('vnd_to_gem_rate', '1000');
        $luckyMoneyMaxGems = SystemSetting::getValue('lucky_money_max_gems', '5');
        $commissionRates = CommissionRate::orderBy('order')->orderBy('level')->get();
        
        // Get maintenance settings
        $depositMaintenance = SystemSetting::getValue('deposit_maintenance', 'false');
        $withdrawMaintenance = SystemSetting::getValue('withdraw_maintenance', 'false');
        
        $depositMaintenanceData = json_decode($depositMaintenance, true) ?: ['enabled' => false, 'start_date' => '', 'end_date' => '', 'message' => ''];
        $withdrawMaintenanceData = json_decode($withdrawMaintenance, true) ?: ['enabled' => false, 'start_date' => '', 'end_date' => '', 'message' => ''];
        
        return view('admin.settings', compact('vndToGemRate', 'luckyMoneyMaxGems', 'commissionRates', 'depositMaintenanceData', 'withdrawMaintenanceData'));
    }

    /**
     * Update system settings
     */
    public function updateSettings(Request $request)
    {
        $rules = [];
        
        // Only validate fields that are present in the request
        if ($request->has('vnd_to_gem_rate')) {
            $rules['vnd_to_gem_rate'] = 'required|numeric|min:1';
        }
        
        if ($request->has('lucky_money_max_gems')) {
            $rules['lucky_money_max_gems'] = 'required|numeric|min:0.1|max:999';
        }
        
        $validated = $request->validate($rules);

        // Update VND to Gem rate if provided
        if (isset($validated['vnd_to_gem_rate'])) {
            SystemSetting::setValue(
                'vnd_to_gem_rate',
                (string) $validated['vnd_to_gem_rate'],
                'Tỉ lệ quy đổi: số VND cần để đổi được 1 đá quý'
            );
        }

        // Update Lucky Money max gems if provided
        if (isset($validated['lucky_money_max_gems'])) {
            SystemSetting::setValue(
                'lucky_money_max_gems',
                (string) $validated['lucky_money_max_gems'],
                'Số đá quý tối đa có thể nhận được khi mở lì xì (random từ 0.1 đến giá trị này, hỗ trợ số thập phân)'
            );
        }

        return back()->with('success', 'Cập nhật cài đặt thành công.');
    }

    /**
     * Update maintenance settings
     */
    public function updateMaintenance(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:deposit,withdraw',
            'enabled' => 'nullable|in:1',
            'start_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_date' => 'required|date',
            'end_time' => 'required|date_format:H:i',
            'message' => 'nullable|string|max:500',
        ]);

        $enabled = $request->has('enabled') && $request->enabled == '1';
        $startDateTime = $validated['start_date'] . ' ' . $validated['start_time'];
        $endDateTime = $validated['end_date'] . ' ' . $validated['end_time'];

        if ($validated['type'] === 'deposit') {
            SystemSetting::setDepositMaintenance(
                $enabled,
                $startDateTime,
                $endDateTime,
                $validated['message'] ?? null
            );
        } else {
            SystemSetting::setWithdrawMaintenance(
                $enabled,
                $startDateTime,
                $endDateTime,
                $validated['message'] ?? null
            );
        }

        return back()->with('success', 'Cập nhật bảo trì thành công.');
    }

    /**
     * Update commission rates
     */
    public function updateCommissionRates(Request $request)
    {
        $validated = $request->validate([
            'rates' => 'required|array',
            'rates.*.level' => 'required|string|max:10',
            'rates.*.rate' => 'required|numeric|min:0|max:100',
            'rates.*.is_active' => 'boolean',
            'rates.*.order' => 'integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['rates'] as $rateData) {
                CommissionRate::updateOrCreate(
                    ['level' => $rateData['level']],
                    [
                        'rate' => $rateData['rate'],
                        'is_active' => $rateData['is_active'] ?? true,
                        'order' => $rateData['order'] ?? 0,
                    ]
                );
            }

            DB::commit();
            return back()->with('success', 'Cập nhật tỉ lệ hoa hồng thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Add new commission rate
     */
    public function addCommissionRate(Request $request)
    {
        $validated = $request->validate([
            'level' => 'required|string|max:10|unique:commission_rates,level',
            'rate' => 'required|numeric|min:0|max:100',
            'order' => 'integer|min:0',
        ]);

        CommissionRate::create([
            'level' => $validated['level'],
            'rate' => $validated['rate'],
            'is_active' => true,
            'order' => $validated['order'] ?? 0,
        ]);

        return back()->with('success', 'Thêm tỉ lệ hoa hồng thành công.');
    }

    /**
     * Delete commission rate
     */
    public function deleteCommissionRate($id)
    {
        $rate = CommissionRate::findOrFail($id);
        $rate->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Xóa tỉ lệ hoa hồng thành công.']);
        }

        return back()->with('success', 'Xóa tỉ lệ hoa hồng thành công.');
    }

    /**
     * Marketing accounts management page
     */
    public function marketingAccounts()
    {
        $marketingAccounts = User::where('role', 'marketing')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('admin.marketing-accounts', compact('marketingAccounts'));
    }

    /**
     * Create marketing account
     */
    public function createMarketingAccount(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email này đã được sử dụng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
        ]);

        // Generate unique username from email (before @)
        $username = explode('@', $validated['email'])[0];
        $baseUsername = $username;
        $counter = 1;
        
        // Ensure username is unique
        while (User::where('email', 'like', $username . '@%')->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        // Generate unique phone number for marketing account (format: MKT + timestamp + random, max 20 chars)
        // phone_number column has max length of 20, so we use shorter format
        $timestamp = time();
        $random = rand(1000, 9999);
        $phoneNumber = 'MKT' . $timestamp . $random;
        // Ensure it doesn't exceed 20 characters
        if (strlen($phoneNumber) > 20) {
            $phoneNumber = 'MKT' . substr($timestamp, -10) . $random;
        }
        while (User::where('phone_number', $phoneNumber)->exists()) {
            $random = rand(1000, 9999);
            $phoneNumber = 'MKT' . substr($timestamp, -10) . $random;
        }

        // Generate unique referral code
        do {
            $referralCode = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        } while (User::where('referral_code', $referralCode)->exists());

        // Generate unique transfer code
        do {
            $transferCode = '0x' . substr(md5(uniqid(rand(), true)), 0, 12);
        } while (User::where('transfer_code', $transferCode)->exists());

        // Create marketing account
        $marketingAccount = User::create([
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone_number' => $phoneNumber,
            'role' => 'marketing',
            'balance' => 0,
            'betting_requirement' => 0,
            'name' => $username,
            'display_name' => $username,
            'referral_code' => $referralCode,
            'transfer_code' => $transferCode,
        ]);

        return back()->with('success', 'Tạo tài khoản marketing thành công.');
    }

    /**
     * Update marketing account balance (add or subtract)
     */
    public function updateMarketingBalance(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:add,subtract',
        ], [
            'amount.required' => 'Vui lòng nhập số lượng.',
            'amount.numeric' => 'Số lượng phải là số.',
            'amount.min' => 'Số lượng phải lớn hơn 0.',
            'type.required' => 'Vui lòng chọn loại thao tác.',
            'type.in' => 'Loại thao tác không hợp lệ.',
        ]);

        $marketingAccount = User::where('id', $id)
            ->where('role', 'marketing')
            ->firstOrFail();

        DB::beginTransaction();
        try {
            if ($validated['type'] === 'add') {
                $marketingAccount->balance += $validated['amount'];
            } else {
                if ($marketingAccount->balance < $validated['amount']) {
                    return back()->with('error', 'Số dư không đủ để trừ.');
                }
                $marketingAccount->balance -= $validated['amount'];
            }
            
            $marketingAccount->save();
            
            DB::commit();
            
            return back()->with('success', $validated['type'] === 'add' 
                ? 'Đã cộng ' . number_format($validated['amount'], 2) . ' đá quý thành công.' 
                : 'Đã trừ ' . number_format($validated['amount'], 2) . ' đá quý thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating marketing balance: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật số dư.');
        }
    }

    /**
     * Delete marketing account
     */
    public function deleteMarketingAccount($id)
    {
        $marketingAccount = User::where('id', $id)
            ->where('role', 'marketing')
            ->firstOrFail();

        // Check if account has balance
        if ($marketingAccount->balance > 0) {
            return back()->with('error', 'Không thể xóa tài khoản có số dư. Vui lòng rút hết số dư trước.');
        }

        $marketingAccount->delete();

        return back()->with('success', 'Đã xóa tài khoản marketing thành công.');
    }
}
