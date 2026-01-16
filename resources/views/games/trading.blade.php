@extends('layouts.mobile')

@section('title', 'Trading - Micex')

@push('styles')
<style>
    #chart {
        width: 100%;
        height: 500px;
        background: #0f172a;
    }
    
    .price-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px;
        background: #1f2937;
        border-radius: 8px;
        margin-bottom: 16px;
    }
    
    .price-label {
        color: #9ca3af;
        font-size: 14px;
    }
    
    .price-value {
        color: #22c55e;
        font-size: 18px;
        font-weight: 600;
    }
    
    .price-change {
        font-size: 14px;
        font-weight: 500;
    }
    
    .price-change.positive {
        color: #22c55e;
    }
    
    .price-change.negative {
        color: #ef4444;
    }
    
    .loading {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 500px;
        color: #9ca3af;
    }
    
    /* Betting Panel */
    .betting-panel {
        background: #1f2937;
        border-radius: 12px;
        padding: 16px;
        margin-top: 16px;
    }
    
    .timer-display {
        text-align: center;
        padding: 12px;
        background: #111827;
        border-radius: 8px;
        margin-bottom: 16px;
    }
    
    .timer-label {
        color: #9ca3af;
        font-size: 12px;
        margin-bottom: 4px;
    }
    
    .timer-value {
        color: #22c55e;
        font-size: 24px;
        font-weight: 700;
    }
    
    .bet-amount-input {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 16px;
    }
    
    .bet-amount-input input {
        flex: 1;
        background: #111827;
        border: 1px solid #374151;
        border-radius: 8px;
        padding: 12px;
        color: white;
        font-size: 16px;
    }
    
    .bet-amount-input input:focus {
        outline: none;
        border-color: #3b82f6;
    }
    
    .bet-buttons {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        gap: 8px;
        margin-bottom: 16px;
    }
    
    .bet-btn {
        padding: 16px;
        border-radius: 12px;
        font-size: 18px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .bet-btn-up {
        background: #22c55e;
        color: white;
    }
    
    .bet-btn-up:hover:not(:disabled) {
        background: #16a34a;
    }
    
    .bet-btn-down {
        background: #ef4444;
        color: white;
    }
    
    .bet-btn-down:hover:not(:disabled) {
        background: #dc2626;
    }
    
    .bet-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .profit-display {
        text-align: center;
        padding: 12px;
        background: #111827;
        border-radius: 8px;
    }
    
    .profit-label {
        color: #9ca3af;
        font-size: 12px;
        margin-bottom: 4px;
    }
    
    .profit-value {
        color: #22c55e;
        font-size: 20px;
        font-weight: 700;
    }
    
    .bet-ratio-bar {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
    }
    
    .bet-ratio-progress {
        flex: 1;
        height: 8px;
        background: #111827;
        border-radius: 4px;
        overflow: hidden;
        display: flex;
    }
    
    .bet-ratio-up {
        background: #22c55e;
        transition: width 0.3s;
    }
    
    .bet-ratio-down {
        background: #ef4444;
        transition: width 0.3s;
    }
    
    .bet-ratio-text {
        font-size: 12px;
        color: #9ca3af;
        min-width: 40px;
    }
    
    /* Indicators Panel */
    .indicators-panel {
        background: #1f2937;
        border-radius: 12px;
        padding: 16px;
        margin-top: 16px;
    }
    
    .indicator-item {
        margin-bottom: 16px;
    }
    
    .indicator-title {
        color: #9ca3af;
        font-size: 12px;
        margin-bottom: 8px;
    }
    
    .indicator-value {
        color: white;
        font-size: 16px;
        font-weight: 600;
    }
    
    .indicator-value.buy {
        color: #22c55e;
    }
    
    .indicator-value.sell {
        color: #ef4444;
    }
    
    .indicator-value.neutral {
        color: #9ca3af;
    }
</style>
@endpush

@section('header')
<header class="w-full px-4 py-4 flex items-center justify-between bg-gray-900 border-b border-gray-800">
    <button onclick="history.back()" class="text-white">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
    </button>
    <h1 class="text-white text-base font-semibold">Trading</h1>
    <div class="w-6"></div>
</header>
@endsection

@section('content')
<div class="px-4 py-6">
    <!-- Debug info (remove in production) -->
    <div id="debug-info" style="display: none; padding: 8px; background: #1f2937; border-radius: 4px; margin-bottom: 8px; font-size: 12px; color: #9ca3af;">
        <div>Symbol: <span id="debug-symbol">--</span></div>
        <div>Interval: <span id="debug-interval">--</span></div>
        <div>Candles loaded: <span id="debug-candles">--</span></div>
    </div>
    
    <!-- Chart Container -->
    <div id="chart"></div>
    
    <div class="loading hidden" id="loading-indicator">
        <div>Đang tải dữ liệu...</div>
    </div>
    
    <!-- Betting Panel -->
    <div class="betting-panel">
        <!-- Timer -->
        <div class="timer-display">
            <div class="timer-label">Thời gian còn lại</div>
            <div class="timer-value" id="timer-value">60s</div>
        </div>
        
        <!-- Bet Ratio Bar -->
        <div class="bet-ratio-bar">
            <span class="bet-ratio-text" id="ratio-down">0%</span>
            <div class="bet-ratio-progress">
                <div class="bet-ratio-down" id="ratio-down-bar" style="width: 50%;"></div>
                <div class="bet-ratio-up" id="ratio-up-bar" style="width: 50%;"></div>
            </div>
            <span class="bet-ratio-text" id="ratio-up">0%</span>
        </div>
        
        <!-- Bet Amount Input -->
        <div class="bet-amount-input">
            <span style="color: #9ca3af;">$</span>
            <input type="number" id="bet-amount" value="10" min="1" step="0.01" placeholder="Nhập số tiền">
        </div>
        
        <!-- Profit Display -->
        <div class="profit-display">
            <div class="profit-label">Lợi nhuận</div>
            <div class="profit-value" id="profit-display">95% +$19.5</div>
        </div>
        
        <!-- Bet Buttons -->
        <div class="bet-buttons">
            <button class="bet-btn bet-btn-down" id="bet-down-btn" onclick="placeBet('down')">
                <span>Giảm</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 9l6 6 6-6"/>
                </svg>
            </button>
            <div style="display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 14px; padding: 0 8px;">
                <span id="timer-seconds">60</span>s
            </div>
            <button class="bet-btn bet-btn-up" id="bet-up-btn" onclick="placeBet('up')">
                <span>Tăng</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 15l6-6 6 6"/>
                </svg>
            </button>
        </div>
    </div>
    
    <!-- Indicators Panel -->
    <div class="indicators-panel">
        <div class="indicator-item">
            <div class="indicator-title">Oscillators</div>
            <div class="indicator-value neutral" id="oscillators-value">Neutral</div>
        </div>
        <div class="indicator-item">
            <div class="indicator-title">Summary</div>
            <div class="indicator-value buy" id="summary-value">Buy</div>
        </div>
        <div class="indicator-item">
            <div class="indicator-title">Moving Averages</div>
            <div class="indicator-value buy" id="ma-value">Strong Buy</div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/lightweight-charts@4.1.3/dist/lightweight-charts.standalone.production.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
<script>
    // Initialize Laravel Echo for WebSocket (using Reverb)
    window.Pusher = Pusher;
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: '{{ env("REVERB_APP_KEY", "your-app-key") }}',
        wsHost: window.location.hostname,
        wsPort: {{ env("REVERB_PORT", 8080) }},
        wssPort: {{ env("REVERB_PORT", 8080) }},
        forceTLS: false,
        enabledTransports: ['ws', 'wss'],
    });

    let chart = null;
    let candleSeries = null;
    let ma9Series = null;
    let ma21Series = null;
    let volumeSeries = null;
    let priceLineSeries = null; // Price line indicator
    let currentSymbol = 'BTCUSDT';
    let currentInterval = '1m';
    let priceUpdateInterval = null;
    let lastCandleData = [];
    let candleMap = new Map(); // Store candles by time for quick lookup
    let priceListener = null;
    let candleListener = null;
    let currentPrice = null;
    
    // Betting variables
    let timerInterval = null;
    let currentRoundTime = null;
    let payoutRate = 1.95; // 95% profit
    let myBet = null;
    
    // Calculate Moving Average
    function calculateMA(data, period) {
        const result = [];
        for (let i = 0; i < data.length; i++) {
            if (i < period - 1) {
                result.push({ time: data[i].time, value: null });
            } else {
                let sum = 0;
                for (let j = 0; j < period; j++) {
                    sum += data[i - j].close;
                }
                result.push({ time: data[i].time, value: sum / period });
            }
        }
        return result;
    }
    
    // Update MA when new candle data arrives
    function updateMA() {
        if (!lastCandleData || lastCandleData.length === 0) return;
        
        const ma9Data = calculateMA(lastCandleData, 9);
        const ma21Data = calculateMA(lastCandleData, 21);
        
        if (ma9Series) ma9Series.setData(ma9Data);
        if (ma21Series) ma21Series.setData(ma21Data);
    }
    
    // Connect to Laravel WebSocket for realtime updates
    function connectWebSocket(symbol, interval) {
        console.log('🔌 Connecting to WebSocket for', symbol, interval);
        
        // Disconnect previous listeners
        disconnectWebSocket();
        
        // Check WebSocket connection
        window.Echo.connector.pusher.connection.bind('connected', () => {
            console.log('✅ WebSocket connected!');
        });
        
        window.Echo.connector.pusher.connection.bind('disconnected', () => {
            console.error('❌ WebSocket disconnected!');
        });
        
        window.Echo.connector.pusher.connection.bind('error', (error) => {
            console.error('❌ WebSocket connection error:', error);
        });
        
        // Listen to price updates (mỗi giây)
        priceListener = window.Echo.channel('price-updates')
            .listen('.price.updated', (data) => {
                console.log('📈 Price update received:', data);
                if (data.symbol === symbol) {
                    currentPrice = parseFloat(data.price);
                    
                    // Update current price display
                    const priceEl = document.getElementById('current-price');
                    const priceOverlayEl = document.getElementById('current-price-overlay');
                    if (priceEl) {
                        priceEl.textContent = currentPrice.toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    }
                    if (priceOverlayEl) {
                        priceOverlayEl.textContent = currentPrice.toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    }
                    
                    // Update price line on chart (realtime - giá nhảy mỗi giây)
                    if (priceLineSeries && currentPrice) {
                        const now = Math.floor(Date.now() / 1000);
                        priceLineSeries.update({
                            time: now,
                            value: currentPrice,
                        });
                    }
                }
            })
            .error((error) => {
                console.error('❌ Price WebSocket error:', error);
            });
        
        // Listen to candle updates
        candleListener = window.Echo.channel('candle-updates')
            .listen('.candle.updated', (data) => {
                console.log('🕯️ Candle update received:', data);
                if (data.symbol === symbol && data.timeframe === interval) {
                    const candle = data.candle;
                    
                    // Convert to chart format
                    const chartCandle = {
                        time: candle.time,
                        open: parseFloat(candle.open),
                        high: parseFloat(candle.high),
                        low: parseFloat(candle.low),
                        close: parseFloat(candle.close),
                    };
                    
                    console.log('📊 Updating candle on chart:', chartCandle);
                    
                    // Update or append candle
                    if (candleSeries) {
                        candleSeries.update(chartCandle);
                    }
                    
                    // Update candle map
                    candleMap.set(candle.time, chartCandle);
                    
                    // Update lastCandleData array
                    const index = lastCandleData.findIndex(c => c.time === candle.time);
                    if (index >= 0) {
                        lastCandleData[index] = chartCandle;
                    } else {
                        lastCandleData.push(chartCandle);
                        // Keep only last 500 candles
                        if (lastCandleData.length > 500) {
                            lastCandleData.shift();
                        }
                    }
                    
                    // Sort by time
                    lastCandleData.sort((a, b) => a.time - b.time);
                    
                    // Update MA
                    updateMA();
                    
                    // Update indicators
                    updateIndicators();
                    
                    // Update volume if exists (màu theo candle)
                    if (volumeSeries && candle.volume) {
                        const volume = {
                            time: candle.time,
                            value: parseFloat(candle.volume),
                            color: candle.close >= candle.open ? '#31BAA0' : '#FC5F5F',
                        };
                        volumeSeries.update(volume);
                    }
                }
            })
            .error((error) => {
                console.error('❌ Candle WebSocket error:', error);
            });
    }
    
    // Disconnect WebSocket
    function disconnectWebSocket() {
        if (priceListener) {
            window.Echo.leave('price-updates');
            priceListener = null;
        }
        if (candleListener) {
            window.Echo.leave('candle-updates');
            candleListener = null;
        }
    }
    
    // Initialize chart
    function initChart() {
        const chartContainer = document.getElementById('chart');
        if (!chartContainer) return;
        
        chart = LightweightCharts.createChart(chartContainer, {
            layout: {
                background: { color: '#0f172a' },
                textColor: '#d1d5db',
            },
            grid: {
                vertLines: { color: '#1f2937' },
                horzLines: { color: '#1f2937' },
            },
            width: chartContainer.clientWidth,
            height: 500,
            timeScale: { 
                timeVisible: true,
                secondsVisible: false,
            },
        });
        
        // Candlestick series (màu giống ảnh: teal cho bullish, salmon cho bearish)
        candleSeries = chart.addCandlestickSeries({
            upColor: '#31BAA0', // Teal/green như trong ảnh
            downColor: '#FC5F5F', // Salmon/coral red như trong ảnh
            borderVisible: false,
            wickUpColor: '#31BAA0',
            wickDownColor: '#FC5F5F',
        });
        
        // Moving Average 9 (magenta/pink như trong ảnh)
        ma9Series = chart.addLineSeries({
            color: '#c70e65', // Magenta/pink như trong ảnh
            lineWidth: 2,
            priceLineVisible: false,
            lastValueVisible: false,
        });
        
        // Moving Average 21 (cyan/light blue như trong ảnh)
        ma21Series = chart.addLineSeries({
            color: '#1cb2b3', // Cyan/light blue như trong ảnh
            lineWidth: 2,
            priceLineVisible: false,
            lastValueVisible: false,
        });
        
        // Volume series
        volumeSeries = chart.addHistogramSeries({
            color: '#26a69a',
            priceFormat: {
                type: 'volume',
            },
            priceScaleId: '',
            scaleMargins: {
                top: 0.8,
                bottom: 0,
            },
        });
        
        // Price line indicator (current price)
        priceLineSeries = chart.addLineSeries({
            color: '#ffffff',
            lineWidth: 1,
            lineStyle: 2, // Dashed line
            priceLineVisible: false,
            lastValueVisible: true,
            crosshairMarkerVisible: true,
            crosshairMarkerRadius: 5,
        });
        
        // Handle resize
        const resizeObserver = new ResizeObserver(entries => {
            if (entries.length > 0) {
                const { width, height } = entries[0].contentRect;
                chart.applyOptions({ width, height });
            }
        });
        resizeObserver.observe(chartContainer);
    }
    
    // Load OHLC data
    async function loadChartData(symbol, interval) {
        const loadingEl = document.getElementById('loading-indicator');
        const chartEl = document.getElementById('chart');
        
        try {
            loadingEl.classList.remove('hidden');
            chartEl.style.display = 'none';
            
            // Load from Laravel API (our own candles, not Binance)
            const response = await fetch(`/api/trading/candles?symbol=${symbol}&timeframe=${interval}&limit=500`);
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.error || 'Failed to load chart data');
            }
            
            const data = result.data || [];
            
            // Debug info
            const debugEl = document.getElementById('debug-info');
            if (debugEl) {
                document.getElementById('debug-symbol').textContent = symbol;
                document.getElementById('debug-interval').textContent = interval;
                document.getElementById('debug-candles').textContent = data.length;
            }
            
            // Check if we have data
            if (!data || data.length === 0) {
                console.warn('No candle data available, using fallback');
                // Try fallback to Binance
                try {
                    const binanceResponse = await fetch(`/api/trading/ohlc?symbol=${symbol}&interval=${interval}&limit=200`);
                    const binanceResult = await binanceResponse.json();
                    if (binanceResult.success && binanceResult.data && binanceResult.data.length > 0) {
                        data = binanceResult.data;
                        console.log('Using Binance fallback data:', data.length, 'candles');
                    } else {
                        loadingEl.innerHTML = '<div style="color: #9ca3af;">Đang chờ dữ liệu... Vui lòng đợi vài giây để hệ thống tạo nến.</div>';
                        return;
                    }
                } catch (e) {
                    console.error('Fallback failed:', e);
                    loadingEl.innerHTML = '<div style="color: #ef4444;">Không thể tải dữ liệu. Vui lòng thử lại sau.</div>';
                    return;
                }
            }
            
            // Prepare candlestick data
            const candleData = data.map(item => ({
                time: item.time,
                open: parseFloat(item.open),
                high: parseFloat(item.high),
                low: parseFloat(item.low),
                close: parseFloat(item.close),
            }));
            
            // Prepare volume data (màu theo candle - teal/salmon như ảnh)
            const volumeData = data.map(item => ({
                time: item.time,
                value: parseFloat(item.volume || 0),
                color: parseFloat(item.close) >= parseFloat(item.open) ? '#31BAA0' : '#FC5F5F',
            }));
            
            // Store candle data for MA calculation and WebSocket updates
            lastCandleData = candleData;
            
            // Calculate and set Moving Averages
            const ma9Data = calculateMA(candleData, 9);
            const ma21Data = calculateMA(candleData, 21);
            
            // Update chart
            if (candleSeries) {
                candleSeries.setData(candleData);
            }
            if (ma9Series) {
                ma9Series.setData(ma9Data);
            }
            if (ma21Series) {
                ma21Series.setData(ma21Data);
            }
            if (volumeSeries && volumeData.length > 0) {
                volumeSeries.setData(volumeData);
            }
            
            chart.timeScale().fitContent();
            
            loadingEl.classList.add('hidden');
            chartEl.style.display = 'block';
            
            // Update indicators
            updateIndicators();
            
            // Initialize round time
            currentRoundTime = getCurrentRoundTime();
            
            // Connect WebSocket for realtime updates
            connectWebSocket(symbol, interval);
            
        } catch (error) {
            console.error('Error loading chart data:', error);
            loadingEl.innerHTML = '<div style="color: #ef4444;">Lỗi khi tải dữ liệu</div>';
        }
    }
    
    // Load current price
    async function loadCurrentPrice(symbol) {
        try {
            const response = await fetch(`/api/trading/price?symbol=${symbol}`);
            const result = await response.json();
            
            if (result.success) {
                const priceEl = document.getElementById('current-price');
                if (priceEl) {
                    priceEl.textContent = parseFloat(result.price).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            }
        } catch (error) {
            console.error('Error loading price:', error);
        }
    }
    
    // No symbol/interval selectors - fixed to BTCUSDT and 1m
    
    // Calculate current round time (based on 1m candles = 60 seconds)
    function getCurrentRoundTime() {
        const now = Math.floor(Date.now() / 1000);
        const roundDuration = 60; // 60 seconds per round (1 minute)
        return Math.floor(now / roundDuration) * roundDuration;
    }
    
    // Calculate time until next round (timer countdown - giống file HTML)
    function getTimeUntilNextRound() {
        const now = Math.floor(Date.now() / 1000);
        const roundTime = getCurrentRoundTime();
        const remain = 60 - (now - roundTime);
        return remain || 60; // Return remaining seconds or 60 if exactly at start
    }
    
    // Start timer (giống file HTML - countdown từ 60 về 0, update mỗi 100ms)
    function startTimer() {
        if (timerInterval) {
            clearInterval(timerInterval);
        }
        
        timerInterval = setInterval(() => {
            const remain = getTimeUntilNextRound();
            const timerEl = document.getElementById('timer-value');
            const timerSecondsEl = document.getElementById('timer-seconds');
            
            if (timerEl) {
                timerEl.textContent = remain + 's';
            }
            if (timerSecondsEl) {
                timerSecondsEl.textContent = remain;
            }
            
            // Disable bet buttons when < 5 seconds
            const betUpBtn = document.getElementById('bet-up-btn');
            const betDownBtn = document.getElementById('bet-down-btn');
            
            if (remain < 5) {
                if (betUpBtn) betUpBtn.disabled = true;
                if (betDownBtn) betDownBtn.disabled = true;
            } else {
                if (betUpBtn && !myBet) betUpBtn.disabled = false;
                if (betDownBtn && !myBet) betDownBtn.disabled = false;
            }
            
            // Check if round changed
            const newRoundTime = getCurrentRoundTime();
            if (currentRoundTime && newRoundTime !== currentRoundTime) {
                currentRoundTime = newRoundTime;
                myBet = null; // Reset bet for new round
                checkBetResult(); // Check previous round result
                updateBetRatio(); // Update bet ratio for new round
            }
        }, 100); // Update every 100ms for smooth countdown (giống file HTML)
    }
    
    // Place bet
    async function placeBet(direction) {
        const amountInput = document.getElementById('bet-amount');
        const amount = parseFloat(amountInput.value);
        
        if (!amount || amount <= 0) {
            alert('Vui lòng nhập số tiền cược hợp lệ');
            return;
        }
        
        try {
            const response = await window.csrfFetch('/api/trading/bet', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    symbol: currentSymbol,
                    direction: direction,
                    amount: amount,
                }),
            });
            
            const result = await response.json();
            
            if (result.success) {
                myBet = result.bet;
                updateProfitDisplay();
                
                // Disable bet buttons
                document.getElementById('bet-up-btn').disabled = true;
                document.getElementById('bet-down-btn').disabled = true;
                
                // Show success message
                if (typeof showToast === 'function') {
                    showToast('Đặt cược thành công!', 'success');
                }
            } else {
                alert(result.error || 'Đặt cược thất bại');
            }
        } catch (error) {
            console.error('Error placing bet:', error);
            alert('Có lỗi xảy ra khi đặt cược');
        }
    }
    
    // Check bet result
    async function checkBetResult() {
        if (!myBet) return;
        
        try {
            const response = await fetch(`/api/trading/bet-result?bet_id=${myBet.id}`);
            const result = await response.json();
            
            if (result.success && result.bet.status !== 'pending') {
                // Bet resolved
                if (result.bet.status === 'won') {
                    if (typeof showToast === 'function') {
                        showToast(`Thắng! Lợi nhuận: +$${result.bet.profit}`, 'success');
                    }
                } else {
                    if (typeof showToast === 'function') {
                        showToast(`Thua! Mất: -$${Math.abs(result.bet.profit)}`, 'error');
                    }
                }
                
                myBet = null;
                
                // Re-enable bet buttons
                document.getElementById('bet-up-btn').disabled = false;
                document.getElementById('bet-down-btn').disabled = false;
                
                // Reload balance
                if (typeof loadCurrentPrice === 'function') {
                    loadCurrentPrice(currentSymbol);
                }
            }
        } catch (error) {
            console.error('Error checking bet result:', error);
        }
    }
    
    // Update profit display
    function updateProfitDisplay() {
        if (!myBet) {
            const profitEl = document.getElementById('profit-display');
            if (profitEl) {
                const amount = parseFloat(document.getElementById('bet-amount').value) || 10;
                const profitPercent = ((payoutRate - 1) * 100).toFixed(0);
                const profitAmount = (amount * (payoutRate - 1)).toFixed(2);
                profitEl.textContent = `${profitPercent}% +$${profitAmount}`;
            }
            return;
        }
        
        const profitEl = document.getElementById('profit-display');
        if (profitEl && myBet.status === 'pending') {
            const profitPercent = ((myBet.payout_rate - 1) * 100).toFixed(0);
            const profitAmount = (myBet.amount * (myBet.payout_rate - 1)).toFixed(2);
            profitEl.textContent = `${profitPercent}% +$${profitAmount}`;
        }
    }
    
    // Update bet ratio bar
    async function updateBetRatio() {
        try {
            const response = await fetch(`/api/trading/bet-ratio?symbol=${currentSymbol}&round_time=${getCurrentRoundTime()}`);
            const result = await response.json();
            
            if (result.success) {
                const ratioUp = result.ratio_up || 50;
                const ratioDown = result.ratio_down || 50;
                
                document.getElementById('ratio-up').textContent = ratioUp + '%';
                document.getElementById('ratio-down').textContent = ratioDown + '%';
                document.getElementById('ratio-up-bar').style.width = ratioUp + '%';
                document.getElementById('ratio-down-bar').style.width = ratioDown + '%';
            }
        } catch (error) {
            console.error('Error updating bet ratio:', error);
        }
    }
    
    // Calculate and update indicators
    function updateIndicators() {
        if (!lastCandleData || lastCandleData.length < 21) return;
        
        const recentCandles = lastCandleData.slice(-21);
        
        // Calculate RSI (simplified)
        let gains = 0;
        let losses = 0;
        for (let i = 1; i < recentCandles.length; i++) {
            const change = recentCandles[i].close - recentCandles[i-1].close;
            if (change > 0) gains += change;
            else losses += Math.abs(change);
        }
        
        const avgGain = gains / 14;
        const avgLoss = losses / 14;
        const rs = avgLoss === 0 ? 100 : avgGain / avgLoss;
        const rsi = 100 - (100 / (1 + rs));
        
        // Update Oscillators
        const oscillatorsEl = document.getElementById('oscillators-value');
        if (oscillatorsEl) {
            if (rsi > 70) {
                oscillatorsEl.textContent = 'Sell';
                oscillatorsEl.className = 'indicator-value sell';
            } else if (rsi < 30) {
                oscillatorsEl.textContent = 'Buy';
                oscillatorsEl.className = 'indicator-value buy';
            } else {
                oscillatorsEl.textContent = 'Neutral';
                oscillatorsEl.className = 'indicator-value neutral';
            }
        }
        
        // Update Summary (based on MA crossover)
        if (ma9Series && ma21Series && recentCandles.length >= 21) {
            const ma9Data = calculateMA(recentCandles, 9);
            const ma21Data = calculateMA(recentCandles, 21);
            
            const lastMa9 = ma9Data[ma9Data.length - 1];
            const lastMa21 = ma21Data[ma21Data.length - 1];
            
            const summaryEl = document.getElementById('summary-value');
            if (summaryEl && lastMa9.value && lastMa21.value) {
                if (lastMa9.value > lastMa21.value) {
                    summaryEl.textContent = 'Buy';
                    summaryEl.className = 'indicator-value buy';
                } else {
                    summaryEl.textContent = 'Sell';
                    summaryEl.className = 'indicator-value sell';
                }
            }
        }
        
        // Update Moving Averages
        const maEl = document.getElementById('ma-value');
        if (maEl && recentCandles.length >= 21) {
            const currentPrice = recentCandles[recentCandles.length - 1].close;
            const ma9Data = calculateMA(recentCandles, 9);
            const ma21Data = calculateMA(recentCandles, 21);
            
            const lastMa9 = ma9Data[ma9Data.length - 1];
            const lastMa21 = ma21Data[ma21Data.length - 1];
            
            if (lastMa9.value && lastMa21.value) {
                if (currentPrice > lastMa9.value && lastMa9.value > lastMa21.value) {
                    maEl.textContent = 'Strong Buy';
                    maEl.className = 'indicator-value buy';
                } else if (currentPrice < lastMa9.value && lastMa9.value < lastMa21.value) {
                    maEl.textContent = 'Strong Sell';
                    maEl.className = 'indicator-value sell';
                } else if (currentPrice > lastMa9.value) {
                    maEl.textContent = 'Buy';
                    maEl.className = 'indicator-value buy';
                } else {
                    maEl.textContent = 'Sell';
                    maEl.className = 'indicator-value sell';
                }
            }
        }
    }
    
    // Initialize on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initChart();
            loadChartData(currentSymbol, currentInterval);
            loadCurrentPrice(currentSymbol);
            startTimer();
            updateBetRatio();
            setInterval(updateBetRatio, 5000); // Update every 5 seconds
            
            // Update price every 5 seconds
            priceUpdateInterval = setInterval(() => {
                loadCurrentPrice(currentSymbol);
            }, 5000);
            
            // Update profit display when amount changes
            document.getElementById('bet-amount').addEventListener('input', updateProfitDisplay);
        });
    } else {
        initChart();
        loadChartData(currentSymbol, currentInterval);
        loadCurrentPrice(currentSymbol);
        startTimer();
        updateBetRatio();
        setInterval(updateBetRatio, 5000);
        
        // Update price every 5 seconds
        priceUpdateInterval = setInterval(() => {
            loadCurrentPrice(currentSymbol);
        }, 5000);
        
        // Update profit display when amount changes
        document.getElementById('bet-amount').addEventListener('input', updateProfitDisplay);
    }
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        disconnectWebSocket();
        if (priceUpdateInterval) {
            clearInterval(priceUpdateInterval);
        }
        if (chart) {
            chart.remove();
        }
    });
</script>
@endpush
