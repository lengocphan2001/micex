# Phân tích yêu cầu: Hệ thống 2 ví (Ví nạp & Ví Thưởng)

## Tổng quan yêu cầu

### 1. Hai loại ví

#### Ví nạp (Deposit Wallet)
- **Vai trò**: Nhận tiền từ nạp tiền
- **Hành vi**: 
  - User nạp tiền → + thẳng vào ví nạp
  - Có thể cược từ ví nạp
  - Có thể rút tiền từ ví nạp

#### Ví thưởng (Reward Wallet)
- **Vai trò**: Nhận tiền thưởng từ lì xì & Giftcode
- **Hành vi**:
  - Nhận tiền thưởng từ lì xì (LuckyMoneyOpen)
  - Nhận tiền thưởng từ Giftcode
  - Có thể cược từ ví thưởng
  - **KHÔNG thể rút trực tiếp** → Phải chuyển sang ví nạp trước
  - **Thu hồi sau 3h** nếu không phát sinh cược

### 2. Chuyển tiền từ ví thưởng sang ví nạp

- **Điều kiện**: Tối thiểu 5$ (hoặc 5 đá quý)
- **Hành vi**: Chuyển từ `reward_balance` → `balance`
- **Mục đích**: Để có thể rút tiền (vì chỉ rút được từ ví nạp)

### 3. Logic thu hồi tiền thưởng

- **Điều kiện**: Sau 3 giờ nếu không phát sinh cược
- **Hành vi**: Trừ tiền từ `reward_balance` của user
- **Cần tracking**: 
  - Thời gian nhận tiền thưởng cuối cùng
  - Thời gian cược cuối cùng từ ví thưởng
  - Nếu `last_bet_at` < `last_reward_at + 3h` → Thu hồi

### 4. Quản lý quỹ hệ thống (System Fund)

- **Khởi tạo**: 1000$ (hoặc 1000 đá quý)
- **Cộng vào quỹ**:
  - User nạp tiền: Chỉ cộng số tiền nạp (không tính khuyến mãi)
    - Ví dụ: User nạp 100$ + khuyến mãi 20$ → Chỉ cộng 100$ vào quỹ
  - User cược và thua: Số tiền thua được cộng vào quỹ
- **Trừ khỏi quỹ**:
  - User rút tiền và admin đã duyệt: Trừ số tiền rút
- **Lưu trữ**: SystemSetting với key `system_fund`

## Cấu trúc Database cần thêm

### 1. Users table - Thêm columns:
```sql
ALTER TABLE users ADD COLUMN reward_balance DECIMAL(15,2) DEFAULT 0;
ALTER TABLE users ADD COLUMN last_reward_at DATETIME NULL;
ALTER TABLE users ADD COLUMN last_bet_from_reward_at DATETIME NULL;
```

### 2. SystemSettings table - Thêm setting:
```sql
INSERT INTO system_settings (key, value, description) 
VALUES ('system_fund', '1000', 'Tổng quỹ hệ thống (đá quý)');
```

## Logic cần thay đổi

### 1. Nạp tiền (DepositController + AdminController)

**Hiện tại:**
```php
$user->balance += $totalGemAmount; // Cộng cả tiền nạp + khuyến mãi
```

**Cần thay đổi:**
```php
// Chỉ cộng tiền nạp vào ví nạp
$user->balance += $baseGemAmount;

// Cộng khuyến mãi vào ví thưởng (nếu có)
if ($promotionBonus > 0) {
    $user->reward_balance += $promotionBonus;
    $user->last_reward_at = now();
}

// Cộng vào quỹ hệ thống: CHỈ số tiền nạp (không tính khuyến mãi)
$systemFund = SystemSetting::getValue('system_fund', '1000');
$systemFund = (float)$systemFund + $baseGemAmount;
SystemSetting::setValue('system_fund', (string)$systemFund, 'Tổng quỹ hệ thống');
```

### 2. Thưởng từ Lì xì (LuckyMoneyOpen)

**Cần thay đổi:**
```php
// Thay vì cộng vào balance, cộng vào reward_balance
$user->reward_balance += $amount;
$user->last_reward_at = now();
$user->save();
```

### 3. Thưởng từ Giftcode

**Cần thay đổi:**
```php
// Thay vì cộng vào balance, cộng vào reward_balance
$user->reward_balance += $giftcodeAmount;
$user->last_reward_at = now();
$user->save();
```

### 4. Cược (Bet logic)

**Cần thay đổi:**
```php
// Ưu tiên trừ từ ví nạp trước, nếu không đủ thì trừ từ ví thưởng
if ($user->balance >= $betAmount) {
    $user->balance -= $betAmount;
    $deductedFrom = 'deposit';
} else {
    $remaining = $betAmount - $user->balance;
    $user->balance = 0;
    $user->reward_balance -= $remaining;
    $deductedFrom = 'reward';
    
    // Cập nhật thời gian cược từ ví thưởng
    $user->last_bet_from_reward_at = now();
}

// Nếu cược từ ví thưởng, reset timer thu hồi
if ($deductedFrom === 'reward') {
    $user->last_bet_from_reward_at = now();
}
```

### 5. Thắng cược (Payout)

**Cần thay đổi:**
```php
// Nếu cược từ ví nạp → Thắng vào ví nạp
// Nếu cược từ ví thưởng → Thắng vào ví thưởng
// (hoặc có thể quy định thắng luôn vào ví nạp)

// Cộng vào quỹ hệ thống khi user thua
// (đã có logic trong Round::processBets())
```

