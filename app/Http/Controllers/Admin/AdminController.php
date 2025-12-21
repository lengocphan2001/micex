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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function dashboard()
    {
        // Calculate statistics - using try-catch in case tables don't exist yet
        try {
            // Optimize: Get all transaction sums in one query using groupBy
            $transactionSums = DB::table('transactions')
                ->select('type', DB::raw('SUM(amount) as total'))
                ->whereIn('type', ['profit', 'deposit', 'withdraw', 'commission', 'promotion', 'first_deposit_bonus', 'manual_bonus'])
                ->groupBy('type')
                ->pluck('total', 'type')
                ->toArray();
            
            // Get user balance sum in separate query (different table)
            $totalOnExchange = DB::table('users')->sum('balance') ?? 0;
            
            // Extract values with defaults
            $systemProfit = $transactionSums['profit'] ?? 0;
            $totalDeposit = $transactionSums['deposit'] ?? 0;
            $totalWithdraw = $transactionSums['withdraw'] ?? 0;
            $commissionPaid = $transactionSums['commission'] ?? 0;
            $promotionBonus = $transactionSums['promotion'] ?? 0;
            $firstDepositBonus = $transactionSums['first_deposit_bonus'] ?? 0;
            $manualBonus = $transactionSums['manual_bonus'] ?? 0;
        } catch (\Exception $e) {
            // If tables don't exist, set default values
            $systemProfit = 32992.11;
            $totalDeposit = 32992.11;
            $totalWithdraw = 32992.11;
            $totalOnExchange = 32992.11;
            $commissionPaid = 12992.11;
            $promotionBonus = 12992.11;
            $firstDepositBonus = 12992.11;
            $manualBonus = 12992.11;
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
        // Get current payout rates from database
        $payoutRates = [
            'thachanh' => (float) SystemSetting::getValue('gem_payout_rate_thachanh', '1.95'),
            'daquy' => (float) SystemSetting::getValue('gem_payout_rate_daquy', '5.95'),
            'kimcuong' => (float) SystemSetting::getValue('gem_payout_rate_kimcuong', '1.95'),
        ];
        
        // Get current round (không tạo mới, chỉ lấy từ database)
        $currentRound = Round::getCurrentRound();
        
        // Nếu round chưa tồn tại, tạo round rỗng để view không bị lỗi
        if (!$currentRound) {
            $currentRound = null;
        }
        
        return view('admin.intervene-results', compact('payoutRates', 'currentRound'));
    }
    
    /**
     * Update payout rates
     */
    public function updatePayoutRates(Request $request)
    {
        $validated = $request->validate([
            'thachanh' => 'required|numeric|min:1',
            'daquy' => 'required|numeric|min:1',
            'kimcuong' => 'required|numeric|min:1',
        ]);
        
        // Save each rate to database
        SystemSetting::setValue('gem_payout_rate_thachanh', (string) $validated['thachanh'], 'Tỉ lệ ăn cho thạch anh');
        SystemSetting::setValue('gem_payout_rate_daquy', (string) $validated['daquy'], 'Tỉ lệ ăn cho đá quý');
        SystemSetting::setValue('gem_payout_rate_kimcuong', (string) $validated['kimcuong'], 'Tỉ lệ ăn cho kim cương');
        
        // If AJAX request, return JSON response
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật tỉ lệ ăn thành công.',
                'payout_rates' => [
                    'thachanh' => (float) $validated['thachanh'],
                    'daquy' => (float) $validated['daquy'],
                    'kimcuong' => (float) $validated['kimcuong'],
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
            'final_result' => 'required|in:thachanh,daquy,kimcuong',
        ]);
        
        $round = Round::findOrFail($validated['round_id']);
        
        // Save admin-set result
        $round->admin_set_result = $validated['final_result'];
        
        // Nếu round đã finish, cập nhật final_result và re-process bets
        if ($round->status === 'finished') {
            // Cập nhật final_result
            $round->final_result = $validated['final_result'];
            $round->save();
            
            // Re-process bets với final_result mới
            // Chỉ re-process các bets đã được xử lý (won/lost) với final_result cũ
            $bets = $round->bets()->whereIn('status', ['won', 'lost'])->get();
            
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
            'thachanh' => isset($betAmounts['thachanh']) ? (float) $betAmounts['thachanh'] : 0,
            'daquy' => isset($betAmounts['daquy']) ? (float) $betAmounts['daquy'] : 0,
            'kimcuong' => isset($betAmounts['kimcuong']) ? (float) $betAmounts['kimcuong'] : 0,
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
            'thachanh' => isset($betAmounts['thachanh']) ? (float) $betAmounts['thachanh'] : 0,
            'daquy' => isset($betAmounts['daquy']) ? (float) $betAmounts['daquy'] : 0,
            'kimcuong' => isset($betAmounts['kimcuong']) ? (float) $betAmounts['kimcuong'] : 0,
        ];
        
        return response()->json([
            'bet_amounts' => $allBetAmounts,
        ]);
    }

    /**
     * Member list page
     */
    public function memberList()
    {
        $users = User::where('role', 'user')->paginate(20);
        return view('admin.member-list', compact('users'));
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
            $user->save();

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
            // Check if user has enough balance
            $user = $withdrawRequest->user;
            if ($user->balance < $withdrawRequest->gem_amount) {
                return back()->with('error', 'Số dư của user không đủ để thực hiện rút tiền.');
            }

            // Update withdraw request status
            $withdrawRequest->status = 'approved';
            $withdrawRequest->approved_by = Auth::guard('admin')->id();
            $withdrawRequest->approved_at = now();
            $withdrawRequest->notes = $request->input('notes');
            $withdrawRequest->save();

            // Deduct balance from user
            $user->balance -= $withdrawRequest->gem_amount;
            $user->save();

            // Create notification for user
            $vndAmount = $withdrawRequest->gem_amount * SystemSetting::getVndToGemRate();
            Notification::createWithdrawApproved($user, $vndAmount, $withdrawRequest->gem_amount, $withdrawRequest->id);

            DB::commit();

            return back()->with('success', 'Đã duyệt yêu cầu rút tiền và trừ số dư của user.');
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
            $withdrawRequest->status = 'rejected';
            $withdrawRequest->approved_by = Auth::guard('admin')->id();
            $withdrawRequest->approved_at = now();
            $withdrawRequest->notes = $request->input('notes');
            $withdrawRequest->save();

            // KHÔNG cần hoàn lại balance vì khi user tạo withdraw request, balance CHƯA bị trừ
            // Balance chỉ bị trừ khi admin approve request
            // Nếu reject, chỉ cần đổi status, không cần thay đổi balance

            // Create notification for user
            $user = $withdrawRequest->user;
            $vndAmount = $withdrawRequest->gem_amount * SystemSetting::getVndToGemRate();
            Notification::createWithdrawRejected($user, $vndAmount, $withdrawRequest->gem_amount, $withdrawRequest->id, $request->input('notes'));

            DB::commit();

            return back()->with('success', 'Đã từ chối yêu cầu rút tiền.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Agent page
     */
    public function agent()
    {
        return view('admin.agent');
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
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $promotion->update($validated);

        return back()->with('success', 'Cập nhật sự kiện khuyến mãi thành công.');
    }

    /**
     * Create giftcodes
     */
    public function createGiftcodes(Request $request)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:10000',
            'value' => 'required|numeric|min:0',
            'expires_at' => 'nullable|date',
        ]);

        $giftcodes = [];
        for ($i = 0; $i < $validated['quantity']; $i++) {
            // Generate unique code
            do {
                $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 7));
            } while (Giftcode::where('code', $code)->exists());

            $giftcodes[] = [
                'code' => $code,
                'quantity' => 1, // Each giftcode can be used once
                'value' => $validated['value'],
                'expires_at' => $validated['expires_at'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert in batches for performance
        Giftcode::insert($giftcodes);

        return back()->with('success', "Đã tạo {$validated['quantity']} giftcode thành công.");
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
        $commissionRates = CommissionRate::orderBy('order')->orderBy('level')->get();
        return view('admin.settings', compact('vndToGemRate', 'commissionRates'));
    }

    /**
     * Update system settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'vnd_to_gem_rate' => 'required|numeric|min:1',
        ]);

        SystemSetting::setValue(
            'vnd_to_gem_rate',
            (string) $validated['vnd_to_gem_rate'],
            'Tỉ lệ quy đổi: số VND cần để đổi được 1 đá quý'
        );

        return back()->with('success', 'Cập nhật cài đặt thành công.');
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
}
