@extends('layouts.mobile')

@section('title', 'Trading - Micex')

@push('styles')
<style>
    * {
        box-sizing: border-box;
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }

    .refresh-spinning {
        animation: spin 1s linear infinite;
    }

    /* Ensure wallet toggle button is visible */
    #trWalletToggleBtn {
        display: flex !important;
        visibility: visible !important;
        opacity: 1 !important;
        position: relative !important;
    }

    #trWalletDropdown {
        z-index: 9999 !important;
    }

    /* Ensure Buy/Sell buttons are clickable */
    #trCallBtn,
    #trPutBtn {
        pointer-events: auto !important;
        cursor: pointer !important;
        position: relative !important;
        z-index: 10 !important;
    }

    #trCallBtn:disabled,
    #trPutBtn:disabled {
        pointer-events: none !important;
        cursor: not-allowed !important;
        opacity: 0.5 !important;
    }

    /* Override layout background */
    body, html {
        background: #0b0b0b !important;
    }

    main {
        background: #0b0b0b !important;
    }

    #trChart {
        width: 100%;
        height: 50vh;
        min-height: 350px;
        background: #0b0b0b;
        position: relative;
        border: none !important;
        outline: none !important;
        flex-shrink: 0;
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
            height: 35vh;
            min-height: 250px;
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

    /* Hide TradingView header/info bar */
    #trChart > div > div:first-child,
    #trChart iframe + div,
    iframe[src*="tradingview"] + div,
    .tv-header,
    [class*="header"],
    [class*="symbol-info"],
    [class*="pane-legend"],
    [id*="header"],
    [id*="symbol"],
    div[style*="position: absolute"][style*="top: 0"] {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        height: 0 !important;
        overflow: hidden !important;
    }

    /* Ensure chart container takes full height */
    #trChart {
        position: relative;
        overflow: hidden;
    }

    /* Hide any top bar in TradingView iframe */
    #trChart iframe {
        border: none !important;
    }

    /* Remove all borders from TradingView chart */
    #trChart * {
        border: none !important;
        border-width: 0 !important;
        border-style: none !important;
        border-color: transparent !important;
    }

    /* Remove border from TradingView widget container */
    #trChart > div,
    #trChart > div > div,
    #trChart > div > div > div {
        border: none !important;
        border-width: 0 !important;
        outline: none !important;
    }

    /* Remove border from iframe and its contents */
    #trChart iframe,
    #trChart iframe * {
        border: none !important;
        border-width: 0 !important;
        outline: none !important;
    }

    /* Remove any gray borders or outlines */
    #trChart,
    #trChart * {
        box-shadow: none !important;
        -webkit-box-shadow: none !important;
        -moz-box-shadow: none !important;
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
        <h1 class="text-white text-base font-semibold">Trading</h1>
        <div class="w-6"></div>
    </header>
@endsection

