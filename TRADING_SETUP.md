# 🚀 Hướng dẫn chạy Trading Game

## 📋 Yêu cầu

- PHP 8.1+
- Composer
- Node.js & NPM
- Database (MySQL/PostgreSQL/SQLite)
- Redis (tùy chọn, cho cache)

## 🔧 Bước 1: Cài đặt Dependencies

```bash
# Cài đặt PHP dependencies
composer install

# Cài đặt Node dependencies
npm install

# Build assets
npm run build
```

## 🗄️ Bước 2: Setup Database

```bash
# Tạo file .env nếu chưa có
cp .env.example .env

# Generate app key
php artisan key:generate

# Chạy migrations
php artisan migrate
```

## ⚙️ Bước 3: Chạy Laravel Scheduler

**Laravel Scheduler** sẽ tự động chạy các commands sau mỗi giây:
- `price:tick` - Cập nhật giá từ Binance
- `candle:process` - Xử lý candles
- `trading:process-bets` - Xử lý kết quả bets

### Cách 1: Chạy Scheduler trong Development (Khuyến nghị cho test)

Mở **2 terminal windows**:

**Terminal 1 - Laravel Server:**
```bash
php artisan serve
```

**Terminal 2 - Laravel Scheduler:**
```bash
php artisan schedule:work
```

### Cách 2: Chạy Scheduler với Screen/Tmux (Background)

```bash
# Sử dụng screen
screen -S scheduler
php artisan schedule:work
# Nhấn Ctrl+A+D để detach

# Hoặc sử dụng tmux
tmux new -s scheduler
php artisan schedule:work
# Nhấn Ctrl+B+D để detach
```

### Cách 3: Setup Cron Job (Production)

Thêm vào crontab:
```bash
crontab -e
```

Thêm dòng sau (thay `/path/to/micex` bằng đường dẫn thực tế):
```
* * * * * cd /path/to/micex && php artisan schedule:run >> /dev/null 2>&1
```

## 🎮 Bước 4: Chạy Application

### Development Mode

```bash
# Terminal 1: Laravel Server
php artisan serve

# Terminal 2: Laravel Scheduler
php artisan schedule:work

# Terminal 3 (tùy chọn): Watch assets
npm run dev
```

Sau đó truy cập: `http://localhost:8000/games/trading`

### Production Mode

```bash
# Build assets
npm run build

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Chạy với Nginx/Apache
# Đảm bảo cron job đã được setup (Bước 3 - Cách 3)
```

## 🔍 Bước 5: Kiểm tra Commands

### Test từng command thủ công:

```bash
# Test Price Engine
php artisan price:tick

# Test Candle Engine
php artisan candle:process

# Test Trading Bets Engine
php artisan trading:process-bets
```

### Kiểm tra logs:

```bash
# Xem log Laravel
tail -f storage/logs/laravel.log

# Hoặc dùng pail (nếu đã cài)
php artisan pail
```

## 🌐 Bước 6: Kết nối Binance WebSocket

Trading game sẽ tự động kết nối Binance WebSocket để lấy giá real-time:
- WebSocket: `wss://stream.binance.com:9443/ws/btcusdt@trade`
- Nếu WebSocket lỗi, sẽ fallback về Laravel API: `/api/trading/price`

**Lưu ý**: Không cần cấu hình gì thêm, frontend sẽ tự động kết nối.

## 👤 Bước 7: Test với User

1. **Đăng nhập/Đăng ký** user
2. **Nạp tiền** vào ví (deposit hoặc reward)
3. **Vào trang Trading**: `/games/trading`
4. **Đặt cược** CALL hoặc PUT
5. **Chờ 30 giây** để round kết thúc
6. **Kiểm tra kết quả** - balance sẽ tự động cập nhật

## 🔧 Bước 8: Admin Settings (Nếu là Admin)

1. **Vào trang Trading** với tài khoản admin
2. **Panel Admin** sẽ hiển thị ở cuối trang
3. **Cài đặt ép nến**:
   - **Hướng ép**: Tự nhiên / Ép lên / Ép xuống
   - **Giây cuối**: 1-10 giây
   - **Độ lệch giá**: 0-50
4. **Click "Lưu cài đặt"** để áp dụng

## ⚠️ Troubleshooting

### Lỗi: "Price not updating"

**Nguyên nhân**: `price:tick` command không chạy

**Giải pháp**:
```bash
# Kiểm tra scheduler có chạy không
ps aux | grep "schedule:work"

# Chạy thủ công để test
php artisan price:tick

# Kiểm tra cache
php artisan cache:clear
```

### Lỗi: "Bets not processing"

**Nguyên nhân**: `trading:process-bets` command không chạy

**Giải pháp**:
```bash
# Chạy thủ công để test
php artisan trading:process-bets

# Kiểm tra database có bets pending không
php artisan tinker
>>> \App\Models\TradingBet::where('status', 'pending')->count();
```

### Lỗi: "WebSocket connection failed"

**Nguyên nhân**: Binance WebSocket bị block hoặc lỗi

**Giải pháp**: 
- Frontend sẽ tự động fallback về Laravel API
- Kiểm tra `/api/trading/price` có hoạt động không
- Đảm bảo `price:tick` command đang chạy

### Lỗi: "Schedule not running"

**Giải pháp**:
```bash
# Kiểm tra cron job
crontab -l

# Test schedule:run
php artisan schedule:run

# Xem list scheduled commands
php artisan schedule:list
```

## 📊 Monitoring

### Kiểm tra Commands đang chạy:

```bash
# Xem process scheduler
ps aux | grep "schedule:work"

# Xem process Laravel
ps aux | grep "php artisan"
```

### Kiểm tra Database:

```bash
# Xem số bets pending
php artisan tinker
>>> \App\Models\TradingBet::where('status', 'pending')->count();

# Xem bets gần đây
>>> \App\Models\TradingBet::latest()->take(10)->get();
```

## 🎯 Quick Start (Tất cả trong 1 lệnh)

```bash
# Terminal 1
php artisan serve & php artisan schedule:work

# Hoặc dùng composer script (nếu có)
composer dev
```

## 📝 Notes

- **Round duration**: 30 giây (có thể thay đổi trong code: `TR_TF = 30`)
- **Price update**: Mỗi 320ms (trong frontend engine)
- **Commands chạy**: Mỗi giây (từ Laravel scheduler)
- **WebSocket**: Tự động reconnect nếu disconnect
- **Admin settings**: Lưu vào database table `price_control`

---

**Chúc bạn chạy thành công! 🎉**
