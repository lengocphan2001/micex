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
            <div class="bg-gray-800 rounded-lg p-3 space-y-2">
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

            <!-- Buy/Sell Sentiment Bar -->
            <div class="space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-white">24% Sell</span>
                    <span class="text-white">76% Buy</span>
                </div>
                <div class="h-2 bg-gray-800 rounded-full overflow-hidden flex">
                    <div id="trSellBar" class="bg-red-500 h-full" style="width: 24%;"></div>
                    <div id="trBuyBar" class="bg-green-500 h-full" style="width: 76%;"></div>
                </div>
            </div>

            <!-- Profit Display -->
            <div class="flex items-center justify-between">
                <span class="text-white text-sm">Lợi nhuận</span>
                <span id="trProfitDisplay" class="text-green-500 font-semibold">95% +19,5$</span>
            </div>

            <!-- Input Fields -->
            <div class="flex gap-3">
                <div class="bg-gray-800 rounded-lg p-3 flex-[2]">
                    <input type="number" id="trBetAmount" value="10" min="0.01" step="0.01" 
                           class="w-full bg-transparent text-white text-base outline-none" placeholder="$10">
                </div>
                <div class="bg-gray-800 rounded-lg p-3 flex-[1] flex items-center justify-center">
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
        <div id="trTabContentOrder" class="tab-content hidden space-y-3">
            <!-- BUY/SELL Order Summary -->
            <div class="grid grid-cols-2 gap-3">
                <!-- BUY Panel -->
                <div class="bg-gray-800 rounded-lg p-3 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-green-500 text-sm font-semibold">BUY</span>
                        <span id="trBuyTotalAmount" class="text-white text-sm">$0.00</span>
                    </div>
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-green-500 text-xs">Đã khớp BUY</span>
                        <span id="trBuyMatchedAmount" class="text-white text-sm">$0.00</span>
                    </div>
                </div>

                <!-- SELL Panel -->
                <div class="bg-gray-800 rounded-lg p-3 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-red-500 text-sm font-semibold">SELL</span>
                        <span id="trSellTotalAmount" class="text-white text-sm">$0.00</span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-red-500 text-xs">Đã khớp SELL</span>
                        <span id="trSellMatchedAmount" class="text-white text-sm">$0.00</span>
                    </div>
                </div>
            </div>

            <!-- Current Bet Status -->
            <!-- All Bets Container -->
            <div id="trBetsContainer" class="space-y-3 hidden">
                <!-- Bet items will be dynamically inserted here -->
            </div>

            <!-- No Orders Message -->
            <div id="trNoOrdersMessage" class="bg-gray-800 rounded-lg p-6 text-center">
                <p class="text-gray-400 text-sm">Chưa có đơn đặt cược nào</p>
            </div>

            <!-- Refund Information -->
            <p class="text-gray-400 text-xs text-center">
                Số tiền chưa khớp sẽ được hoàn trả sau khi hết thời gian
            </p>
        </div>

        <!-- Entry Tab Content -->
        <div id="trTabContentEntry" class="tab-content hidden space-y-3">
            <!-- Price Information Cards -->
            <div class="grid grid-cols-2 gap-3">
                <!-- Reference Price Card -->
                <div class="bg-gray-800 rounded-lg p-3 space-y-2">
                    <span class="text-gray-400 text-xs">Giá tham chiếu</span>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1">
                            <div class="w-1 h-1 bg-white rounded-full"></div>
                            <div class="w-1 h-1 bg-white rounded-full"></div>
                            <div class="w-1 h-1 bg-white rounded-full"></div>
                        </div>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>

                <!-- Current Price Card -->
                <div class="bg-gray-800 rounded-lg p-3 space-y-2">
                    <span class="text-gray-400 text-xs">Giá hiện tại</span>
                    <div class="text-green-500 text-lg font-semibold">89,300</div>
                </div>
            </div>

            <!-- Result Calculation Description -->
            <div class="bg-gray-800 rounded-lg p-4">
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
                // Check if round finished and show result notification
                if (data.round.status === 'finished' && data.round.final_result) {
                    if (trLastProcessedRoundId !== data.round.id) {
                        // New round finished, show result
                        const result = data.round.final_result;
                        if (typeof showToast === 'function') {
                            showToast(`Kết quả: ${result} thắng!`, result === 'BUY' ? 'success' : 'error');
                        }
                        trLastProcessedRoundId = data.round.id;
                        console.log('Round finished:', data.round.round_number, 'Result:', data.round.final_result);
                        
                        // Update bets - this will check for winnings and show modal automatically
                        await updateMyBets();
                    }
                }
                
                trRoundData = data.round;
                updateTimer();
                updateStatistics(data.statistics);
            }
        } catch (error) {
            console.error('Error fetching round data:', error);
        }
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
                            if (typeof showToast === 'function') {
                                showToast(`Đặt cược ${bet.direction} thắng! Nhận được $${payoutAmount.toFixed(2)}`, 'success');
                            }
                        } else if (trLastBetStatuses[bet.id] === 'pending' && bet.status === 'lost') {
                            // Bet lost
                            if (typeof showToast === 'function') {
                                showToast(`Đặt cược ${bet.direction} thua`, 'error');
                            }
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

    // Update statistics (BUY/SELL percentages)
    function updateStatistics(stats) {
        if (!stats) return;
        
        const sellBar = document.getElementById('trSellBar');
        const buyBar = document.getElementById('trBuyBar');
        const sellText = document.querySelector('#trTabContentBuySell .text-white:first-child');
        const buyText = document.querySelector('#trTabContentBuySell .text-white:last-child');
        
        if (sellBar) sellBar.style.width = stats.sell_percentage + '%';
        if (buyBar) buyBar.style.width = stats.buy_percentage + '%';
        if (sellText) sellText.textContent = stats.sell_percentage + '% Sell';
        if (buyText) buyText.textContent = stats.buy_percentage + '% Buy';
    }

    // Update Order tab with user's bets
    function updateOrderTab() {
        const orderContent = document.getElementById('trTabContentOrder');
        if (!orderContent) return;

        // Group bets by direction
        const buyBets = trMyBets.filter(b => b.direction === 'BUY');
        const sellBets = trMyBets.filter(b => b.direction === 'SELL');

        const totalBuyMatched = buyBets.reduce((sum, b) => sum + (parseFloat(b.matched_amount) || 0), 0);
        const totalBuyPending = buyBets.reduce((sum, b) => sum + (parseFloat(b.pending_amount) || 0), 0);
        const totalSellMatched = sellBets.reduce((sum, b) => sum + (parseFloat(b.matched_amount) || 0), 0);
        const totalSellPending = sellBets.reduce((sum, b) => sum + (parseFloat(b.pending_amount) || 0), 0);

        // Update BUY panel with dynamic data
        const buyTotalAmountEl = document.getElementById('trBuyTotalAmount');
        const buyMatchedAmountEl = document.getElementById('trBuyMatchedAmount');
        
        if (buyTotalAmountEl) {
            buyTotalAmountEl.textContent = '$' + (totalBuyMatched + totalBuyPending).toFixed(2);
        }
        
        if (buyMatchedAmountEl) {
            buyMatchedAmountEl.textContent = '$' + totalBuyMatched.toFixed(2);
        }

        // Update SELL panel with dynamic data
        const sellTotalAmountEl = document.getElementById('trSellTotalAmount');
        const sellMatchedAmountEl = document.getElementById('trSellMatchedAmount');
        
        if (sellTotalAmountEl) {
            sellTotalAmountEl.textContent = '$' + (totalSellMatched + totalSellPending).toFixed(2);
        }
        
        if (sellMatchedAmountEl) {
            sellMatchedAmountEl.textContent = '$' + totalSellMatched.toFixed(2);
        }

        // Show/hide bets container and no orders message
        const betsContainer = document.getElementById('trBetsContainer');
        const noOrdersMessage = document.getElementById('trNoOrdersMessage');
        
        if (trMyBets.length > 0) {
            // Show bets container, hide no orders message
            if (betsContainer) betsContainer.classList.remove('hidden');
            if (noOrdersMessage) noOrdersMessage.classList.add('hidden');
            
            // Clear existing bet items
            if (betsContainer) {
                betsContainer.innerHTML = '';
            }
            
            // Render ALL bets (both BUY and SELL)
            trMyBets.forEach(bet => {
                const matchedAmount = parseFloat(bet.matched_amount) || 0;
                const pendingAmount = parseFloat(bet.pending_amount) || 0;
                const totalAmount = parseFloat(bet.amount) || 0;
                const payoutAmount = parseFloat(bet.payout_amount) || 0;
                
                // Create bet item container - Rectangle 672 style
                const betItem = document.createElement('div');
                betItem.className = 'p-3';
                betItem.style.cssText = 'background: #232944; border-radius: 10px; border: 1px solid rgba(255, 255, 255, 0.1);';
                
                // First line: BUY/SELL + Amount
                const firstLine = document.createElement('div');
                firstLine.className = 'flex items-center justify-between mb-2';
                
                const directionText = document.createElement('span');
                directionText.className = bet.direction === 'BUY' 
                    ? 'text-green-500 text-sm font-semibold' 
                    : 'text-red-500 text-sm font-semibold';
                directionText.textContent = bet.direction;
                
                const amountContainer = document.createElement('div');
                amountContainer.className = 'flex items-center gap-1';
                
                const amountText = document.createElement('span');
                amountText.className = 'text-white text-sm';
                amountText.textContent = `$${totalAmount.toFixed(2)}`;
                
                const chevronIcon = document.createElement('svg');
                chevronIcon.className = 'w-4 h-4 text-gray-400';
                chevronIcon.setAttribute('fill', 'none');
                chevronIcon.setAttribute('stroke', 'currentColor');
                chevronIcon.setAttribute('viewBox', '0 0 24 24');
                chevronIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>';
                
                amountContainer.appendChild(amountText);
                amountContainer.appendChild(chevronIcon);
                
                firstLine.appendChild(directionText);
                firstLine.appendChild(amountContainer);
                
                // Second line: Đã khớp BUY/SELL + Matched Amount (only if matched_amount > 0)
                if (matchedAmount > 0) {
                    const secondLine = document.createElement('div');
                    secondLine.className = 'flex items-center justify-between mb-2';
                    
                    const matchedLabel = document.createElement('span');
                    matchedLabel.className = 'text-sm';
                    const matchedLabelText = document.createElement('span');
                    matchedLabelText.className = 'text-gray-400';
                    matchedLabelText.textContent = 'Đã khớp ';
                    const matchedDirection = document.createElement('span');
                    matchedDirection.className = bet.direction === 'BUY' 
                        ? 'text-green-500' 
                        : 'text-red-500';
                    matchedDirection.textContent = bet.direction;
                    matchedLabel.appendChild(matchedLabelText);
                    matchedLabel.appendChild(matchedDirection);
                    
                    const matchedAmountText = document.createElement('span');
                    matchedAmountText.className = 'text-white text-sm';
                    matchedAmountText.textContent = `$${matchedAmount.toFixed(2)}`;
                    
                    secondLine.appendChild(matchedLabel);
                    secondLine.appendChild(matchedAmountText);
                    betItem.appendChild(secondLine);
                }
                
                // Third line: Đang chờ khớp BUY/SELL + Pending Amount (only if pending_amount > 0)
                if (pendingAmount > 0) {
                    const thirdLine = document.createElement('div');
                    thirdLine.className = 'flex items-center justify-between';
                    
                    const pendingLabel = document.createElement('span');
                    pendingLabel.className = 'text-sm';
                    const pendingLabelText = document.createElement('span');
                    pendingLabelText.className = 'text-gray-400';
                    pendingLabelText.textContent = 'Đang chờ khớp ';
                    const pendingDirection = document.createElement('span');
                    pendingDirection.className = bet.direction === 'BUY' 
                        ? 'text-green-500' 
                        : 'text-red-500';
                    pendingDirection.textContent = bet.direction;
                    pendingLabel.appendChild(pendingLabelText);
                    pendingLabel.appendChild(pendingDirection);
                    
                    const pendingAmountText = document.createElement('span');
                    pendingAmountText.className = 'text-white text-sm';
                    pendingAmountText.textContent = `$${pendingAmount.toFixed(2)}`;
                    
                    thirdLine.appendChild(pendingLabel);
                    thirdLine.appendChild(pendingAmountText);
                    betItem.appendChild(thirdLine);
                }
                
                // Fourth line: Thắng/Thua (if bet is finished)
                if (bet.status === 'won') {
                    const fourthLine = document.createElement('div');
                    fourthLine.className = 'flex items-center justify-between mt-2';
                    
                    const wonLabel = document.createElement('span');
                    wonLabel.className = 'text-green-500 text-sm font-semibold';
                    wonLabel.textContent = 'Thắng';
                    
                    const wonAmountText = document.createElement('span');
                    wonAmountText.className = 'text-white text-sm font-semibold';
                    wonAmountText.textContent = `$${payoutAmount.toFixed(2)}`;
                    
                    fourthLine.appendChild(wonLabel);
                    fourthLine.appendChild(wonAmountText);
                    betItem.appendChild(fourthLine);
                } else if (bet.status === 'lost') {
                    const fourthLine = document.createElement('div');
                    fourthLine.className = 'flex items-center justify-between mt-2';
                    
                    const lostLabel = document.createElement('span');
                    lostLabel.className = 'text-red-500 text-sm font-semibold';
                    lostLabel.textContent = 'Thua';
                    
                    fourthLine.appendChild(lostLabel);
                    betItem.appendChild(fourthLine);
                }
                
                // Assemble bet item
                betItem.appendChild(firstLine);
                
                // Add to container
                if (betsContainer) {
                    betsContainer.appendChild(betItem);
                }
            });
        } else {
            // Hide bets container, show no orders message
            if (betsContainer) betsContainer.classList.add('hidden');
            if (noOrdersMessage) noOrdersMessage.classList.remove('hidden');
        }
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
                if (typeof showToast === 'function') {
                    showToast('Thời gian đặt cược đã kết thúc. Chỉ có thể đặt cược trong 55 giây đầu của mỗi phiên.', 'error');
                } else {
                    alert('Thời gian đặt cược đã kết thúc. Chỉ có thể đặt cược trong 55 giây đầu của mỗi phiên.');
                }
                return;
            }
        } else if (!trRoundData || trRoundData.status !== 'running') {
            if (typeof showToast === 'function') {
                showToast('Round chưa bắt đầu hoặc đã kết thúc.', 'error');
            } else {
                alert('Round chưa bắt đầu hoặc đã kết thúc.');
            }
            return;
        }
        
        const amountInput = document.getElementById('trBetAmount');
        if (!amountInput) {
            console.error('Amount input not found');
            return;
        }

        const amount = parseFloat(amountInput.value);
        if (!amount || amount <= 0) {
            if (typeof showToast === 'function') {
                showToast('Vui lòng nhập số tiền hợp lệ', 'error');
            } else {
                alert('Vui lòng nhập số tiền hợp lệ');
            }
            return;
        }

        // Check balance
        const selectedBalance = trSelectedWallet === 'reward' 
            ? trUserBalances.reward_balance 
            : trUserBalances.balance;
        
        if (amount > selectedBalance) {
            if (typeof showToast === 'function') {
                showToast('Số dư không đủ để đặt cược', 'error');
            } else {
                alert('Số dư không đủ để đặt cược');
            }
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
                    wallet_type: trSelectedWallet
                })
            });

            const data = await response.json();

            if (data.success) {
                if (typeof showToast === 'function') {
                    showToast('Đặt cược thành công!', 'success');
                } else {
                    alert('Đặt cược thành công!');
                }
                await updateMyBets();
                await updateRoundData();
            } else {
                if (typeof showToast === 'function') {
                    showToast(data.error || 'Có lỗi xảy ra khi đặt cược', 'error');
                } else {
                    alert(data.error || 'Có lỗi xảy ra khi đặt cược');
                }
            }
        } catch (error) {
            console.error('Error placing bet:', error);
            if (typeof showToast === 'function') {
                showToast('Có lỗi xảy ra khi đặt cược', 'error');
            } else {
                alert('Có lỗi xảy ra khi đặt cược');
            }
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

        // Initialize: fetch round data and start timer
        updateRoundData();
        updateMyBets();
        
        // Update every second
        trTimerInterval = setInterval(function() {
            updateTimer();
            updateRoundData();
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
