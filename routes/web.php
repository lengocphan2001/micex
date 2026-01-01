<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // If user is logged in, redirect to dashboard
    if (\Illuminate\Support\Facades\Auth::guard('web')->check()) {
        return redirect()->route('dashboard');
    }
    // Otherwise redirect to login
    return redirect()->route('login');
});

// CSRF Token refresh endpoint
Route::get('/csrf-token', function () {
    try {
        $request = request();

        // IMPORTANT:
        // Do NOT regenerate the CSRF token here.
        // Regenerating the token during normal browsing can desync already-rendered
        // Blade forms that still contain an older hidden `_token`, causing 419 errors.
        // This endpoint is only meant to expose the current token for JS clients.

        return response()->json([
            'token' => csrf_token(),
        ]);
    } catch (\Exception $e) {
        // If there's an error, just return current token
        return response()->json([
            'token' => csrf_token(),
        ]);
    }
})->middleware('web');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:5,1');
    
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
    
    // Password Reset Routes
    Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
});

// Protected routes for authenticated users
Route::middleware('auth')->group(function () {
    // Dashboard - default
    Route::get('/dashboard', function () {
        $user = \Illuminate\Support\Facades\Auth::guard('web')->user();
        $sliders = \App\Models\Slider::where('is_active', true)
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get recent notifications for dropdown
        $recentNotifications = $user ? \App\Models\Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get() : collect();
        
        // Get unread count
        $unreadCount = $user ? \App\Models\Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count() : 0;
        
        return view('dashboard', compact('sliders', 'recentNotifications', 'unreadCount'));
    })->name('dashboard');

    // Explore screen
    Route::get('/explore', [\App\Http\Controllers\ExploreController::class, 'index'])->name('explore');
    
    // Explore API endpoints
    Route::get('/api/explore/current-round', [\App\Http\Controllers\ExploreController::class, 'getCurrentRound'])->name('explore.current-round');
    Route::post('/api/explore/save-result', [\App\Http\Controllers\ExploreController::class, 'saveResult'])->name('explore.save-result');
    Route::post('/api/explore/bet', [\App\Http\Controllers\ExploreController::class, 'placeBet'])->name('explore.bet');
    Route::get('/api/explore/my-bet', [\App\Http\Controllers\ExploreController::class, 'getMyBet'])->name('explore.my-bet');
    Route::get('/api/explore/bet-amounts', [\App\Http\Controllers\ExploreController::class, 'getBetAmounts'])->name('explore.bet-amounts');
    Route::get('/api/explore/gem-types', [\App\Http\Controllers\ExploreController::class, 'getGemTypes'])->name('explore.gem-types');
    Route::get('/api/explore/recent-rounds', [\App\Http\Controllers\ExploreController::class, 'getRecentRounds'])->name('explore.recent-rounds');
    Route::get('/api/explore/round-result', [\App\Http\Controllers\ExploreController::class, 'getRoundResult'])->name('explore.round-result');
    Route::get('/api/explore/signal-grid-rounds', [\App\Http\Controllers\ExploreController::class, 'getSignalGridRounds'])->name('explore.signal-grid-rounds');
    Route::post('/api/explore/signal-grid-rounds/append', [\App\Http\Controllers\ExploreController::class, 'appendSignalGridRound'])->name('explore.signal-grid-rounds.append');

    // Assets screen
    Route::get('/assets', function () {
        return view('assets');
    })->name('assets');

    // Deposit screens
    Route::get('/deposit', function () {
        return view('deposit.index');
    })->name('deposit');

    Route::get('/deposit/bank', function () {
        $user = \Illuminate\Support\Facades\Auth::guard('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }
        // Get VND to Gem rate from settings
        $vndToGemRate = \App\Models\SystemSetting::getVndToGemRate();
        // Get active promotion
        $activePromotion = \App\Models\Promotion::getActivePromotion();
        // Get latest pending deposit request for countdown
        $pendingDeposit = \App\Models\DepositRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->first();
        // Check maintenance
        $isMaintenance = \App\Models\SystemSetting::isDepositMaintenance();
        $maintenanceMessage = \App\Models\SystemSetting::getDepositMaintenanceMessage();
        return view('deposit.bank', compact('user', 'vndToGemRate', 'activePromotion', 'pendingDeposit', 'isMaintenance', 'maintenanceMessage'));
    })->name('deposit.bank');

    Route::post('/deposit/submit', [DepositController::class, 'submit'])->name('deposit.submit')->middleware('throttle:10,1');
    Route::get('/deposit/check-status/{id}', [DepositController::class, 'checkStatus'])->name('deposit.check-status');

    // Withdraw screen
    Route::get('/withdraw', function () {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }
        $vndToGemRate = \App\Models\SystemSetting::getVndToGemRate();
        // Check maintenance
        $isMaintenance = \App\Models\SystemSetting::isWithdrawMaintenance();
        $maintenanceMessage = \App\Models\SystemSetting::getWithdrawMaintenanceMessage();
        return view('withdraw', compact('user', 'vndToGemRate', 'isMaintenance', 'maintenanceMessage'));
    })->name('withdraw');

    Route::post('/withdraw/submit', [\App\Http\Controllers\WithdrawController::class, 'submit'])->name('withdraw.submit');
    Route::get('/withdraw/check-status/{id}', [\App\Http\Controllers\WithdrawController::class, 'checkStatus'])->name('withdraw.check-status');

    // Profile / My page
    Route::get('/me', function () {
        return view('me');
    })->name('me');

    Route::get('/me/edit', function () {
        return view('me-edit');
    })->name('me.edit');

    Route::get('/me/bank-link', function () {
        return view('me-bank-link');
    })->name('me.bank');

    Route::get('/me/change-login-password', function () {
        return view('me-change-login-password');
    })->name('me.change-login-password');

    Route::get('/me/change-fund-password', function (Request $request) {
        // Ensure session is started
        if (!$request->hasSession()) {
            $request->session()->start();
        }
        // Ensure CSRF token exists in session
        if (!$request->session()->has('_token')) {
            $request->session()->regenerateToken();
        }

        $user = \Illuminate\Support\Facades\Auth::guard('web')->user();
        if (!$user || empty($user->fund_password)) {
            return redirect()->route('me.bank')->with('error', 'Bạn chưa có mật khẩu quỹ. Vui lòng tạo mật khẩu quỹ trước.');
        }
        return view('me-change-fund-password');
    })->name('me.change-fund-password');

    Route::get('/transaction-history', function () {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Get user's bets with round information, ordered by created_at desc
        $bets = \App\Models\Bet::where('user_id', $user->id)
            ->with('round')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Gem types mapping
        $gemTypes = [
            'kcxanh' => ['name' => 'Kim Cương Xanh', 'icon' => 'kcxanh.png'],
            'thachanhtim' => ['name' => 'Thạch Anh Tím', 'icon' => 'thachanhtim.png'],
            'ngusac' => ['name' => 'Ngũ Sắc', 'icon' => 'ngusac.png'],
            'daquy' => ['name' => 'Đá Quý', 'icon' => 'daquy.png'],
            'cuoc' => ['name' => 'Cuốc', 'icon' => 'cuoc.png'],
            'kcdo' => ['name' => 'Kim Cương Đỏ', 'icon' => 'kcdo.png'],
            // Backward compatibility: map old values to new ones
            'thachanh' => ['name' => 'Kim Cương Xanh', 'icon' => 'kcxanh.png'],
            'kimcuong' => ['name' => 'Kim Cương Đỏ', 'icon' => 'kcdo.png'],
        ];
        
        return view('transaction-history', compact('bets', 'gemTypes'));
    })->name('transaction-history');

    Route::get('/notifications', function () {
        $user = \Illuminate\Support\Facades\Auth::guard('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }
        
        $notifications = \App\Models\Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('notifications', compact('notifications'));
    })->name('notifications');
    
    Route::post('/notifications/{id}/read', function ($id) {
        $user = \Illuminate\Support\Facades\Auth::guard('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $notification = \App\Models\Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }
        
        $notification->markAsRead();
        
        return response()->json(['success' => true]);
    })->name('notifications.read');

    Route::get('/deposit-withdraw-history', function () {
        $user = \Illuminate\Support\Facades\Auth::guard('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Get deposit and withdraw requests
        $depositRequests = \App\Models\DepositRequest::where('user_id', $user->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
        $withdrawRequests = \App\Models\WithdrawRequest::where('user_id', $user->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Merge and sort by created_at
        $allRequests = $depositRequests->map(function($request) {
            return [
                'type' => 'deposit',
                'id' => $request->id,
                'amount' => $request->amount,
                'gem_amount' => $request->gem_amount,
                'status' => $request->status,
                'created_at' => $request->created_at,
                'approved_at' => $request->approved_at,
                'notes' => $request->notes,
                'bank_name' => null,
                'bank_account' => null,
                'bank_full_name' => null,
                'transfer_code' => $request->transfer_code,
            ];
        })->concat($withdrawRequests->map(function($request) {
            return [
                'type' => 'withdraw',
                'id' => $request->id,
                'amount' => $request->amount,
                'gem_amount' => $request->gem_amount,
                'status' => $request->status,
                'created_at' => $request->created_at,
                'approved_at' => $request->approved_at,
                'notes' => $request->notes,
                'bank_name' => $request->bank_name,
                'bank_account' => $request->bank_account,
                'bank_full_name' => $request->bank_full_name,
                'transfer_code' => null,
            ];
        }))->sortByDesc('created_at')->values();
        
        return view('deposit-withdraw-history', compact('allRequests'));
    })->name('deposit-withdraw-history');

    Route::get('/subordinate-system', [\App\Http\Controllers\SubordinateSystemController::class, 'index'])->name('subordinate-system');
    Route::get('/subordinate-system/check-withdraw-status', [\App\Http\Controllers\SubordinateSystemController::class, 'checkWithdrawStatus'])->name('subordinate-system.check-withdraw-status');
    Route::post('/subordinate-system/withdraw-commission', [\App\Http\Controllers\SubordinateSystemController::class, 'withdrawCommission'])->name('subordinate-system.withdraw-commission')->middleware('throttle:5,1');

    Route::post('/me/bank-link', [ProfileController::class, 'bankLink'])->name('me.bank.submit');
    Route::post('/me/change-login-password', [ProfileController::class, 'changeLoginPassword'])->name('me.change-login-password.submit');
    Route::post('/me/create-fund-password', [ProfileController::class, 'createFundPassword'])->name('me.create-fund-password.submit');
    Route::post('/me/send-fund-password-verification-code', [ProfileController::class, 'sendFundPasswordVerificationCode'])->name('me.send-fund-password-verification-code');
    Route::post('/me/change-fund-password', [ProfileController::class, 'changeFundPassword'])->name('me.change-fund-password.submit');

    // Giftcode
    Route::post('/giftcode/redeem', [\App\Http\Controllers\GiftcodeController::class, 'redeem'])->name('giftcode.redeem');

    // Lucky Money
    Route::get('/api/lucky-money/status', [\App\Http\Controllers\LuckyMoneyController::class, 'getStatus'])->name('lucky-money.status');
    Route::post('/api/lucky-money/open', [\App\Http\Controllers\LuckyMoneyController::class, 'open'])->name('lucky-money.open');

    // Logout route
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// Admin Routes - Áp dụng middleware set.admin.guard cho toàn bộ admin routes để tách biệt session
Route::prefix('admin')->name('admin.')->middleware('set.admin.guard')->group(function () {
    // Admin Authentication - chỉ cho phép khi chưa login admin
    Route::middleware('guest.admin')->group(function () {
        Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminLoginController::class, 'login']);
    });

    // Admin Protected Routes
    Route::middleware('admin')->group(function () {
        Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
        
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/intervene-results', [AdminController::class, 'interveneResults'])->name('intervene-results');
        Route::post('/intervene-results/payout-rates', [AdminController::class, 'updatePayoutRates'])->name('intervene-results.update-rates');
        Route::post('/intervene-results/set-round-result', [AdminController::class, 'setRoundResult'])->name('intervene-results.set-result');
        Route::get('/api/intervene-results/realtime', [AdminController::class, 'getRealtimeRoundData'])->name('intervene-results.realtime');
        Route::get('/api/intervene-results/bet-amounts', [AdminController::class, 'getBetAmounts'])->name('intervene-results.bet-amounts');
        Route::get('/member-list', [AdminController::class, 'memberList'])->name('member-list');
        Route::get('/member/{id}', [AdminController::class, 'viewUserDetail'])->name('member.detail');
        Route::get('/member/{id}/network', [AdminController::class, 'viewUserNetwork'])->name('member.network');
        Route::post('/member/{id}/update-password', [AdminController::class, 'updateUserPassword'])->name('member.update-password');
        Route::post('/member/{id}/update-fund-password', [AdminController::class, 'updateUserFundPassword'])->name('member.update-fund-password');
        Route::post('/member/{id}/add-balance', [AdminController::class, 'addBalance'])->name('member.add-balance');
        Route::get('/deposit', [AdminController::class, 'deposit'])->name('deposit');
        Route::post('/deposit/{id}/approve', [AdminController::class, 'approveDeposit'])->name('deposit.approve');
        Route::post('/deposit/{id}/reject', [AdminController::class, 'rejectDeposit'])->name('deposit.reject');
        Route::get('/withdraw', [AdminController::class, 'withdraw'])->name('withdraw');
        Route::post('/withdraw/{id}/approve', [AdminController::class, 'approveWithdraw'])->name('withdraw.approve');
        Route::post('/withdraw/{id}/reject', [AdminController::class, 'rejectWithdraw'])->name('withdraw.reject');
        Route::get('/agent', [AdminController::class, 'agent'])->name('agent');
        Route::post('/agent/{id}/reward', [AdminController::class, 'giveAgentReward'])->name('agent.reward');
        Route::get('/api/agent/{id}/f1-details', [AdminController::class, 'getAgentF1Details'])->name('agent.f1-details');
        Route::get('/banner', [AdminController::class, 'banner'])->name('banner');
        Route::get('/promotion-giftcode', [AdminController::class, 'promotionGiftcode'])->name('promotion-giftcode');
        Route::post('/promotion-giftcode/promotion', [AdminController::class, 'createPromotion'])->name('promotion.create');
        Route::put('/promotion-giftcode/promotion/{id}', [AdminController::class, 'updatePromotion'])->name('promotion.update');
        Route::post('/promotion-giftcode/giftcode', [AdminController::class, 'createGiftcodes'])->name('giftcode.create');
        Route::post('/promotion-giftcode/giftcode/{id}/cancel', [AdminController::class, 'cancelGiftcode'])->name('giftcode.cancel');
        Route::get('/promotion-giftcode/giftcode/{id}/history', [AdminController::class, 'getGiftcodeHistory'])->name('giftcode.history');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
        Route::post('/settings/maintenance', [AdminController::class, 'updateMaintenance'])->name('settings.maintenance.update');
        Route::post('/settings/commission-rates', [AdminController::class, 'updateCommissionRates'])->name('settings.commission-rates.update');
        Route::post('/settings/commission-rates/add', [AdminController::class, 'addCommissionRate'])->name('settings.commission-rates.add');
        Route::delete('/settings/commission-rates/{id}', [AdminController::class, 'deleteCommissionRate'])->name('settings.commission-rates.delete');
        
        // Marketing Accounts Management
        Route::get('/marketing-accounts', [AdminController::class, 'marketingAccounts'])->name('marketing-accounts');
        Route::post('/marketing-accounts/create', [AdminController::class, 'createMarketingAccount'])->name('marketing-accounts.create');
        Route::post('/marketing-accounts/{id}/update-balance', [AdminController::class, 'updateMarketingBalance'])->name('marketing-accounts.update-balance');
        Route::delete('/marketing-accounts/{id}', [AdminController::class, 'deleteMarketingAccount'])->name('marketing-accounts.delete');
        
        // Slider Management
        Route::resource('sliders', SliderController::class);
    });
});
