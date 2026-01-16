#!/bin/bash

echo "🔍 Kiểm tra Trading Realtime System..."
echo ""

# Check if schedule is running
echo "1️⃣ Kiểm tra Schedule (php artisan schedule:work):"
if pgrep -f "schedule:work" > /dev/null; then
    echo "   ✅ Schedule đang chạy"
else
    echo "   ❌ Schedule KHÔNG chạy!"
    echo "   👉 Chạy: php artisan schedule:work"
fi

# Check if Reverb is running
echo ""
echo "2️⃣ Kiểm tra Reverb Server (php artisan reverb:start):"
if pgrep -f "reverb:start" > /dev/null; then
    echo "   ✅ Reverb đang chạy"
else
    echo "   ❌ Reverb KHÔNG chạy!"
    echo "   👉 Chạy: php artisan reverb:start"
fi

# Test price tick command
echo ""
echo "3️⃣ Test Price Tick Command:"
php artisan price:tick
if [ $? -eq 0 ]; then
    echo "   ✅ Price tick command hoạt động"
else
    echo "   ❌ Price tick command LỖI!"
fi

# Test candle process command
echo ""
echo "4️⃣ Test Candle Process Command:"
php artisan candle:process --timeframe=1m
if [ $? -eq 0 ]; then
    echo "   ✅ Candle process command hoạt động"
else
    echo "   ❌ Candle process command LỖI!"
fi

# Check cache for price
echo ""
echo "5️⃣ Kiểm tra Cache (BTCUSDT_PRICE):"
PRICE=$(php artisan tinker --execute="echo Cache::get('BTCUSDT_PRICE', 'NOT_FOUND');")
if [ "$PRICE" != "NOT_FOUND" ]; then
    echo "   ✅ Giá trong cache: $PRICE"
else
    echo "   ⚠️  Giá chưa có trong cache (cần chạy price:tick)"
fi

# Check cache for candle
echo ""
echo "6️⃣ Kiểm tra Cache (BTCUSDT_1m_CANDLE):"
CANDLE=$(php artisan tinker --execute="echo json_encode(Cache::get('BTCUSDT_1m_CANDLE', null));")
if [ "$CANDLE" != "null" ]; then
    echo "   ✅ Nến trong cache: $CANDLE"
else
    echo "   ⚠️  Nến chưa có trong cache (cần chạy candle:process)"
fi

echo ""
echo "📋 Hướng dẫn:"
echo "   1. Mở terminal 1: php artisan schedule:work"
echo "   2. Mở terminal 2: php artisan reverb:start"
echo "   3. Mở browser và vào /games/trading"
echo "   4. Mở Console (F12) để xem debug logs"
echo ""
