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
        body,
        html {
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
        #trChart>div>div:first-child,
        #trChart iframe+div,
        iframe[src*="tradingview"]+div,
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
        #trChart>div,
        #trChart>div>div,
        #trChart>div>div>div {
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
        <!-- Balance Header (Moved & Redesigned) -->
        <div class="px-4 pt-4 pb-2 bg-[#0b0b0b] relative z-20">
            <div class="flex flex-col gap-2 relative w-full">
                <!-- Wallet Name Label -->
                <div class="flex items-center gap-2">
                    <span class="text-[#64748b] text-sm font-medium" id="trWalletName">Ví chính</span>
                    <!-- Optional Refresh Button (Small/Subtle) -->
                    <button type="button" id="trRefreshBalanceBtn" class="text-white hover:text-gray-300 transition-colors">
                        <svg id="trRefreshBalanceIcon" class="w-4 h-4 transition-transform duration-700" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                </div>

                <!-- Balance + Dropdown Trigger -->
                <div class="flex items-center gap-2 cursor-pointer w-fit group" id="trWalletToggleBtn">
                    <span id="trTotalBalanceText"
                        class="text-white text-[24px] font-bold leading-none tracking-tight">$0.00</span>
                    <div
                        class="w-5 h-5 rounded-full border-1 border-white flex items-center justify-center transition-transform group-hover:border-white/50">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

                <!-- Dropdown Menu (Absolute) -->
                <div id="trWalletDropdown"
                    class="hidden absolute left-0 top-full mt-2 z-50 w-64 bg-[#1e1e2d] border border-gray-800 rounded-xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200 origin-top-left">
                    <div class="p-2 space-y-1">
                        <button type="button" data-wallet="deposit"
                            class="w-full flex items-center gap-3 px-3 py-3 text-left text-white bg-white/5 rounded-lg transition-colors tr-wallet-option group">
                            <div
                                class="w-8 h-8 rounded-full bg-green-500/20 text-green-500 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-medium">Ví chính</span>
                                <span class="text-xs text-gray-500">Tài khoản thực</span>
                            </div>
                        </button>

                        <button type="button" data-wallet="reward"
                            class="w-full flex items-center gap-3 px-3 py-3 text-left text-gray-400 hover:bg-white/5 rounded-lg transition-colors tr-wallet-option group">
                            <div
                                class="w-8 h-8 rounded-full bg-yellow-500/20 text-yellow-500 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                                </svg>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-medium">Ví thưởng</span>
                                <span class="text-xs text-gray-500">Khuyến mãi</span>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Area -->
        <div id="trChart" class="flex-shrink-0" style="height: 42vh; min-height: 330px;"></div>

        <!-- Bottom Trading Controls -->
        <div class="px-4 py-4 space-y-3">
            <!-- Navigation Tabs -->
            <div class="flex items-center justify-center gap-0 w-full"
                style="height: 41px; border-radius: 20px; background: #0a0a0a; position: relative; padding: 2px;">
                <button id="trTabBuySell"
                    class="flex-1 h-full bg-gray-700 rounded-[18px] text-white text-sm font-semibold transition-all">BUY/SELL</button>
                <button id="trTabOrder"
                    class="flex-1 h-full rounded-[18px] text-gray-400 text-sm transition-all">Order</button>
            </div>

            <!-- BUY/SELL Tab Content -->
            <div id="trTabContentBuySell" class="tab-content space-y-3">
                <!-- Balance and Wallet Selection -->


                <!-- Buy/Sell Sentiment Bar (tổng bet tất cả user - cập nhật từ API statistics) -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span id="trSellPercentText" class="text-white">0% Sell</span>
                        <span id="trBuyPercentText" class="text-white">0% Buy</span>
                    </div>
                    <div class="h-2 bg-[#232944] rounded-full overflow-hidden flex gap-0.5">
                        <div id="trSellBar" class="bg-red-500 h-full rounded-l-full flex-shrink-0" style="width: 24%;">
                        </div>
                        <div id="trBuyBar" class="bg-green-500 h-full rounded-r-full flex-shrink-0" style="width: 76%;">
                        </div>
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
                    <button id="trPutBtn" type="button"
                        class="bg-red-500 hover:bg-red-600 text-white font-semibold py-3 rounded-lg text-base transition-colors flex-[1] cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">Sell</button>
                    <button id="trCallBtn" type="button"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 rounded-lg text-base transition-colors flex-[2] cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">Buy</button>
                </div>
            </div>

            <!-- Order Tab Content -->
            <div id="trTabContentOrder" class="tab-content hidden flex flex-col"
                style="max-height: calc(100vh - 200px); overflow-y: auto;">
                <div class="space-y-3 flex-1">
                    <div id="trAllBetsContainer" class="space-y-2">
                        <!-- All bet items will be dynamically inserted here -->
                    </div>

                    <!-- No Orders Message -->
                    <div id="trNoOrdersMessage" class="bg-[#232944] rounded-lg p-6 text-center hidden">
                        <p class="text-gray-400 text-sm">Chưa có đơn đặt cược nào</p>
                    </div>
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

                            <button id="trSaveAdmin" class="tr-bet-btn" style="background: #666; margin-top: 12px;">Lưu cài
                                đặt</button>
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
                        onReady: function (callback) {
                            callback({
                                supported_resolutions: ["1", "5", "15", "30", "60", "240", "D"],
                                supports_marks: false,
                                supports_timescale_marks: false,
                                supports_time: false
                            });
                        },
                        searchSymbols: function () { },
                        resolveSymbol: function (symbolName, onSymbolResolvedCallback) {
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
                        getBars: function (symbolInfo, resolution, from, to, onHistoryCallback, onErrorCallback) {
                            onHistoryCallback([], { noData: true });
                        },
                        subscribeBars: function () { },
                        unsubscribeBars: function () { }
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

        // Cập nhật tab Entry: Removed
        // function updateEntryTabPrices() {}

        // Helper function to update current price on all cards
        function updateCardsCurrentPrice() {
            const priceEls = document.querySelectorAll('.tr-card-current-price');
            const priceStr = trCurrentBtcPrice != null ? formatPrice(trCurrentBtcPrice) : '--';
            priceEls.forEach(el => {
                el.textContent = priceStr;
            });
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

            balanceEl.textContent = '$' + selectedBalance.toFixed(2);
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

        // Create bet item (Full Card Layout)
        function createBetItem(bet) {
            const matchedAmount = parseFloat(bet.matched_amount) || 0;
            const pendingAmount = parseFloat(bet.pending_amount) || 0;
            const amount = parseFloat(bet.amount) || 0;
            // Payout calculation: if won, use payout_amount; else show potential profit (e.g. 95% of matched)
            // But from image, it shows "+19,5" for profit. If pending/running, maybe show potential?
            // Let's assume: if status won -> payout; if running -> potential on matched? 
            // For simplicity/safety: if won, show payout. If pending, show '0' or '--'?
            // Image shows "+19,5" which looks like profit.
            // Let's use potential profit for matched amount if not finished? 
            // Actually image shows status text like "Lợi nhuận +19,5".

            let profitText = '--';
            let profitClass = 'text-white';

            if (bet.status === 'won') {
                const profit = parseFloat(bet.payout_amount) || 0;
                profitText = '+' + profit.toFixed(2).replace(/\.00$/, ''); // Remove trailing .00 if needed, image has comma
                profitClass = 'text-green-500';
            } else if (bet.status === 'lost') {
                profitText = '-'; // Or 0
                profitClass = 'text-red-500';
            } else {
                // Running/Pending
                // Potential profit = matched * 0.95 (rate) ?
                // For now let's just show current payout if any or '--'
                if (matchedAmount > 0) {
                    const potential = matchedAmount * 0.95; // Assuming 95% rate
                    profitText = '+' + potential.toFixed(2).replace(/\.00$/, '');
                    profitClass = 'text-green-500';
                }
            }

            const isBuy = bet.direction === 'BUY';
            const directionColor = isBuy ? 'text-[#00ff9c]' : 'text-[#ff4d4d]';
            const directionText = isBuy ? 'BUY' : 'SELL';

            const card = document.createElement('div');
            card.className = 'bg-[#1e1e2d] rounded-xl p-4 space-y-4'; // Darker card bg
            // Using inline style to match exact image generic dark theme if needed, but Tailwind classes are cleaner.
            // Image looked like #131722 or similar. Using default panel color.

            const refPrice = bet.entry_price ? formatPrice(bet.entry_price) : '--';
            const curPrice = trCurrentBtcPrice ? formatPrice(trCurrentBtcPrice) : '--';

            // Pending dots animation for "..."
            const pendingDots = '<span class="loading-dots">...</span>';

            card.innerHTML = `
                                                <!-- Header -->
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-white font-bold text-base">BTC/USDT</span>
                                                        <span class="text-xs font-bold ${directionColor} bg-${isBuy ? 'green' : 'red'}-500/10 px-1.5 py-0.5 rounded">${directionText}</span>
                                                    </div>
                                                    <span class="text-gray-400 text-sm">Fee <span class="text-green-500">5%</span></span>
                                                </div>

                                                <!-- Grid Stats -->
                                                <div class="grid grid-cols-3 gap-y-4 text-sm">
                                                    <!-- Row 1 -->
                                                    <div>
                                                        <div class="text-gray-400 mb-1">Ký quỹ</div>
                                                        <div class="text-white font-medium">${amount}</div>
                                                    </div>
                                                    <div>
                                                        <div class="text-gray-400 mb-1">Chờ khớp</div>
                                                        <div class="text-white font-medium">${pendingAmount}</div>
                                                    </div>
                                                    <div class="text-right">
                                                        <div class="text-gray-400 mb-1">Đã khớp</div>
                                                        <div class="text-white font-medium">${matchedAmount}</div>
                                                    </div>

                                                    <!-- Row 2 -->
                                                    <div>
                                                        <div class="text-gray-400 mb-1">Giá tham chiếu</div>
                                                        <div class="text-white font-medium flex items-center gap-1">
                                                             <span class="w-2 h-2 rounded-full ${isBuy ? 'bg-green-500' : 'bg-red-500'}"></span>
                                                             ${refPrice}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="text-gray-400 mb-1">Giá hiện tại</div>
                                                        <div class="text-white font-medium tr-card-current-price">${curPrice}</div>
                                                    </div>
                                                    <div class="text-right">
                                                        <div class="text-gray-400 mb-1">Lợi nhuận</div>
                                                        <div class="${profitClass} font-medium">${profitText}</div>
                                                    </div>
                                                </div>

                                                <!-- Footer Message -->
                                                <div class="pt-2 border-t border-gray-700/50 text-center">
                                                    <span class="text-gray-500 text-xs">Số tiền chưa khớp sẽ được hoàn trả sau khi hết thời gian</span>
                                                </div>
                                            `;

            return card;
        }

        // Update Order Tab (modified to remove summary logic and just list bets)
        function updateOrderTab() {
            const orderContent = document.getElementById('trTabContentOrder');
            // Don't check !orderContent hidden status here because we might want to update it in background
            if (!document.getElementById('trAllBetsContainer')) return;

            const allBetsContainer = document.getElementById('trAllBetsContainer');
            const noOrdersMessage = document.getElementById('trNoOrdersMessage');

            // Clear existing bet items is brutal for performance if many, but safe for syncing.
            // A better approach for real-time price might be to just update prices if elements exist, but re-rendering 
            // is acceptable for low frequency updates (3s).
            // However, for "Current Price" updating every 1s, we use updateCardsCurrentPrice helper.

            // Only re-render list if bets changed? 
            // Since this is called from updateMyBets (3s), it's fine.

            allBetsContainer.innerHTML = '';

            if (trMyBets.length > 0) {
                // Sort by ID or time? usually newest first.
                // Assuming API returns sorted or we sort here.
                const sortedBets = [...trMyBets].sort((a, b) => b.id - a.id);

                sortedBets.forEach(bet => {
                    const betItem = createBetItem(bet);
                    allBetsContainer.appendChild(betItem);
                });
                if (noOrdersMessage) noOrdersMessage.classList.add('hidden');
            } else {
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
        document.addEventListener('DOMContentLoaded', function () {
            const tabBuySell = document.getElementById("trTabBuySell");
            const tabOrder = document.getElementById("trTabOrder");

            const contentBuySell = document.getElementById("trTabContentBuySell");
            const contentOrder = document.getElementById("trTabContentOrder");

            function switchTab(activeTab, activeContent) {
                // Reset all tabs
                [tabBuySell, tabOrder].forEach(tab => {
                    if (tab) {
                        tab.classList.remove('bg-gray-700', 'text-white', 'rounded-[18px]');
                        tab.classList.add('text-gray-400');
                    }
                });

                // Reset all content
                [contentBuySell, contentOrder].forEach(content => {
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
                tabBuySell.addEventListener('click', function () {
                    switchTab(tabBuySell, contentBuySell);
                });
            }

            if (tabOrder) {
                tabOrder.addEventListener('click', function () {
                    switchTab(tabOrder, contentOrder);
                    // Update orders when switching to Order tab
                    updateMyBets();
                });
            }


            // Setup bet buttons - ensure they're clickable
            const buyBtn = document.getElementById('trCallBtn');
            const sellBtn = document.getElementById('trPutBtn');

            if (buyBtn) {
                buyBtn.addEventListener('click', function (e) {
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
                sellBtn.addEventListener('click', function (e) {
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

                        // Update Label
                        const walletNameEl = document.getElementById('trWalletName');
                        if (walletNameEl) {
                            walletNameEl.textContent = walletType === 'deposit' ? 'Ví chính' : 'Ví thưởng';
                        }

                        // Update Active Styles
                        walletOptions.forEach(opt => {
                            if (opt.dataset.wallet === walletType) {
                                opt.classList.add('bg-white/5', 'text-white');
                                opt.classList.remove('text-gray-400');
                            } else {
                                opt.classList.remove('bg-white/5', 'text-white');
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
            fetchBtcPriceFromBinance().then(function (p) {
                if (p != null) trCurrentBtcPrice = p;
            });

            // Update every second: timer, round, và giá BTC từ Binance (tab Entry)
            trTimerInterval = setInterval(function () {
                updateTimer();
                updateRoundData();
                fetchBtcPriceFromBinance().then(function (p) {
                    if (p != null) trCurrentBtcPrice = p;
                    updateCardsCurrentPrice();
                });
            }, 1000);

            // Update bets every 3 seconds
            setInterval(function () {
                updateMyBets();
            }, 3000);

            // Mark initial load as complete after first update
            setTimeout(() => {
                trIsInitialLoad = false;
                console.log('Initial load complete, modal can now be shown');
            }, 2000);

            // Test modal function (for debugging - remove in production)
            window.testTradingModal = function (amount = 100) {
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