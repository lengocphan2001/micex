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

    #trChart {
        width: 100%;
        height: 55vh;
        min-height: 450px;
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
            height: 50vh;
            min-height: 400px;
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
    <div id="trChart" class="flex-shrink-0" style="height: 55vh; min-height: 450px;"></div>

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
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-gray-800 rounded-lg p-3">
                    <input type="number" id="trBetAmount" value="10" min="0.01" step="0.01" 
                           class="w-full bg-transparent text-white text-base outline-none" placeholder="$10">
                </div>
                <div class="bg-gray-800 rounded-lg p-3 flex items-center justify-center">
                    <span id="trTimer" class="text-white text-base font-mono">00:00:29</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-2 gap-3">
                <button id="trPutBtn" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-3 rounded-lg text-base transition-colors">Sell</button>
                <button id="trCallBtn" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 rounded-lg text-base transition-colors">Buy</button>
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
                        <div class="flex items-center gap-1">
                            <span class="text-white text-sm">$100</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-green-500 text-xs">Đã khớp BUY</span>
                        <span class="text-white text-sm">$100</span>
                    </div>
                </div>

                <!-- SELL Panel -->
                <div class="bg-gray-800 rounded-lg p-3 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-red-500 text-sm font-semibold">SELL</span>
                        <div class="flex items-center gap-1">
                            <span class="text-white text-sm">$100</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-red-500 text-xs">Đã khớp SELL</span>
                        <span class="text-white text-sm">$100</span>
                    </div>
                </div>
            </div>

            <!-- Current Bet Status -->
            <div class="bg-gray-800 rounded-lg p-3 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-green-500 text-sm font-semibold">Đặt cược BUY</span>
                    <span class="text-white text-sm">$100/50</span>
                </div>

                <!-- Bet Status Indicators -->
                <div class="space-y-2">
                    <!-- Đã cược -->
                    <div class="bg-green-600 rounded-lg p-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-white text-sm">Đã cược: 50$</span>
                    </div>

                    <!-- Đang chờ khớp -->
                    <div class="bg-amber-500 rounded-lg p-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                        <span class="text-white text-sm">Đang chờ khớp: 50$</span>
                    </div>
                </div>
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
                    "paneProperties.legendProperties.showStudyTitle": false,
                    "paneProperties.legendProperties.showStudyValues": false,
                    "paneProperties.legendProperties.showSeriesLastValue": false,
                    "paneProperties.legendProperties.showStudyArguments": false
                },
                "hide_top_toolbar": true,
                "hide_side_toolbar": true,
                "hide_volume": false
            });
        }
    }

    /* ================== TAB SWITCHING (UI Only) ================== */
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
        }

        if (tabBuySell) {
            tabBuySell.addEventListener('click', function() {
                switchTab(tabBuySell, contentBuySell);
            });
        }

        if (tabOrder) {
            tabOrder.addEventListener('click', function() {
                switchTab(tabOrder, contentOrder);
            });
        }

        if (tabEntry) {
            tabEntry.addEventListener('click', function() {
                switchTab(tabEntry, contentEntry);
            });
        }
    });
</script>
@endpush