### 6. Rút tiền (WithdrawController)

**Hiện tại:**
```php
if ($user->balance < $validated['gem_amount']) {
    // Error: Không đủ số dư
}
```

**Cần thay đổi:**
```php
// Chỉ kiểm tra ví nạp (balance)
if ($user->balance < $validated['gem_amount']) {
    // Error: Không đủ số dư trong ví nạp
    // Gợi ý: Chuyển từ ví thưởng sang ví nạp
}

// Khi admin approve withdraw:
// Trừ khỏi quỹ hệ thống
$systemFund = SystemSetting::getValue('system_fund', '1000');
$systemFund = (float)$systemFund - $withdrawRequest->gem_amount;
SystemSetting::setValue('system_fund', (string)$systemFund, 'Tổng quỹ hệ thống');
```

### 7. Chuyển tiền từ ví thưởng sang ví nạp

**Tạo mới:**
```php
// Route: POST /wallet/transfer-reward-to-deposit
// Controller: WalletController::transferRewardToDeposit()

public function transferRewardToDeposit(Request $request) {
    $validated = $request->validate([
        'amount' => 'required|numeric|min:5', // Tối thiểu 5$
    ]);
    
    $user = Auth::guard('web')->user();
    
    if ($user->reward_balance < $validated['amount']) {
        return response()->json([
            'success' => false,
            'message' => 'Số dư ví thưởng không đủ.',
        ], 400);
    }
    
    DB::beginTransaction();
    try {
        $user->reward_balance -= $validated['amount'];
        $user->balance += $validated['amount'];
        $user->save();
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'Chuyển tiền thành công.',
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
        ], 500);
    }
}
```

### 8. Thu hồi tiền thưởng (Job/Command)

**Tạo mới:**
```php
// Command: php artisan reward:expire
// Chạy định kỳ mỗi phút hoặc mỗi 5 phút

public function handle() {
    $users = User::where('reward_balance', '>', 0)
        ->whereNotNull('last_reward_at')
        ->get();
    
    foreach ($users as $user) {
        // Kiểm tra: Nếu không có cược từ ví thưởng trong 3h
        if ($user->last_bet_from_reward_at === null) {
            // Nếu last_reward_at + 3h < now() → Thu hồi
            $expireTime = $user->last_reward_at->addHours(3);
            if (now() >= $expireTime) {
                $user->reward_balance = 0;
                $user->save();
                \Log::info("Expired reward balance for user {$user->id}");
            }
        } else {
            // Nếu có cược từ ví thưởng, kiểm tra last_bet_from_reward_at
            $expireTime = $user->last_bet_from_reward_at->addHours(3);
            if (now() >= $expireTime) {
                $user->reward_balance = 0;
                $user->last_bet_from_reward_at = null;
                $user->save();
                \Log::info("Expired reward balance for user {$user->id}");
            }
        }
    }
}
```

### 9. Cập nhật quỹ hệ thống khi user thua

**Trong Round::processBets():**
```php
// Khi user thua, cộng số tiền thua vào quỹ hệ thống
if ($bet->status === 'lost') {
    $systemFund = SystemSetting::getValue('system_fund', '1000');
    $systemFund = (float)$systemFund + $bet->amount;
    SystemSetting::setValue('system_fund', (string)$systemFund, 'Tổng quỹ hệ thống');
}
```

## UI/UX cần thay đổi

### 1. Hiển thị số dư
- Hiển thị cả 2 ví: Ví nạp và Ví thưởng
- Có thể toggle giữa 2 ví

### 2. Chuyển tiền
- Button "Chuyển từ ví thưởng sang ví nạp"
- Input số tiền (tối thiểu 5$)
- Validation và confirm

### 3. Cược
- Hiển thị số dư tổng (ví nạp + ví thưởng)
- Tự động ưu tiên ví nạp trước

### 4. Admin Dashboard
- Hiển thị tổng quỹ hệ thống
- Lịch sử thay đổi quỹ (có thể thêm bảng `fund_history`)

## Files cần tạo/sửa

### Tạo mới:
1. `database/migrations/XXXX_add_reward_wallet_to_users.php`
2. `app/Http/Controllers/WalletController.php`
3. `app/Console/Commands/ExpireRewardBalance.php`
4. `routes/web.php` - Thêm routes cho wallet transfer

### Sửa đổi:
1. `app/Models/User.php` - Thêm reward_balance, methods
2. `app/Http/Controllers/DepositController.php` - Cập nhật logic nạp
3. `app/Http/Controllers/Admin/AdminController.php` - Cập nhật approve deposit/withdraw
4. `app/Models/Round.php` - Cập nhật logic cược và thắng/thua
5. `app/Http/Controllers/ExploreController.php` - Cập nhật logic lì xì
6. `app/Http/Controllers/GiftcodeController.php` - Cập nhật logic giftcode
7. Views: Hiển thị 2 ví, chuyển tiền

## Lưu ý quan trọng

1. **Backward compatibility**: Users hiện tại có `balance` → Giữ nguyên, coi như ví nạp
2. **Transaction safety**: Tất cả operations liên quan đến balance phải dùng DB transaction
3. **Race conditions**: Sử dụng `lockForUpdate()` khi cần
4. **Logging**: Log tất cả thay đổi quỹ hệ thống để audit
5. **Testing**: Test kỹ các edge cases:
   - Chuyển tiền khi đang cược
   - Thu hồi tiền thưởng khi đang cược
   - Cược từ cả 2 ví cùng lúc