@section('content')
<div class="flex flex-col h-full" style="height: calc(100vh - 64px - 80px);">
    <!-- Chart Area -->
    <div id="trChart" class="flex-shrink-0" style="height: 42vh; min-height: 330px;"></div>

    <!-- Bottom Trading Controls -->
    <div class="px-4 py-4 space-y-3">
        <!-- Navigation Tabs -->
        <div class="flex items-center justify-center gap-0 w-full" style="height: 41px; border-radius: 20px; background: #0a0a0a; position: relative; padding: 2px;">
            <button id="trTabBuySell" class="flex-1 h-full bg-gray-700 rounded-[18px] text-white text-sm font-semibold transition-all">BUY/SELL</button>
            <button id="trTabOrder" class="flex-1 h-full rounded-[18px] text-gray-400 text-sm transition-all">Oder</button>
            <button id="trTabEntry" class="flex-1 h-full rounded-[18px] text-gray-400 text-sm transition-all">Entry</button>
        </div>

        <!-- BUY/SELL Tab Content -->
        <div id="trTabContentBuySell" class="tab-content space-y-3">
            <!-- Balance and Wallet Selection -->
            <div class="bg-[#232944] rounded-lg p-3 space-y-2">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <p class="text-gray-400 text-sm font-medium">Tổng số dư:</p>
                        <span id="trTotalBalanceText" class="text-white text-base font-medium">0.00</span>
                        <span class="text-gray-400 text-sm font-medium">USDT</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" id="trRefreshBalanceBtn"
                            class="text-center cursor-pointer hover:opacity-80 transition-opacity">
                            <svg id="trRefreshBalanceIcon" xmlns="http://www.w3.org/2000/svg" width="15"
                                height="16" viewBox="0 0 15 16" fill="none"
                                class="transition-transform duration-300">
                                <path
                                    d="M1.56689 11.1755C1.81326 11.5861 2.11437 11.9693 2.45655 12.3115C4.975 14.83 9.06747 14.83 11.5996 12.3115C12.6261 11.285 13.2147 9.98464 13.4063 8.65698"
                                    stroke="#707797" stroke-width="1.3" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path
                                    d="M0.649902 6.82298C0.841523 5.48164 1.43008 4.19498 2.45662 3.16844C4.97507 0.64999 9.06754 0.64999 11.5997 3.16844C11.9555 3.5243 12.243 3.90757 12.4893 4.3045"
                                    stroke="#707797" stroke-width="1.3" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M1.30664 14.8299V11.1755H4.9611" stroke="#707797" stroke-width="1.3"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M12.7492 0.650024V4.30449H9.09473" stroke="#707797" stroke-width="1.3"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                        <div class="relative flex-shrink-0 z-30">
                            <button type="button" id="trWalletToggleBtn"
                                class="w-7 h-7 flex items-center justify-center rounded-full border-2 border-white/40 bg-white/10 hover:bg-white/20 hover:border-white/60 transition-all cursor-pointer">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div id="trWalletDropdown"
                                class="hidden absolute right-0 top-full mt-1 z-50 w-48 bg-gray-800 border border-gray-700 rounded-lg shadow-lg overflow-hidden">
                                <button type="button" data-wallet="deposit"
                                    class="w-full flex items-center gap-2 px-3 py-2.5 text-left text-white hover:bg-gray-700 transition-colors tr-wallet-option">
                                    <span
                                        class="w-4 h-4 rounded-full border-2 border-green-500 flex items-center justify-center">
                                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                    </span>
                                    <span>Ví giao dịch</span>
                                </button>
                                <div class="border-t border-gray-700"></div>
                                <button type="button" data-wallet="reward"
                                    class="w-full flex items-center gap-2 px-3 py-2.5 text-left text-gray-400 hover:bg-gray-700 transition-colors tr-wallet-option">
                                    <span class="w-4 h-4 rounded-full border-2 border-gray-500"></span>
                                    <span>Ví tiền thưởng</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Buy/Sell Sentiment Bar (tổng bet tất cả user - cập nhật từ API statistics) -->
            <div class="space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span id="trSellPercentText" class="text-white">0% Sell</span>
                    <span id="trBuyPercentText" class="text-white">0% Buy</span>
                </div>
                <div class="h-2 bg-[#232944] rounded-full overflow-hidden flex gap-0.5">
                    <div id="trSellBar" class="bg-red-500 h-full rounded-l-full flex-shrink-0" style="width: 24%;"></div>
                    <div id="trBuyBar" class="bg-green-500 h-full rounded-r-full flex-shrink-0" style="width: 76%;"></div>
                </div>
            </div>

            <!-- Profit Display -->
            <div class="flex items-center justify-between">
                <span class="text-white text-sm">Lợi nhuận</span>
                <span id="trProfitDisplay" class="text-green-500 font-semibold">95% +19,5$</span>
            </div>

            <!-- Input Fields -->
            <div class="flex gap-3">
                <div class="bg-[#232944] rounded-lg p-3 flex-[2]">
                    <input type="number" id="trBetAmount" value="10" min="0.01" step="0.01" 
                           class="w-full bg-transparent text-white text-base outline-none" placeholder="$10">
                </div>
                <div class="bg-[#232944] rounded-lg p-3 flex-[1] flex items-center justify-center">
                    <span id="trTimer" class="text-white text-base font-mono">00:00:29</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3">
                <button id="trPutBtn" type="button" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-3 rounded-lg text-base transition-colors flex-[1] cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">Sell</button>
                <button id="trCallBtn" type="button" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 rounded-lg text-base transition-colors flex-[2] cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">Buy</button>
            </div>
        </div>

        <!-- Order Tab Content -->
        <div id="trTabContentOrder" class="tab-content hidden flex flex-col" style="max-height: calc(100vh - 200px); overflow-y: auto;">
            <div class="space-y-3 flex-1">
                <!-- Matched BUY/SELL Summary Cards -->
                <div class="flex gap-3 w-full">
                    <div class="flex-1 w-full bg-[#232944] rounded-xl p-3">
                        <div class="flex items-center justify-between">
                            <span class="text-green-500 text-sm font-semibold">BUY</span>
                            <span id="trMatchedBuyAmount" class="text-white text-sm font-medium">$0.00</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span><span class="text-gray-400 text-sm font-medium">Đã khớp</span><span class="text-green-500 text-sm font-medium"> BUY</span></span>
                            <span id="trMatchedBuyAmountDetail" class="text-white text-sm">$0.00</span>
                        </div>
                    </div>
                    <div class="flex-1 w-full bg-[#232944] rounded-xl p-3">
                        <div class="flex items-center justify-between">
                            <span class="text-red-500 text-sm font-semibold">SELL</span>
                            <span id="trMatchedSellAmount" class="text-white text-sm font-medium">$0.00</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span><span class="text-gray-400 text-sm font-medium">Đã khớp</span><span class="text-red-500 text-sm font-medium"> SELL</span></span>
                            <span id="trMatchedSellAmountDetail" class="text-white text-sm">$0.00</span>
                        </div>
                    </div>
                </div>

                <!-- BUY Block -->
                <div class="bg-[#232944] rounded-lg p-3 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-green-500 text-sm font-semibold">Đặt cược BUY</span>
                        <span id="trBuyTotalAmount" class="text-white text-sm">$0.00</span>
                    </div>
                    <div id="trBuyBetsContainer" class="space-y-2">
                        <!-- BUY bet items will be dynamically inserted here -->
                    </div>
                </div>

                <!-- SELL Block -->
                <div class="bg-[#232944] rounded-lg p-3 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-red-500 text-sm font-semibold">Đặt cược SELL</span>
                        <span id="trSellTotalAmount" class="text-white text-sm">$0.00</span>
                    </div>
                    <div id="trSellBetsContainer" class="space-y-2">
                        <!-- SELL bet items will be dynamically inserted here -->
                    </div>
                </div>

                <!-- No Orders Message -->
                <div id="trNoOrdersMessage" class="bg-[#232944] rounded-lg p-6 text-center">
                    <p class="text-gray-400 text-sm">Chưa có đơn đặt cược nào</p>
                </div>

                <!-- Refund Information -->
                <p class="text-gray-400 text-xs text-center">
                    Số tiền chưa khớp sẽ được hoàn trả sau khi hết thời gian
                </p>
            </div>
        </div>

        <!-- Entry Tab Content -->
        <div id="trTabContentEntry" class="tab-content hidden space-y-3">
            <!-- Price Information Cards -->
            <div class="grid grid-cols-2 gap-3">
                <!-- Reference Price Card: giá khi user vào lệnh (start của round) -->
                <div class="bg-[#232944] rounded-lg p-3 space-y-2">
                    <span class="text-gray-400 text-xs">Giá tham chiếu</span>
                    <div class="flex items-center justify-between">
                        <span id="trEntryReferencePrice" class="text-white text-lg font-semibold">--</span>
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>

                <!-- Current Price Card: giá BTC hiện tại (realtime) -->
                <div class="bg-[#232944] rounded-lg p-3 space-y-2">
                    <span class="text-gray-400 text-xs">Giá hiện tại</span>
                    <div id="trEntryCurrentPrice" class="text-green-500 text-lg font-semibold">--</div>
                </div>
            </div>

            <!-- Result Calculation Description -->
            <div class="bg-[#232944] rounded-lg p-4">
                <p class="text-gray-400 text-xs leading-relaxed">
                    "Kết quả được tính bằng cách so sánh giá Entry tại giây 55 và giá kết thúc tại giây 60 của BTC/USDT."
                </p>
            </div>
        </div>

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

    <!-- Winning Result Popup -->
    <div id="trWinningResultPopup" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/70"
        style="display: none;">
        <div class="relative w-full max-w-sm mx-4">
            <button type="button" id="trCloseWinningResult"
                class="absolute top-2 right-2 z-10 w-8 h-8 rounded-full bg-[#2F2F5C] flex items-center justify-center text-white hover:bg-[#3F3F6C] transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="relative">
                <img src="{{ asset('images/xanhdoresult1.png') }}" alt="Kết quả thắng" class="w-full h-auto">
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="text-center" style="margin-top: 20%;">
                        <div id="trWinningAmountText" class="text-green-500 font-bold text-4xl"
                            style="text-shadow: 0 0 10px rgba(34, 197, 94, 0.5);">
                            + 0$
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
<script>
    /* ================== CHART ================== */
    let trChart = null;
    
    // Initialize TradingView widget (UI only - no logic)
    if (typeof TradingView !== 'undefined') {
        const trChartContainer = document.getElementById("trChart");
        if (trChartContainer) {
            trChart = new TradingView.widget({
                "autosize": true,
                "symbol": "BINANCE:BTCUSDT",
                "interval": "1",
                "container_id": "trChart",
                "datafeed": {
                    onReady: function(callback) {
                        callback({
                            supported_resolutions: ["1", "5", "15", "30", "60", "240", "D"],
                            supports_marks: false,
                            supports_timescale_marks: false,
                            supports_time: false
                        });
                    },
                    searchSymbols: function() {},
                    resolveSymbol: function(symbolName, onSymbolResolvedCallback) {
                        onSymbolResolvedCallback({
                            name: symbolName,
                            ticker: symbolName,
                            description: "",
                            type: "crypto",
                            session: "24x7",
                            timezone: "Etc/UTC",
                            exchange: "BINANCE",
                            minmov: 1,
                            pricescale: 100,
                            has_intraday: true,
                            has_weekly_and_monthly: false,
                            supported_resolutions: ["1", "5", "15", "30", "60", "240", "D"],
                            volume_precision: 8,
                            data_status: "streaming"
                        });
                    },
                    getBars: function(symbolInfo, resolution, from, to, onHistoryCallback, onErrorCallback) {
                        onHistoryCallback([], { noData: true });
                    },
                    subscribeBars: function() {},
                    unsubscribeBars: function() {}
                },
                "locale": "en",
                "disabled_features": [
                    "use_localstorage_for_settings",
                    "volume_force_overlay",
                    "create_volume_indicator_by_default",
                    "header_widget",
                    "header_symbol_search",
                    "header_compare",
                    "header_undo_redo",
                    "header_screenshot",
                    "header_chart_type",
                    "header_resolutions",
                    "header_save_load",
                    "header_settings",
                    "header_fullscreen_button",
                    "header_widget_dom_node",
                    "show_logo_on_all_charts",
                    "symbol_info",
                    "pane_legend"
                ],
                "enabled_features": ["study_templates"],
                "charts_storage_url": "",
                "charts_storage_api_version": "1.1",
                "client_id": "tradingview.com",
                "user_id": "public_user_id",
                "fullscreen": false,
                "autosize": true,
                "studies_overrides": {},
                "theme": "dark",
                "overrides": {
                    "paneProperties.background": "#0b0b0b",
                    "paneProperties.backgroundType": "solid",
                    "mainSeriesProperties.candleStyle.upColor": "#00ff9c",
                    "mainSeriesProperties.candleStyle.downColor": "#ff4d4d",
                    "mainSeriesProperties.candleStyle.borderUpColor": "#00ff9c",
                    "mainSeriesProperties.candleStyle.borderDownColor": "#ff4d4d",
                    "mainSeriesProperties.candleStyle.wickUpColor": "#00ff9c",
                    "mainSeriesProperties.candleStyle.wickDownColor": "#ff4d4d",
                    "paneProperties.legendProperties.showLegend": false,
                    "paneProperties.legendProperties.showSeriesTitle": false,
                    "paneProperties.legendProperties.showStudyValues": false,
                    "paneProperties.legendProperties.showStudyArguments": false
                },
                "hide_top_toolbar": true,
                "hide_side_toolbar": true,
                "hide_volume": false
            });
        }
    }

    /* ================== TRADING LOGIC ================== */
    let trTimerInterval = null;
    let trRoundData = null;
    let trMyBets = [];
    let trSelectedWallet = 'deposit'; // Default to deposit wallet
    let trUserBalances = { balance: 0, reward_balance: 0 };
    let trLastProcessedRoundId = null;
    let trLastBetStatuses = {}; // Track bet statuses to detect changes
    let trLastCheckedRound = null; // Track which round we've already checked for winnings
    let trWinningModalTimeout = null; // Timeout for auto-closing winning modal
    let trIsInitialLoad = true; // Track if this is the initial page load
    let trLastShownRoundNumber = null; // Track which round number we've already shown modal for
    let trCurrentBtcPrice = null; // Giá BTC hiện tại (lấy từ Binance FE), dùng khi vào lệnh và hiển thị tab Entry
    let trPreviousRoundNumber = null; // Để phát hiện round vừa kết thúc (khi round_number tăng)

    // Clear clientBetInfo from other games when on trading page
    // This prevents global result popup from showing results from other games
    if (localStorage.getItem('clientBetInfo')) {
        localStorage.removeItem('clientBetInfo');
        localStorage.removeItem('resultPopupShownForRound');
    }

    // Get current round and update UI
    async function updateRoundData() {
        try {
            const response = await fetch('/api/trading/current-round');
            const data = await response.json();
            
            if (data.round) {
                const currentRoundNumber = data.round.round_number;

                // Phát hiện round vừa kết thúc: (1) API trả về status finished HOẶC (2) round_number tăng (API thường trả về round mới nên không có status finished)
                let finishedRoundNumber = null;
                if (data.round.status === 'finished' && data.round.final_result && trLastProcessedRoundId !== data.round.id) {
                    finishedRoundNumber = data.round.round_number;
                    trLastProcessedRoundId = data.round.id;
                } else if (trPreviousRoundNumber !== null && currentRoundNumber > trPreviousRoundNumber) {
                    finishedRoundNumber = trPreviousRoundNumber;
                }
                trPreviousRoundNumber = currentRoundNumber;

                if (finishedRoundNumber != null) {
                    console.log('Round finished:', finishedRoundNumber, 'Result:', data.round.final_result || '(from round number change)');
                    await updateMyBets();
                    await trCheckRoundWinnings(finishedRoundNumber);
                }

                trRoundData = data.round;
                updateTimer();
                updateStatistics(data.statistics);
                updateEntryTabPrices();
            }
        } catch (error) {
            console.error('Error fetching round data:', error);
        }
    }

    // Lấy giá BTC từ Binance (gọi ở FE)
    async function fetchBtcPriceFromBinance() {
        try {
            const res = await fetch('https://api.binance.com/api/v3/ticker/price?symbol=BTCUSDT');
            const data = await res.json();
            const p = data && data.price != null ? parseFloat(data.price) : null;
            if (p != null && !isNaN(p)) return p;
        } catch (e) {
            console.warn('Fetch Binance price failed:', e);
        }
        return null;
    }

    function formatPrice(num) {
        if (num == null || isNaN(num)) return '--';
        const n = Number(num);
        if (n === 0) return '0';
        return n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    // Cập nhật tab Entry: Giá tham chiếu = giá vào lệnh (entry_price của bet user), Giá hiện tại = Binance
    function updateEntryTabPrices() {
        const refEl = document.getElementById('trEntryReferencePrice');
        const curEl = document.getElementById('trEntryCurrentPrice');
        if (curEl) curEl.textContent = trCurrentBtcPrice != null ? formatPrice(trCurrentBtcPrice) : '--';
        let refPrice = null;
        if (trRoundData && trMyBets && trMyBets.length > 0) {
            const roundBets = trMyBets.filter(b => b.round_id === trRoundData.id);
            const latest = roundBets[0];
            if (latest && latest.entry_price != null) refPrice = latest.entry_price;
        }
        if (refEl) refEl.textContent = refPrice != null ? formatPrice(refPrice) : '--';
    }

    // Get user's bets
    async function updateMyBets() {
        try {
            const response = await fetch('/api/trading/my-bets');
            const data = await response.json();
            
            if (data.bets) {
                let totalWinnings = 0;
                let hasNewWonBets = false;
                let hasShownModalForRound = false;
                
                // Check for status changes and show notifications
                data.bets.forEach(bet => {
                    const payoutAmount = parseFloat(bet.payout_amount) || 0;
                    
                    if (!trLastBetStatuses[bet.id] || trLastBetStatuses[bet.id] !== bet.status) {
                        // Status changed
                        if (trLastBetStatuses[bet.id] === 'pending' && bet.status === 'won') {
                            // Bet won - NEW WINNING BET!
                            hasNewWonBets = true;
                            totalWinnings += payoutAmount;
                            console.log('🎉 NEW WINNING BET!', bet.id, 'Payout:', payoutAmount);
                        } else if (trLastBetStatuses[bet.id] === 'pending' && bet.status === 'lost') {
                            // Bet lost
                        }
                        trLastBetStatuses[bet.id] = bet.status;
                    }
                    
                    // Always add won bets to total (for modal)
                    if (bet.status === 'won' && payoutAmount > 0) {
                        totalWinnings += payoutAmount;
                    }
                });
                
                // ALWAYS check and show modal if we have winning bets with payout
                // Check if we haven't shown modal for this round yet
                const currentRoundId = trRoundData?.id;
                const modalShownKey = `trModalShown_${currentRoundId}`;
                const hasShownModal = sessionStorage.getItem(modalShownKey) === 'true';
                
                if (totalWinnings > 0 && !hasShownModal) {
                    console.log('💰 SHOWING MODAL! Total winnings:', totalWinnings, 'Round:', currentRoundId);
                    sessionStorage.setItem(modalShownKey, 'true');
                    setTimeout(() => {
                        trShowWinningResult(totalWinnings);
                        // Auto-close after 3 seconds
                        if (trWinningModalTimeout) {
                            clearTimeout(trWinningModalTimeout);
                        }
                        trWinningModalTimeout = setTimeout(() => {
                            trHideWinningResult();
                        }, 3000);
                    }, 300);
                }
                
                trMyBets = data.bets;
                updateOrderTab();
                updateEntryTabPrices();
            }

            // Update balances
            if (data.balance !== undefined) {
                trUserBalances.balance = parseFloat(data.balance) || 0;
                trUserBalances.reward_balance = parseFloat(data.reward_balance) || 0;
                updateBalanceDisplay();
            }
        } catch (error) {
            console.error('Error fetching my bets:', error);
        }
    }

    // Update balance display based on selected wallet
    function updateBalanceDisplay() {
        const balanceEl = document.getElementById('trTotalBalanceText');
        if (!balanceEl) return;
        
        const selectedBalance = trSelectedWallet === 'reward' 
            ? trUserBalances.reward_balance 
            : trUserBalances.balance;
        
        balanceEl.textContent = selectedBalance.toFixed(2);
    }

    // Update timer display
    function updateTimer() {
        const timerEl = document.getElementById('trTimer');
        if (!timerEl) return;

        const buyBtn = document.getElementById('trCallBtn');
        const sellBtn = document.getElementById('trPutBtn');

        if (!trRoundData) {
            // No round data - disable buttons
            if (buyBtn) buyBtn.disabled = true;
            if (sellBtn) sellBtn.disabled = true;
            timerEl.textContent = '00:00:00';
            return;
        }

        if (trRoundData.status === 'running' && trRoundData.started_at) {
            const startedAt = new Date(trRoundData.started_at);
            const now = new Date();
            const elapsed = Math.floor((now - startedAt) / 1000);
            const remaining = Math.max(0, 60 - elapsed);
            
            const minutes = Math.floor(remaining / 60);
            const seconds = remaining % 60;
            timerEl.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}:00`;
            
            // Enable buttons only in first 55 seconds (elapsed <= 55)
            // After 55 seconds, disable buttons
            if (elapsed <= 55) {
                if (buyBtn) buyBtn.disabled = false;
                if (sellBtn) sellBtn.disabled = false;
            } else {
                if (buyBtn) buyBtn.disabled = true;
                if (sellBtn) sellBtn.disabled = true;
            }
        } else {
            // Round not running - disable buttons
            if (buyBtn) buyBtn.disabled = true;
            if (sellBtn) sellBtn.disabled = true;
            timerEl.textContent = '00:00:00';
        }
    }

    // Update statistics: % thanh sentiment + hai thẻ BUY/SELL (tổng bet tất cả user: khớp + đang chờ)
    function updateStatistics(stats) {
        if (!stats) return;
        
        const sellBar = document.getElementById('trSellBar');
        const buyBar = document.getElementById('trBuyBar');
        const sellText = document.getElementById('trSellPercentText');
        const buyText = document.getElementById('trBuyPercentText');
        
        const sellPct = Number(stats.sell_percentage) || 0;
        const buyPct = Number(stats.buy_percentage) || 0;
        
        if (sellBar) sellBar.style.width = `calc(${sellPct}% - 2px)`;
        if (buyBar) buyBar.style.width = `calc(${buyPct}% - 2px)`;
        if (sellText) sellText.textContent = sellPct + '% Sell';
        if (buyText) buyText.textContent = buyPct + '% Buy';

        // BUY = tổng amount cược BUY. Đã khớp BUY = tổng - chưa khớp (số matched)
        const totalBuyAll = Number(stats.total_buy_all) || 0;
        const totalSellAll = Number(stats.total_sell_all) || 0;
        const totalBuyMatchedAll = Number(stats.total_buy_matched_all) || 0;
        const totalSellMatchedAll = Number(stats.total_sell_matched_all) || 0;
        const matchedBuyEl = document.getElementById('trMatchedBuyAmount');
        const matchedBuyDetailEl = document.getElementById('trMatchedBuyAmountDetail');
        const matchedSellEl = document.getElementById('trMatchedSellAmount');
        const matchedSellDetailEl = document.getElementById('trMatchedSellAmountDetail');
        if (matchedBuyEl) matchedBuyEl.textContent = '$' + totalBuyAll.toFixed(2);
        if (matchedBuyDetailEl) matchedBuyDetailEl.textContent = '$' + totalBuyMatchedAll.toFixed(2);
        if (matchedSellEl) matchedSellEl.textContent = '$' + totalSellAll.toFixed(2);
        if (matchedSellDetailEl) matchedSellDetailEl.textContent = '$' + totalSellMatchedAll.toFixed(2);
    }

    // Update Order tab with user's bets
    function updateOrderTab() {
        const orderContent = document.getElementById('trTabContentOrder');
        if (!orderContent) return;

        // Group bets by direction
        const buyBets = trMyBets.filter(b => b.direction === 'BUY');
        const sellBets = trMyBets.filter(b => b.direction === 'SELL');

        const totalBuyAmount = buyBets.reduce((sum, b) => sum + (parseFloat(b.matched_amount) || 0) + (parseFloat(b.pending_amount) || 0), 0);
        const totalSellAmount = sellBets.reduce((sum, b) => sum + (parseFloat(b.matched_amount) || 0) + (parseFloat(b.pending_amount) || 0), 0);

        // Update total amounts in Order tab (chỉ tổng của user trong từng khối Đặt cược BUY/SELL)
        const buyTotalAmount = document.getElementById('trBuyTotalAmount');
        const sellTotalAmount = document.getElementById('trSellTotalAmount');
        if (buyTotalAmount) buyTotalAmount.textContent = '$' + totalBuyAmount.toFixed(2);
        if (sellTotalAmount) sellTotalAmount.textContent = '$' + totalSellAmount.toFixed(2);
        // Hai thẻ Đã khớp BUY/SELL được cập nhật từ updateStatistics (tổng tất cả user)

        // Get containers
        const buyBetsContainer = document.getElementById('trBuyBetsContainer');
        const sellBetsContainer = document.getElementById('trSellBetsContainer');
        const noOrdersMessage = document.getElementById('trNoOrdersMessage');

        // Clear existing bet items
        if (buyBetsContainer) buyBetsContainer.innerHTML = '';
        if (sellBetsContainer) sellBetsContainer.innerHTML = '';

        // Render BUY bets
        if (buyBets.length > 0 && buyBetsContainer) {
            buyBets.forEach(bet => {
                const betItem = createBetItem(bet);
                buyBetsContainer.appendChild(betItem);
            });
        }

        // Render SELL bets
        if (sellBets.length > 0 && sellBetsContainer) {
            sellBets.forEach(bet => {
                const betItem = createBetItem(bet);
                sellBetsContainer.appendChild(betItem);
            });
        }

        // Show/hide no orders message
        if (trMyBets.length > 0) {
            if (noOrdersMessage) noOrdersMessage.classList.add('hidden');
        } else {
            if (noOrdersMessage) noOrdersMessage.classList.remove('hidden');
        }
    }

    // Helper function to create bet item
    function createBetItem(bet) {
        const matchedAmount = parseFloat(bet.matched_amount) || 0;
        const pendingAmount = parseFloat(bet.pending_amount) || 0;
        const payoutAmount = parseFloat(bet.payout_amount) || 0;
        
        // Create bet item container - chỉ chứa badges, không có header
        const betItem = document.createElement('div');
        betItem.className = 'w-fit space-y-2';
        
        // Bet status indicators container - all badges in one block
        const indicatorsContainer = document.createElement('div');
        indicatorsContainer.className = 'space-y-2';
        
        // Đã cược badge (if matched_amount > 0)
        if (matchedAmount > 0) {
            const matchedBadge = document.createElement('div');
            matchedBadge.className = 'p-3 flex items-center gap-2';
            matchedBadge.style.cssText = 'background: #328357; border-radius: 20px;';
            matchedBadge.innerHTML = `
            <div class="flex items-center rounded-full justify-center border border-[#0AFF68] p-1 w-6 h-6">
                <svg width="12" height="10" viewBox="0 0 12 10" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M0.473877 4.4541L4.76831 7.9541L10.4739 0.454102" stroke="white" stroke-width="1.5"/>
</svg>
</div>

                <span class="text-[#0AFF68] text-sm">Đã cược: $${matchedAmount.toFixed(2)}</span>
            `;
            indicatorsContainer.appendChild(matchedBadge);
        }
        
        // Đang chờ khớp badge (if pending_amount > 0)
        if (pendingAmount > 0) {
            const pendingBadge = document.createElement('div');
            pendingBadge.className = 'p-3 flex items-center gap-2';
            pendingBadge.style.cssText = 'background: #554524; border-radius: 20px;';
            pendingBadge.innerHTML = `
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path opacity="0.3" d="M10 1.6665L10 4.1665" stroke="#E4B754" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path opacity="0.3" d="M10 15.833L10 18.333" stroke="#E4B754" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path opacity="0.3" d="M18.3334 10L15.8334 10" stroke="#E4B754" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path opacity="0.3" d="M4.16663 10L1.66663 10" stroke="#E4B754" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M15.8925 4.10723L14.1248 5.875" stroke="#E4B754" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M5.87519 14.1248L4.10742 15.8926" stroke="#E4B754" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M4.10748 4.10723L5.87524 5.875" stroke="#E4B754" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M14.1248 14.1248L15.8926 15.8926" stroke="#E4B754" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="text-[#E8B139] text-sm">Đang chờ khớp: $${pendingAmount.toFixed(2)}</span>
            `;
            indicatorsContainer.appendChild(pendingBadge);
        }
        
        // Thắng badge (if status = 'won')
        if (bet.status === 'won') {
            const wonBadge = document.createElement('div');
            wonBadge.className = 'p-3 flex items-center gap-2';
            wonBadge.style.cssText = 'background: #22c55e; border-radius: 20px;';
            wonBadge.innerHTML = `
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-white text-sm font-semibold">Thắng: $${payoutAmount.toFixed(2)}</span>
            `;
            indicatorsContainer.appendChild(wonBadge);
        }
        
        // Thua badge (if status = 'lost')
        if (bet.status === 'lost') {
            const lostBadge = document.createElement('div');
            lostBadge.className = 'p-3 flex items-center gap-2';
            lostBadge.style.cssText = 'background: #ef4444; border-radius: 20px;';
            lostBadge.innerHTML = `
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-white text-sm font-semibold">Thua</span>
            `;
            indicatorsContainer.appendChild(lostBadge);
        }
        
        // Assemble bet item - chỉ có badges, không có header
        betItem.appendChild(indicatorsContainer);
        
        return betItem;
    }

    // Place bet (BUY or SELL)
    async function placeBet(direction) {
        console.log('placeBet called with direction:', direction);
        
        // Check if betting window is still open (first 55 seconds)
        if (trRoundData && trRoundData.status === 'running' && trRoundData.started_at) {
            const startedAt = new Date(trRoundData.started_at);
            const now = new Date();
            const elapsed = Math.floor((now - startedAt) / 1000);
            
            if (elapsed > 55) {
                return;
            }
        } else if (!trRoundData || trRoundData.status !== 'running') {
            return;
        }
        
        const amountInput = document.getElementById('trBetAmount');
        if (!amountInput) {
            console.error('Amount input not found');
            return;
        }

        const amount = parseFloat(amountInput.value);
        if (!amount || amount <= 0) {
            return;
        }

        // Check balance
        const selectedBalance = trSelectedWallet === 'reward' 
            ? trUserBalances.reward_balance 
            : trUserBalances.balance;
        
        if (amount > selectedBalance) {
            return;
        }

        const buyBtn = document.getElementById('trCallBtn');
        const sellBtn = document.getElementById('trPutBtn');
        
        if (buyBtn) buyBtn.disabled = true;
        if (sellBtn) sellBtn.disabled = true;

        try {
            const response = await fetch('/api/trading/bet', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    direction: direction,
                    amount: amount,
                    wallet_type: trSelectedWallet,
                    entry_price: trCurrentBtcPrice != null ? trCurrentBtcPrice : undefined
                })
            });

            const data = await response.json();

            if (data.success) {
                await updateMyBets();
                await updateRoundData();
            }
        } catch (error) {
            console.error('Error placing bet:', error);
        } finally {
            if (buyBtn) buyBtn.disabled = false;
            if (sellBtn) sellBtn.disabled = false;
        }
    }

    /* ================== TAB SWITCHING ================== */
    document.addEventListener('DOMContentLoaded', function() {
        const tabBuySell = document.getElementById("trTabBuySell");
        const tabOrder = document.getElementById("trTabOrder");
        const tabEntry = document.getElementById("trTabEntry");
        
        const contentBuySell = document.getElementById("trTabContentBuySell");
        const contentOrder = document.getElementById("trTabContentOrder");
        const contentEntry = document.getElementById("trTabContentEntry");

        function switchTab(activeTab, activeContent) {
            // Reset all tabs
            [tabBuySell, tabOrder, tabEntry].forEach(tab => {
                if (tab) {
                    tab.classList.remove('bg-gray-700', 'text-white', 'rounded-[18px]');
                    tab.classList.add('text-gray-400');
                }
            });

            // Reset all content
            [contentBuySell, contentOrder, contentEntry].forEach(content => {
                if (content) {
                    content.classList.add('hidden');
                }
            });

            // Activate selected tab
            if (activeTab) {
                activeTab.classList.remove('text-gray-400');
                activeTab.classList.add('bg-gray-700', 'text-white', 'rounded-[18px]');
            }

            // Show selected content
            if (activeContent) {
                activeContent.classList.remove('hidden');
            }

            // Update data when switching to Order tab
            if (activeContent === contentOrder) {
                updateMyBets();
            }
        }

        if (tabBuySell) {
            tabBuySell.addEventListener('click', function() {
                switchTab(tabBuySell, contentBuySell);
            });
        }

        if (tabOrder) {
            tabOrder.addEventListener('click', function() {
                switchTab(tabOrder, contentOrder);
                // Update orders when switching to Order tab
                updateMyBets();
            });
        }

        if (tabEntry) {
            tabEntry.addEventListener('click', function() {
                switchTab(tabEntry, contentEntry);
            });
        }

        // Setup bet buttons - ensure they're clickable
        const buyBtn = document.getElementById('trCallBtn');
        const sellBtn = document.getElementById('trPutBtn');
        
        if (buyBtn) {
            buyBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Buy button clicked');
                placeBet('BUY');
            }, true); // Use capture phase
            // Ensure button is enabled by default
            buyBtn.disabled = false;
            buyBtn.style.pointerEvents = 'auto';
            buyBtn.style.cursor = 'pointer';
        } else {
            console.error('Buy button not found');
        }

        if (sellBtn) {
            sellBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Sell button clicked');
                placeBet('SELL');
            }, true); // Use capture phase
            // Ensure button is enabled by default
            sellBtn.disabled = false;
            sellBtn.style.pointerEvents = 'auto';
            sellBtn.style.cursor = 'pointer';
        } else {
            console.error('Sell button not found');
        }

        // Wallet dropdown
        const walletToggleBtn = document.getElementById('trWalletToggleBtn');
        const walletDropdown = document.getElementById('trWalletDropdown');
        const walletOptions = document.querySelectorAll('.tr-wallet-option');

        if (walletToggleBtn && walletDropdown) {
            walletToggleBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                walletDropdown.classList.toggle('hidden');
            });

            walletOptions.forEach(option => {
                option.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const walletType = option.dataset.wallet;
                    trSelectedWallet = walletType;

                    // Update radio buttons
                    walletOptions.forEach(opt => {
                        const radio = opt.querySelector('span');
                        if (opt.dataset.wallet === walletType) {
                            radio.className =
                                'w-4 h-4 rounded-full border-2 border-green-500 flex items-center justify-center';
                            radio.innerHTML =
                                '<span class="w-2 h-2 rounded-full bg-green-500"></span>';
                            opt.classList.remove('text-gray-400');
                            opt.classList.add('text-white');
                        } else {
                            radio.className =
                                'w-4 h-4 rounded-full border-2 border-gray-500';
                            radio.innerHTML = '';
                            opt.classList.remove('text-white');
                            opt.classList.add('text-gray-400');
                        }
                    });

                    // Update displayed balance
                    updateBalanceDisplay();
                    walletDropdown.classList.add('hidden');
                });
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!walletToggleBtn.contains(e.target) && !walletDropdown.contains(e.target)) {
                    walletDropdown.classList.add('hidden');
                }
            });
        }

        // Refresh balance button
        const refreshBalanceBtn = document.getElementById('trRefreshBalanceBtn');
        if (refreshBalanceBtn) {
            refreshBalanceBtn.addEventListener('click', async () => {
                const icon = document.getElementById('trRefreshBalanceIcon');
                if (icon) icon.classList.add('refresh-spinning');
                try {
                    await updateMyBets();
                } finally {
                    if (icon) icon.classList.remove('refresh-spinning');
                }
            });
        }

        // Initialize: fetch round data, bets, và giá BTC (tab Entry)
        updateRoundData();
        updateMyBets();
        fetchBtcPriceFromBinance().then(function(p) {
            if (p != null) trCurrentBtcPrice = p;
            updateEntryTabPrices();
        });

        // Update every second: timer, round, và giá BTC từ Binance (tab Entry)
        trTimerInterval = setInterval(function() {
            updateTimer();
            updateRoundData();
            fetchBtcPriceFromBinance().then(function(p) {
                if (p != null) trCurrentBtcPrice = p;
                updateEntryTabPrices();
            });
        }, 1000);

        // Update bets every 3 seconds
        setInterval(function() {
            updateMyBets();
        }, 3000);
        
        // Mark initial load as complete after first update
        setTimeout(() => {
            trIsInitialLoad = false;
            console.log('Initial load complete, modal can now be shown');
        }, 2000);
        
        // Test modal function (for debugging - remove in production)
        window.testTradingModal = function(amount = 100) {
            console.log('Testing modal with amount:', amount);
            trShowWinningResult(amount);
        };
        
        // Winning result popup event listeners
        const trWinningPopup = document.getElementById('trWinningResultPopup');
        const trCloseWinningBtn = document.getElementById('trCloseWinningResult');
        
        if (trWinningPopup) {
            trWinningPopup.addEventListener('click', (e) => {
                if (e.target.id === 'trWinningResultPopup') {
                    trHideWinningResult();
                }
            });
        }
        
        if (trCloseWinningBtn) {
            trCloseWinningBtn.addEventListener('click', () => {
                trHideWinningResult();
            });
        }
    });
    
    async function trCheckRoundWinnings(roundNumber) {
        // Only check once per round
        if (trLastCheckedRound === roundNumber || trLastShownRoundNumber === roundNumber) {
            console.log('Already checked/shown modal for round', roundNumber);
            return;
        }
        
        console.log('=== Checking round winnings for round:', roundNumber, '===');
        const apiUrl = `/api/trading/round-winnings?round_number=${roundNumber}`;
        console.log('API URL:', apiUrl);
        
        try {
            const res = await fetch(apiUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            console.log('Response status:', res.status, res.statusText);
            
            if (!res.ok) {
                const errorText = await res.text();
                console.error('Failed to fetch round winnings:', res.status, res.statusText, errorText);
                return;
            }
            
            const data = await res.json();
            console.log('=== Round winnings API response ===', data);
            
            if (data && data.has_winnings === true && data.total_winnings > 0) {
                trLastCheckedRound = roundNumber;
                trLastShownRoundNumber = roundNumber;
                console.log('✅ Has winnings! Showing modal with:', data.total_winnings);
                trShowWinningResult(data.total_winnings);
                
                // Auto-close modal after 3 seconds
                if (trWinningModalTimeout) {
                    clearTimeout(trWinningModalTimeout);
                }
                trWinningModalTimeout = setTimeout(() => {
                    trHideWinningResult();
                }, 3000);
            } else {
                console.log('❌ No winnings for this round. Response:', data);
            }
        } catch (e) {
            console.error('❌ Error checking round winnings:', e);
            console.error('Error stack:', e.stack);
        }
    }
    
    function trShowWinningResult(amount) {
        console.log('=== trShowWinningResult called with amount:', amount, '===');
        const popup = document.getElementById('trWinningResultPopup');
        const amountText = document.getElementById('trWinningAmountText');
        
        console.log('Modal elements:', { popup: !!popup, amountText: !!amountText });
        
        if (!popup || !amountText) {
            console.error('❌ Modal elements not found:', { popup, amountText });
            return;
        }
        
        // Format amount: + 19$ or + 19.50$
        const formattedAmount = amount % 1 === 0 ?
            `+ ${amount}$` :
            `+ ${amount.toFixed(2)}$`;
        
        console.log('Setting amount text to:', formattedAmount);
        amountText.textContent = formattedAmount;
        
        // Remove hidden class and inline style
        popup.classList.remove('hidden');
        popup.style.display = 'flex';
        popup.style.visibility = 'visible';
        popup.style.opacity = '1';
        popup.style.zIndex = '9999';
        
        console.log('✅ Modal should be visible now.');
        console.log('Popup display:', popup.style.display);
        console.log('Popup classes:', popup.className);
        console.log('Popup computed style:', window.getComputedStyle(popup).display);
        
        // Force show modal
        setTimeout(() => {
            if (popup.style.display !== 'flex') {
                console.warn('Modal not showing, forcing display');
                popup.style.display = 'flex !important';
            }
        }, 100);
    }
    
    function trHideWinningResult() {
        const popup = document.getElementById('trWinningResultPopup');
        if (!popup) return;
        popup.style.display = 'none';
        popup.classList.add('hidden');
        
        // Clear timeout if modal is manually closed
        if (trWinningModalTimeout) {
            clearTimeout(trWinningModalTimeout);
            trWinningModalTimeout = null;
        }
    }
</script>
@endpush
