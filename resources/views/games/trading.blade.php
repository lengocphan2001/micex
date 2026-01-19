@extends('layouts.mobile')

@section('title', 'Trading - Micex')

@push('styles')
<style>
    * {
        box-sizing: border-box;
    }

    /* Override layout background */
    body, html {
        background: #0b0b0b !important;
    }

    main {
        background: #0b0b0b !important;
    }

    .tr-container-wrapper {
        display: flex;
        height: calc(100vh - 64px - 80px); /* 64px header + 80px bottom nav */
        width: 100%;
        background: #0b0b0b;
    }

    #trChart {
        flex: 1;
        min-height: 400px;
        height: 100%;
        background: #0b0b0b ;
        position: relative;
    }

    /* Force chart canvas to have black background */


    /* Header styles are now handled by Tailwind classes */

    /* Chart styles are handled by flex layout */

    .tr-panel {
        width: 300px;
        background: #0f0f0f;
        border-left: 1px solid #222;
        padding: 12px;
        overflow-y: auto;
    }

    @media (max-width: 768px) {
        .tr-container-wrapper {
            flex-direction: column;
            height: auto;
        }
        
        #trChart {
            height: 400px;
        }
        
        .tr-panel {
            width: 100%;
            border-left: none;
            border-top: 1px solid #222;
        }
    }

    .tr-box {
        background: #161616;
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 6px;
    }

    .tr-label {
        font-size: 12px;
        color: #aaa;
        margin-bottom: 4px;
    }

    .tr-small {
        font-size: 11px;
        color: #777;
        text-align: right;
    }

    .tr-panel select,
    .tr-panel input[type="range"] {
        width: 100%;
        padding: 8px;
        background: #0b0b0b;
        border: 1px solid #333;
        border-radius: 4px;
        color: white;
        margin-bottom: 4px;
    }

    .tr-panel input[type="range"] {
        padding: 0;
    }

    .tr-timer {
        text-align: center;
        font-size: 22px;
        color: #00ff9c;
        font-weight: bold;
    }

    .tr-bet-btn {
        width: 100%;
        padding: 16px;
        margin-top: 8px;
        border-radius: 6px;
        border: none;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: opacity 0.2s;
    }

    .tr-bet-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .tr-bet-btn-call {
        background: #04b0e5;
        color: white;
    }

    .tr-bet-btn-put {
        background: #ff4d4d;
        color: white;
    }

    .tr-bet-amount {
        width: 100%;
        padding: 12px;
        background: #111;
        border: 1px solid #333;
        border-radius: 6px;
        color: white;
        font-size: 16px;
        margin-bottom: 8px;
    }

    .tr-bet-amount:focus {
        outline: none;
        border-color: #04b0e5;
    }

    .tr-wallet-select {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        padding: 8px;
        background: #111;
        border-radius: 6px;
    }

    .tr-wallet-select select {
        flex: 1;
        padding: 8px;
        background: #0b0b0b;
        border: 1px solid #333;
        border-radius: 4px;
        color: white;
        font-size: 14px;
    }

    .tr-balance {
        font-size: 12px;
        color: #aaa;
    }



    /* Force dark background for all elements */
    .tr-container * {
        color: #fff !important;
    }

    .tr-container input,
    .tr-container select,
    .tr-container button {
        color: #fff !important;
    }

    /* Override any white backgrounds from layout */
    .w-full.md\\:max-w-\\[450px\\] {
        background: #0b0b0b !important;
    }

    main {
        background: #0b0b0b !important;
        /* Keep padding-top: 64px from layout to avoid header overlap */
    }

    /* Override layout container */
    .w-full.md\\:max-w-\\[450px\\].h-full {
        background: #0b0b0b !important;
    }

    /* Fix any white text that should be visible */
    .tr-label,
    .tr-small,
    .tr-balance {
        color: #aaa !important;
    }

    /* Ensure buttons are visible */
    .tr-bet-btn-call,
    .tr-bet-btn-put {
        color: white !important;
    }

    /* Hide TradingView logo */
    #tv-attr-logo,
    a[title*="TradingView"],
    a[title*="Charting by TradingView"],
    a[data-dark] {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        width: 0 !important;
        height: 0 !important;
        position: absolute !important;
        left: -9999px !important;
    }
</style>
@endpush

@section('header')
    <header class="w-full px-4 py-4 flex items-center justify-between bg-gray-900 border-b border-gray-800">
        <a href="{{ route('games.index') }}" class="text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <h1 class="text-white text-base font-semibold">BTC / USDT</h1>
        <div class="w-6"></div>
    </header>
@endsection

@section('content')
<div class="tr-container-wrapper">
    <div id="trChart"></div>

    <div class="tr-panel">
        <div class="tr-box">
            <div class="tr-label">⏱ Countdown</div>
            <div class="tr-timer" id="trTimer">30</div>
        </div>

        <!-- Wallet Selection -->
        <div class="tr-wallet-select">
            <span class="tr-label">Ví:</span>
            <select id="trWalletSelect">
                <option value="deposit">Ví giao dịch</option>
                <option value="reward">Ví tiền thưởng</option>
            </select>
            <span class="tr-balance" id="trBalance">0.00 USDT</span>
        </div>

        <!-- Bet Amount -->
        <input type="number" id="trBetAmount" class="tr-bet-amount" value="10" min="0.01" step="0.01" placeholder="Số tiền cược">

        <!-- Bet Buttons -->
        <button id="trCallBtn" class="tr-bet-btn tr-bet-btn-call">⬆ CALL</button>
        <button id="trPutBtn" class="tr-bet-btn tr-bet-btn-put">⬇ PUT</button>

        @auth
            @if(auth()->user()->is_admin ?? false)
            <!-- Admin Panel -->
            <div class="tr-admin-panel">
                <div class="tr-box">
                    <div class="tr-label">🛠 ADMIN</div>

                    <div class="tr-label">Hướng ép</div>
                    <select id="trBiasDir">
                        <option value="0">Tự nhiên</option>
                        <option value="1">Ép lên</option>
                        <option value="-1">Ép xuống</option>
                    </select>

                    <div class="tr-label">Giây cuối</div>
                    <input id="trLastSeconds" type="range" min="1" max="10" value="10">
                    <div class="tr-small"><span id="trLastSecondsValue">10</span> giây</div>

                    <div class="tr-label">Độ lệch giá</div>
                    <input id="trBiasPower" type="range" min="0" max="50" value="10">
                    <div class="tr-small"><span id="trBiasPowerValue">10</span> giá</div>

                    <button id="trSaveAdmin" class="tr-bet-btn" style="background: #666; margin-top: 12px;">Lưu cài đặt</button>
                </div>
            </div>
            @endif
        @endauth
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/lightweight-charts@4.2.0/dist/lightweight-charts.standalone.production.js"></script>
<script>
    /* ================== CONFIG ================== */
    const TR_TF = 30; // 30 seconds per round
    const TR_SYMBOL = 'BTCUSDT';
    const TR_ADMIN = {
        followSpeed: 0.18,
        wickNoise: 4,
        biasDir: 0,
        lastSeconds: 10,
        biasPower: 10
    };

    /* ================== STATE ================== */
    let trPrice = null;
    let trBinance = null;
    let trCandle = null;
    let trStartTime = 0;
    let trCandleCloses = [];
    let trEma5 = null;
    let trEma10 = null;
    const trK5 = 2 / (5 + 1);
    const trK10 = 2 / (10 + 1);
    let trCandleVolume = 0;
    let trSmoothVolume = 0;
    let trVolumeColor = "rgba(0,255,156,0.35)";
    let trSelectedWallet = 'deposit';
    let trDepositBalance = 0;
    let trRewardBalance = 0;
    let trMyBet = null;
    let trHistoricalCandles = []; // Lưu lịch sử nến
    let trHistoricalVolumes = []; // Lưu lịch sử volume

    /* ================== CHART ================== */
    let trChart = null;
    let trCandles = null;
    let trMa5 = null;
    let trMa10 = null;
    let trVolumeSeries = null;
    
    function trInitChart() {
        const trChartContainer = document.getElementById("trChart");
        if (!trChartContainer || trChart) return;
        
        // Đảm bảo container có kích thước trước khi khởi tạo chart
        const rect = trChartContainer.getBoundingClientRect();
        if (rect.width === 0 || rect.height === 0) {
            // Nếu container chưa có kích thước, đợi một chút
            setTimeout(() => trInitChart(), 100);
            return;
        }
        
        trChart = LightweightCharts.createChart(
            trChartContainer,
            {
                layout: { 
                    backgroundColor: "#0b0b0b", 
                    textColor: "#ccc",
                    background: { type: 'solid', color: '#0b0b0b' }
                },
                grid: { 
                    vertLines: { color: "#222", style: 0 }, 
                    horzLines: { color: "#222", style: 0 } 
                },
                timeScale: { 
                    timeVisible: true, 
                    secondsVisible: true,
                    borderColor: "#333"
                },
                rightPriceScale: {
                    borderColor: "#333"
                },
                width: rect.width,
                height: rect.height
            }
        );

        trCandles = trChart.addCandlestickSeries({
            upColor: "#00ff9c",
            downColor: "#ff4d4d",
            wickUpColor: "#00ff9c",
            wickDownColor: "#ff4d4d",
            borderVisible: false
        });

        trMa5 = trChart.addLineSeries({ color: "#facc15", lineWidth: 1.3 });
        trMa10 = trChart.addLineSeries({ color: "#38bdf8", lineWidth: 1.3 });

        trVolumeSeries = trChart.addHistogramSeries({
            priceFormat: { type: "volume" },
            priceScaleId: "",
            scaleMargins: { top: 0.8, bottom: 0 }
        });
        
        // Handle resize
        window.addEventListener('resize', () => {
            if (trChart && trChartContainer) {
                const rect = trChartContainer.getBoundingClientRect();
                if (rect.width > 0 && rect.height > 0) {
                    trChart.applyOptions({ 
                        width: rect.width,
                        height: rect.height
                    });
                }
            }
        });
    }

    // Không giới hạn số nến như mẫu HTML


    // Remove TradingView logo if it appears
    function trRemoveTradingViewLogo() {
        const logos = document.querySelectorAll('#tv-attr-logo, a[title*="TradingView"], a[title*="Charting by TradingView"]');
        logos.forEach(logo => {
            logo.style.display = 'none';
            logo.remove();
        });
    }

    // Remove logo immediately and periodically
    trRemoveTradingViewLogo();
    setInterval(trRemoveTradingViewLogo, 1000);
    
    // Also remove when chart is created
    setTimeout(trRemoveTradingViewLogo, 500);

    /* ================== BINANCE WEBSOCKET ================== */
    let trWs = null;
    function trConnectBinance() {
        try {
            trWs = new WebSocket("wss://stream.binance.com:9443/ws/btcusdt@trade");
            trWs.onmessage = (e) => {
                try {
                    const data = JSON.parse(e.data);
                    trBinance = parseFloat(data.p);
                    if (trPrice === null) trPrice = trBinance;
                } catch (err) {
                    console.error('Error parsing Binance data:', err);
                }
            };
            trWs.onerror = (err) => {
                console.error('Binance WebSocket error:', err);
            };
            trWs.onclose = () => {
                console.log('Binance WebSocket closed, reconnecting...');
                setTimeout(trConnectBinance, 3000);
            };
        } catch (err) {
            console.error('Error connecting to Binance:', err);
            // Fallback to Laravel API
            trLoadPriceFromAPI();
        }
    }

    // Fallback: Load price from Laravel API
    async function trLoadPriceFromAPI() {
        try {
            const res = await fetch('{{ route("trading.price") }}?symbol=' + TR_SYMBOL);
            const data = await res.json();
            if (data.success) {
                trBinance = parseFloat(data.price);
                if (trPrice === null) trPrice = trBinance;
            }
        } catch (err) {
            console.error('Error loading price from API:', err);
        }
    }

    /* ================== LOAD HISTORICAL CANDLES ================== */
    async function trLoadHistoricalCandles() {
        try {
            // Tính toán thời gian: lấy 100 candles 1m gần nhất
            const now = Math.floor(Date.now() / 1000);
            const currentBucket = Math.floor(now / TR_TF) * TR_TF;
            const limit = 100;
            
            // Fetch từ Binance API (1m candles)
            const response = await fetch(`{{ route("trading.ohlc") }}?symbol=${TR_SYMBOL}&interval=1m&limit=${limit}`);
            const result = await response.json();
            
            if (result.success && result.data && result.data.length > 0) {
                const candles = result.data;
                const historicalCandles = [];
                const historicalVolumes = [];
                
                // Convert 1m candles thành 30s candles (mỗi 1m = 2 candles 30s)
                for (let i = 0; i < candles.length; i++) {
                    const candle = candles[i];
                    const candleTime = candle.time;
                    
                    // Chỉ lấy candles trước round hiện tại
                    if (candleTime >= currentBucket) continue;
                    
                    // Tạo 2 candles 30s từ 1 candle 1m
                    const midPrice = (parseFloat(candle.open) + parseFloat(candle.close)) / 2;
                    
                    const first30s = {
                        time: candleTime,
                        open: parseFloat(candle.open),
                        high: parseFloat(candle.high),
                        low: parseFloat(candle.low),
                        close: midPrice,
                    };
                    
                    const second30s = {
                        time: candleTime + 30,
                        open: midPrice,
                        high: parseFloat(candle.high),
                        low: parseFloat(candle.low),
                        close: parseFloat(candle.close),
                    };
                    
                    historicalCandles.push(first30s, second30s);
                    
                    // Volume chia đều
                    const halfVolume = parseFloat(candle.volume || 0) / 2;
                    historicalVolumes.push(
                        { 
                            time: candleTime, 
                            value: halfVolume, 
                            color: first30s.close >= first30s.open ? "rgba(0,255,156,0.35)" : "rgba(255,77,77,0.35)" 
                        },
                        { 
                            time: candleTime + 30, 
                            value: halfVolume, 
                            color: second30s.close >= second30s.open ? "rgba(0,255,156,0.35)" : "rgba(255,77,77,0.35)" 
                        }
                    );
                }
                
                if (historicalCandles.length > 0 && trCandles) {
                    // Set tất cả historical candles (không chỉ 5 nến)
                    trCandles.setData(historicalCandles);
                    if (historicalVolumes.length > 0 && trVolumeSeries) {
                        trVolumeSeries.setData(historicalVolumes);
                    }
                    
                    // Update candle closes từ tất cả historical candles
                    trCandleCloses = historicalCandles.map(c => c.close);
                    
                    // Tính EMA từ tất cả candles
                    if (trCandleCloses.length >= 5) {
                        trEma5 = trCandleCloses[0];
                        for (let i = 1; i < trCandleCloses.length; i++) {
                            trEma5 = trEma5 + (trCandleCloses[i] - trEma5) * trK5;
                        }
                        
                        // Update MA5 line cho tất cả candles
                        if (trMa5) {
                            const ma5Data = [];
                            let ema5Calc = trCandleCloses[0];
                            for (let i = 1; i < historicalCandles.length; i++) {
                                if (i >= 4) { // Bắt đầu từ candle thứ 5
                                    ema5Calc = ema5Calc + (trCandleCloses[i] - ema5Calc) * trK5;
                                    ma5Data.push({ time: historicalCandles[i].time, value: ema5Calc });
                                }
                            }
                            if (ma5Data.length > 0) trMa5.setData(ma5Data);
                        }
                    }
                    
                    // Tính EMA10 nếu có đủ 10 candles
                    if (trCandleCloses.length >= 10 && trMa10) {
                        trEma10 = trCandleCloses[0];
                        for (let i = 1; i < trCandleCloses.length; i++) {
                            trEma10 = trEma10 + (trCandleCloses[i] - trEma10) * trK10;
                        }
                        
                        const ma10Data = [];
                        let ema10Calc = trCandleCloses[0];
                        for (let i = 1; i < historicalCandles.length; i++) {
                            if (i >= 9) { // Bắt đầu từ candle thứ 10
                                ema10Calc = ema10Calc + (trCandleCloses[i] - ema10Calc) * trK10;
                                ma10Data.push({ time: historicalCandles[i].time, value: ema10Calc });
                            }
                        }
                        if (ma10Data.length > 0) trMa10.setData(ma10Data);
                    } else {
                        trEma10 = null;
                        if (trMa10) trMa10.setData([]);
                    }
                    
                    // Set start time và price từ candle cuối cùng
                    const lastCandle = historicalCandles[historicalCandles.length - 1];
                    trStartTime = lastCandle.time;
                    trPrice = lastCandle.close;
                    
                    // Khởi tạo candle cho round hiện tại nếu chưa có
                    const now = Math.floor(Date.now() / 1000);
                    const currentBucket = Math.floor(now / TR_TF) * TR_TF;
                    if (trStartTime < currentBucket) {
                        // Tạo candle mới cho round hiện tại
                        trStartTime = currentBucket;
                        trCandle = { 
                            time: currentBucket, 
                            open: trPrice, 
                            high: trPrice, 
                            low: trPrice, 
                            close: trPrice 
                        };
                        if (trCandles) trCandles.update(trCandle);
                    }
                    
                    console.log(`Loaded ${historicalCandles.length} historical candles`);
                }
            }
        } catch (err) {
            console.error('Error loading historical candles:', err);
        }
    }

    // Initialize chart when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            trInitChart();
            trConnectBinance();
            // Load historical candles sau khi chart đã được khởi tạo
            setTimeout(() => trLoadHistoricalCandles(), 200);
        });
    } else {
        trInitChart();
        trConnectBinance();
        // Load historical candles sau khi chart đã được khởi tạo
        setTimeout(() => trLoadHistoricalCandles(), 200);
    }

    /* ================== TIMER ================== */
    setInterval(() => {
        const now = Math.floor(Date.now() / 1000);
        const remain = TR_TF - (now % TR_TF) || TR_TF;
        
        // Update timer
        const timerEl = document.getElementById("trTimer");
        if (timerEl) timerEl.textContent = remain;

        // Disable buttons in last 5 seconds
        const callBtn = document.getElementById("trCallBtn");
        const putBtn = document.getElementById("trPutBtn");
        if (remain < 5 || trMyBet) {
            if (callBtn) callBtn.disabled = true;
            if (putBtn) putBtn.disabled = true;
        } else {
            if (callBtn) callBtn.disabled = false;
            if (putBtn) putBtn.disabled = false;
        }
    }, 100);

    /* ================== ENGINE ================== */
    setInterval(() => {
        if (!trBinance || trPrice === null) return;

        const now = Math.floor(Date.now() / 1000);
        const bucket = Math.floor(now / TR_TF) * TR_TF;
        const remain = TR_TF - (now - bucket);

        /* ===== ĐÓNG NẾN ===== */
        if (trStartTime !== bucket) {
            if (trCandle && trCandles) {
                // Add wick noise
                trCandle.high += Math.random() * TR_ADMIN.wickNoise;
                trCandle.low -= Math.random() * TR_ADMIN.wickNoise;
                trCandles.update(trCandle);

                // Update volume color
                trVolumeColor = trCandle.close >= trCandle.open
                    ? "rgba(0,255,156,0.35)"
                    : "rgba(255,77,77,0.35)";

                // Update EMA - giữ 50 nến như mẫu
                trCandleCloses.push(trCandle.close);
                if (trCandleCloses.length > 50) trCandleCloses.shift();

                // Update EMA5
                if (trCandleCloses.length >= 5 && trMa5) {
                    trEma5 = trEma5 === null ? trCandle.close : trEma5 + (trCandle.close - trEma5) * trK5;
                    trMa5.update({ time: trCandle.time, value: trEma5 });
                }
                
                // Update EMA10
                if (trCandleCloses.length >= 10 && trMa10) {
                    trEma10 = trEma10 === null ? trCandle.close : trEma10 + (trCandle.close - trEma10) * trK10;
                    trMa10.update({ time: trCandle.time, value: trEma10 });
                }
                
                // Lưu vào historical
                trHistoricalCandles.push(trCandle);
                if (trHistoricalCandles.length > 200) {
                    trHistoricalCandles.shift();
                }

                // Process bet result when round closes
                if (trMyBet && trMyBet.status === 'pending') {
                    // Round just closed, process bet result
                    trProcessBetResult(trCandle.close);
                }
            }

            trStartTime = bucket;
            trCandle = { time: bucket, open: trPrice, high: trPrice, low: trPrice, close: trPrice };
            if (trCandles) trCandles.update(trCandle);
            trCandleVolume = 0;
            trUpdateRoundNo(bucket);
            
            // Reset bet after processing (if any)
            if (trMyBet && trMyBet.status !== 'pending') {
                trMyBet = null;
            }
            return;
        }

        /* ===== FOLLOW BINANCE ===== */
        let move = (trBinance - trPrice) * TR_ADMIN.followSpeed;

        /* ===== ÉP TRONG GIÂY CUỐI ===== */
        if (remain <= TR_ADMIN.lastSeconds && TR_ADMIN.biasDir !== 0 && trCandle) {
            const target = trCandle.open + TR_ADMIN.biasDir * TR_ADMIN.biasPower;
            const dist = target - trPrice;
            const step = dist * 0.08; // 8% mỗi tick
            move = step; // ÉP GHI ĐÈ BINANCE
        }

        trPrice += move;

        // Đảm bảo trCandle tồn tại trước khi update
        if (!trCandle) {
            // Khởi tạo candle nếu chưa có
            trStartTime = bucket;
            trCandle = { time: bucket, open: trPrice, high: trPrice, low: trPrice, close: trPrice };
            if (trCandles) trCandles.update(trCandle);
            trCandleVolume = 0;
            return;
        }

        trCandle.close = trPrice;
        trCandle.high = Math.max(trCandle.high, trPrice);
        trCandle.low = Math.min(trCandle.low, trPrice);
        if (trCandles) trCandles.update(trCandle);

        // Update volume
        trCandleVolume += Math.abs(move) * 1.1 + Math.random() * 0.4;
        trSmoothVolume = trSmoothVolume * 0.88 + trCandleVolume * 0.12;

        if (trVolumeSeries) {
            trVolumeSeries.update({
                time: trCandle.time,
                value: Math.round(trSmoothVolume),
                color: trVolumeColor
            });
        }

        // Update current price display (if element exists)
        const priceEl = document.getElementById("trCurrentPrice");
        if (priceEl) priceEl.textContent = trPrice.toFixed(2);
    }, 320);

    /* ================== BETTING ================== */
    async function trLoadBalances() {
        try {
            const res = await fetch('{{ route("trading.my-bet") }}', {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return;
            const data = await res.json();
            trDepositBalance = Number(data.balance || 0);
            trRewardBalance = Number(data.reward_balance || 0);
            trUpdateBalanceDisplay();
        } catch (e) {
            console.error('Error loading balances:', e);
        }
    }

    function trUpdateBalanceDisplay() {
        const balance = trSelectedWallet === 'reward' ? trRewardBalance : trDepositBalance;
        document.getElementById("trBalance").textContent = balance.toFixed(2) + ' USDT';
    }

    async function trPlaceBet(direction) {
        const amount = parseFloat(document.getElementById("trBetAmount").value);
        if (!amount || amount <= 0) {
            alert('Vui lòng nhập số tiền cược hợp lệ');
            return;
        }

        try {
            const res = await fetch('{{ route("trading.bet") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    symbol: TR_SYMBOL,
                    direction: direction,
                    amount: amount,
                    wallet_type: trSelectedWallet
                })
            });

            const result = await res.json();

            if (result.success) {
                trMyBet = result.bet;
                trLoadBalances();
                alert('Đặt cược thành công!');
            } else {
                alert(result.error || 'Đặt cược thất bại');
            }
        } catch (error) {
            console.error('Error placing bet:', error);
            alert('Có lỗi xảy ra khi đặt cược');
        }
    }

    /**
     * Process bet result when round closes
     * Frontend calculates exit_price and calls API to process
     */
    async function trProcessBetResult(exitPrice) {
        if (!trMyBet || trMyBet.status !== 'pending') return;

        try {
            const res = await fetch('{{ route("trading.process-bet-result") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    bet_id: trMyBet.id,
                    exit_price: exitPrice
                })
            });

            const result = await res.json();

            if (result.success) {
                trMyBet = result.bet;
                
                if (result.is_win) {
                    alert(`Thắng! Lợi nhuận: +$${result.profit.toFixed(2)}`);
                } else {
                    alert(`Thua! Mất: -$${Math.abs(result.profit).toFixed(2)}`);
                }
                
                // Update balances
                trDepositBalance = result.balance || 0;
                trRewardBalance = result.reward_balance || 0;
                trUpdateBalanceDisplay();
                
                // Reset bet after showing result
                setTimeout(() => {
                    trMyBet = null;
                }, 1000);
            } else {
                console.error('Error processing bet result:', result.error);
            }
        } catch (error) {
            console.error('Error processing bet result:', error);
        }
    }

    function trUpdateRoundNo(bucket) {
        // Round number update if needed
    }

    /* ================== ADMIN ================== */
    async function trLoadAdminSettings() {
        try {
            const res = await fetch('/api/trading/admin-settings?symbol=' + TR_SYMBOL);
            const data = await res.json();
            if (data.success) {
                TR_ADMIN.biasDir = data.bias_dir || 0;
                TR_ADMIN.lastSeconds = data.last_seconds || 10;
                TR_ADMIN.biasPower = data.bias_power || 10;
                document.getElementById("trBiasDir").value = TR_ADMIN.biasDir;
                document.getElementById("trLastSeconds").value = TR_ADMIN.lastSeconds;
                document.getElementById("trBiasPower").value = TR_ADMIN.biasPower;
                document.getElementById("trLastSecondsValue").textContent = TR_ADMIN.lastSeconds;
                document.getElementById("trBiasPowerValue").textContent = TR_ADMIN.biasPower;
            }
        } catch (e) {
            console.error('Error loading admin settings:', e);
        }
    }

    async function trSaveAdminSettings() {
        try {
            const res = await fetch('/api/trading/admin-settings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    symbol: TR_SYMBOL,
                    bias_dir: TR_ADMIN.biasDir,
                    last_seconds: TR_ADMIN.lastSeconds,
                    bias_power: TR_ADMIN.biasPower
                })
            });
            const result = await res.json();
            if (result.success) {
                alert('Đã lưu cài đặt admin!');
            } else {
                alert('Lỗi khi lưu cài đặt');
            }
        } catch (error) {
            console.error('Error saving admin settings:', error);
            alert('Có lỗi xảy ra');
        }
    }

    /* ================== EVENT LISTENERS ================== */
    document.getElementById("trCallBtn").addEventListener('click', () => trPlaceBet('up'));
    document.getElementById("trPutBtn").addEventListener('click', () => trPlaceBet('down'));
    document.getElementById("trWalletSelect").addEventListener('change', (e) => {
        trSelectedWallet = e.target.value;
        trUpdateBalanceDisplay();
    });

    // Admin controls
    const adminPanel = document.querySelector('.tr-admin-panel');
    if (adminPanel) {
        document.getElementById("trBiasDir").addEventListener('change', (e) => {
            TR_ADMIN.biasDir = +e.target.value;
        });
        document.getElementById("trLastSeconds").addEventListener('input', (e) => {
            TR_ADMIN.lastSeconds = +e.target.value;
            document.getElementById("trLastSecondsValue").textContent = e.target.value;
        });
        document.getElementById("trBiasPower").addEventListener('input', (e) => {
            TR_ADMIN.biasPower = +e.target.value;
            document.getElementById("trBiasPowerValue").textContent = e.target.value;
        });
        document.getElementById("trSaveAdmin").addEventListener('click', trSaveAdminSettings);
        trLoadAdminSettings();
    }

    /* ================== INIT ================== */
    trLoadBalances();
    setInterval(trLoadBalances, 10000); // Update balance every 10 seconds
</script>
@endpush
