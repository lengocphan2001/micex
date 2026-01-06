@extends('layouts.mobile')

@section('title', 'Xanh đỏ 60s - Micex')

@push('styles')
<style>
    .xd-card { background: rgba(17, 17, 17, 0.65); border: 1px solid rgba(255,255,255,0.08); }
    .xd-chip { background: rgba(255,255,255,0.08); }
    .xd-selected { background: rgba(234, 179, 8, 0.3) !important; border: 2px solid rgba(234, 179, 8, 0.8) !important; }
    .xd-num { border: 1px solid rgba(255,255,255,0.08); }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .refresh-spinning { animation: spin 1s linear infinite; }

    /* Hide number input spinner */
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type="number"] {
        -moz-appearance: textfield;
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
    <h1 class="text-white text-base font-semibold">Trò chơi</h1>
    <div class="w-6"></div>
</header>
@endsection

@section('content')
<div class="">
    <div class="flex items-center justify-between p-4">
        <div class="flex items-center gap-2">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full xd-chip">
                    <span class="block w-1.5 h-1.5 bg-white rounded-full"></span>
                    <span class="block w-1.5 h-1.5 bg-white rounded-full ml-1"></span>
                </span>
                <span class="text-white text-xl font-semibold">Xanh đỏ 1 Phút</span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <div class="flex items-center gap-2 rounded-full xd-chip px-3 py-2">
                <button type="button" id="showRecentResultsBtn" class="text-white/80 text-sm cursor-pointer hover:text-white transition-colors">•••</button>
                <span class="w-px h-4 bg-white/15"></span>
                <a href="{{ route('games.index') }}" class="text-white/80 hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

        <div class="xd-card p-4 space-y-3">
            <div class="flex items-start">
             <div class="w-full">
                 <div class="w-full flex items-center justify-between gap-3">
                    <div class="text-white/90 font-medium" id="periodCurrent">No.-</div>
                    <div class="flex items-center gap-2 text-red-400 font-mono">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full xd-chip text-white/80">⏱</span>
                        <span id="countdownText">00:00:00</span>
                    </div>
                </div>
                
                 <div class="w-full flex items-center justify-between gap-3">
                    <div class="text-white/60 font-medium" id="periodPrev">No.-</div>
                    <div class="flex items-center gap-2">
                        <span class="text-white text-lg font-semibold" id="lastResultLabel">-</span>
                        <span class="w-3 h-3 rounded-full bg-gray-500 cursor-pointer" id="lastResultDot"></span>
                    </div>
                </div>
            </div>
            
        </div>

        <div class="text-white/75 text-xs leading-relaxed">
            Đoán giá trị của 0-9, 1, 3, 5, 7, 9 là màu xanh, 2, 4, 6, 8 là màu đỏ và 0 (tím + đỏ) hoặc 5 (tím + xanh).
        </div>

        <div class="grid grid-cols-3 gap-3 pt-2">
            <button type="button" data-pick="do" class="xd-card rounded-xl p-3 text-center" id="pickDo">
                <div class="text-white font-semibold">Đỏ</div>
                <div class="text-white/60 text-xs mt-1" id="rateDo">1.95x</div>
            </button>
            <button type="button" data-pick="tim" class="xd-card rounded-xl p-3 text-center" id="pickTim">
                <div class="text-white font-semibold">Tím</div>
                <div class="text-white/60 text-xs mt-1" id="rateTim">4.87x</div>
            </button>
            <button type="button" data-pick="xanh" class="xd-card rounded-xl p-3 text-center" id="pickXanh">
                <div class="text-white font-semibold">Xanh</div>
                <div class="text-white/60 text-xs mt-1" id="rateXanh">1.95x</div>
            </button>
        </div>

        <div class="grid grid-cols-5 gap-3 pt-2" id="numberGrid">
            <!-- numbers injected by JS -->
        </div>
    </div>

    <div class="xd-card p-4 space-y-3">
        <!-- Balance (like explore) -->
        <div class="flex items-center gap-4">
            <div class="flex items-center justify-center gap-2">
                <p class="text-[#FFFFFFB2] text-[14px] font-medium">Số dư:</p>
                <span id="balanceText" class="text-white text-[16px] font-medium">0.00</span>
                <span class="text-[#FFFFFFB2] text-[14px] font-medium">USDT</span>
            </div>
            <button type="button" id="xdRefreshBalanceBtn" class="text-center cursor-pointer hover:opacity-80 transition-opacity">
                <svg id="xdRefreshBalanceIcon" xmlns="http://www.w3.org/2000/svg" width="15" height="16" viewBox="0 0 15 16" fill="none" class="transition-transform duration-300">
                    <path d="M1.56689 11.1755C1.81326 11.5861 2.11437 11.9693 2.45655 12.3115C4.975 14.83 9.06747 14.83 11.5996 12.3115C12.6261 11.285 13.2147 9.98464 13.4063 8.65698" stroke="#707797" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M0.649902 6.82298C0.841523 5.48164 1.43008 4.19498 2.45662 3.16844C4.97507 0.64999 9.06754 0.64999 11.5997 3.16844C11.9555 3.5243 12.243 3.90757 12.4893 4.3045" stroke="#707797" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M1.30664 14.8299V11.1755H4.9611" stroke="#707797" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12.7492 0.650024V4.30449H9.09473" stroke="#707797" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>

        <div class="xd-card rounded-xl p-4 space-y-3">
            <div class="flex items-center gap-3">
                <div class="flex-1 min-w-0 px-3 flex items-center"
                     style="height: 47px; border-radius: 5px; border: 0.5px solid rgba(255, 255, 255, 0.5);">
                    <input id="betAmount" type="number" min="0.01" step="0.01"
                           class="w-full bg-transparent text-white outline-none placeholder-white/30"
                           placeholder="Giá trị" value="1">
                </div>
                <button type="button" id="btnDiv3" class="xd-chip rounded-lg px-4 py-2 text-white font-semibold flex-shrink-0" style="height: 47px;">/3</button>
                <button type="button" id="btnMul3" class="xd-chip rounded-lg px-4 py-2 text-white font-semibold flex-shrink-0" style="height: 47px;">x3</button>
            </div>
            <div class="text-white/70" id="calcText">0 × 0 = 0.00 USDT</div>
        </div>

        <button type="button" id="confirmBtn"
                class="w-full bg-[#3958F5] hover:bg-[#2f49c8] text-white font-semibold py-3 rounded-full">
            Xác nhận
        </button>
    </div>
</div>

<!-- Recent Results Popup -->
<div id="recentResultsPopup" class="fixed inset-0 z-50 hidden items-start justify-center bg-black/50 backdrop-blur-sm pt-6" style="display: none;">
    <div class="w-full max-w-md bg-gray-900 shadow-2xl max-h-[80vh] flex flex-col">
        <div class="flex items-center justify-between p-4 border-b border-gray-800">
            <h3 class="text-white text-lg font-semibold">Kết quả gần nhất</h3>
            <button type="button" id="closeRecentResults" class="text-white/60 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-4">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-800">
                            <th class="text-left py-3 px-2 text-white/70 font-medium">No.</th>
                            <th class="text-left py-3 px-2 text-white/70 font-medium">Thời gian</th>
                            <th class="text-left py-3 px-2 text-white/70 font-medium">Kết quả</th>
                            <th class="text-left py-3 px-2 text-white/70 font-medium">Màu sắc</th>
                        </tr>
                    </thead>
                    <tbody id="recentResultsBody" class="text-white">
                        <!-- Results will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Winning Result Popup -->
<div id="winningResultPopup" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70" style="display: none;">
    <div class="relative w-full max-w-sm mx-4">
        <button type="button" id="closeWinningResult" class="absolute top-2 right-2 z-10 w-8 h-8 rounded-full bg-[#2F2F5C] flex items-center justify-center text-white hover:bg-[#3F3F6C] transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <div class="relative">
            <img src="{{ asset('images/xanhdoresult1.png') }}" alt="Kết quả thắng" class="w-full h-auto">
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="text-center" style="margin-top: 20%;">
                    <div id="winningAmountText" class="text-green-500 font-bold text-4xl" style="text-shadow: 0 0 10px rgba(34, 197, 94, 0.5);">
                        + 0$
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const XD_BASE_TIME = new Date('2025-01-01T00:00:00Z').getTime();
    const XD_ROUND_DURATION = 60;

    let xdSelectedPicks = []; // Array of selected picks: ['do', 'xanh', 'tim']
    let xdSelectedNumbers = []; // Array of selected numbers: [0, 1, 2, ...]
    let xdMyBets = []; // Array of bets for current round
    let xdLastRoundNumberSeen = null;
    let xdRoundFlipRetryTimer = null;
    let xdLastCheckedRound = null; // Track which round we've already checked for winnings

    function xdCalculateRoundNumber() {
        const now = Date.now();
        const elapsed = Math.floor((now - XD_BASE_TIME) / 1000);
        return Math.floor(elapsed / XD_ROUND_DURATION) + 1;
    }

    function xdCalculateRoundDeadline(roundNumber) {
        const roundStartTime = XD_BASE_TIME + ((roundNumber - 1) * XD_ROUND_DURATION * 1000);
        return roundStartTime + (XD_ROUND_DURATION * 1000);
    }

    function xdPickToGemType(pick) {
        if (pick === 'xanh') return 'kcxanh';
        if (pick === 'do') return 'kcdo';
        return 'daquy'; // tim
    }

    function xdNumberToPick(n) {
        // 0: tím + đỏ
        if (n === 0) return 'tim'; // Can bet on both tím and đỏ
        // 5: tím + xanh
        if (n === 5) return 'tim'; // Can bet on both tím and xanh
        // 1, 3, 5, 7, 9: xanh
        if ([1, 3, 5, 7, 9].includes(n)) return 'xanh';
        // 0, 2, 4, 6, 8: đỏ
        if ([0, 2, 4, 6, 8].includes(n)) return 'do';
        return 'do'; // default
    }
    
    function xdNumberToWinningGems(n) {
        // Returns array of winning gem types for a number
        const winning = [];
        if (n === 0) {
            winning.push('daquy', 'kcdo'); // tím + đỏ
        } else if (n === 5) {
            winning.push('daquy', 'kcxanh'); // tím + xanh
        } else if ([1, 3, 7, 9].includes(n)) {
            winning.push('kcxanh'); // xanh
        } else if ([2, 4, 6, 8].includes(n)) {
            winning.push('kcdo'); // đỏ
        }
        return winning;
    }

    function xdTogglePick(pick) {
        const index = xdSelectedPicks.indexOf(pick);
        if (index > -1) {
            // Deselect
            xdSelectedPicks.splice(index, 1);
            const btn = document.querySelector(`[data-pick="${pick}"]`);
            if (btn) btn.classList.remove('xd-selected');
        } else {
            // Select
            xdSelectedPicks.push(pick);
            const btn = document.querySelector(`[data-pick="${pick}"]`);
            if (btn) btn.classList.add('xd-selected');
        }
        xdUpdateCalc();
    }

    function xdToggleNumber(n) {
        const index = xdSelectedNumbers.indexOf(n);
        if (index > -1) {
            // Deselect
            xdSelectedNumbers.splice(index, 1);
            const el = document.querySelector(`[data-num="${n}"]`);
            if (el) el.classList.remove('xd-selected');
        } else {
            // Select
            xdSelectedNumbers.push(n);
            const el = document.querySelector(`[data-num="${n}"]`);
            if (el) el.classList.add('xd-selected');
            // Don't auto-select color pick
        }
        xdUpdateCalc();
    }

    function xdUpdateCalc() {
        const amount = parseFloat(document.getElementById('betAmount')?.value || '0') || 0;
        // Count total selections (numbers + picks, but avoid duplicates)
        const totalSelections = xdSelectedNumbers.length + xdSelectedPicks.length;
        const totalAmount = totalSelections * amount;
        const calcEl = document.getElementById('calcText');
        if (calcEl) {
            if (totalSelections === 0) {
                calcEl.textContent = '0 × ' + amount.toFixed(2) + ' = 0.00 USDT';
            } else {
                calcEl.textContent = `${totalSelections} × ${amount.toFixed(2)} = ${totalAmount.toFixed(2)} USDT`;
            }
        }
    }

    async function xdLoadRates() {
        try {
            const res = await fetch('{{ route('xanhdo.gem-types') }}', { headers: { 'Accept': 'application/json' }});
            if (!res.ok) return;
            const list = await res.json();
            if (!Array.isArray(list)) return;
            const map = Object.fromEntries(list.map(x => [x.type, x.payout_rate]));
            const rateDo = document.getElementById('rateDo');
            const rateXanh = document.getElementById('rateXanh');
            const rateTim = document.getElementById('rateTim');
            if (rateDo && map.kcdo) rateDo.textContent = `${Number(map.kcdo).toFixed(2)}x`;
            if (rateXanh && map.kcxanh) rateXanh.textContent = `${Number(map.kcxanh).toFixed(2)}x`;
            if (rateTim && map.daquy) rateTim.textContent = `${Number(map.daquy).toFixed(2)}x`;
        } catch (e) {
            // ignore
        }
    }

    async function xdLoadBalanceAndLastResult() {
        // Balance and current bets
        try {
            const res = await fetch('{{ route('xanhdo.my-bet') }}', { headers: { 'Accept': 'application/json' }});
            if (res.ok) {
                const data = await res.json();
                if (data && data.balance !== undefined) {
                    const el = document.getElementById('balanceText');
                    if (el) el.textContent = Number(data.balance).toFixed(2);
                }
                // Load current round bets
                if (data && data.bets && Array.isArray(data.bets)) {
                    xdMyBets = data.bets;
                    xdUpdateBetsDisplay();
                }
            }
        } catch (e) {}

        // Last result (previous round)
        const roundNumber = xdCalculateRoundNumber();
        const prev = roundNumber - 1;
        if (prev < 1) return;
        try {
            const res = await fetch(`{{ route('xanhdo.round-result') }}?round_number=${prev}`, { headers: { 'Accept': 'application/json' }});
            if (!res.ok) return;
            const data = await res.json();
            // For displaying historical result, final_result is the source of truth.
            // admin_set_result can differ due to timing/race (admin set may not be applied).
            const result = data && (data.final_result ?? data.result ?? data.admin_set_result);
            const label = document.getElementById('lastResultLabel');
            const dot = document.getElementById('lastResultDot');
            if (result === null || result === undefined || result === '' || !label || !dot) return;

            // Check if result is a number (0-9) for xanhdo
            const resultNum = parseInt(result);
            if (!isNaN(resultNum) && resultNum >= 0 && resultNum <= 9) {
                // Display the number and its color
                label.textContent = resultNum;
                const winningGems = xdNumberToWinningGems(resultNum);
                if (winningGems.includes('daquy')) {
                    dot.className = 'w-3 h-3 rounded-full bg-purple-400';
                } else if (winningGems.includes('kcxanh')) {
                    dot.className = 'w-3 h-3 rounded-full bg-green-400';
                } else if (winningGems.includes('kcdo')) {
                    dot.className = 'w-3 h-3 rounded-full bg-red-400';
                }
            } else {
                // Legacy gem result
                if (result === 'kcxanh') {
                    label.textContent = 'Xanh';
                    dot.className = 'w-3 h-3 rounded-full bg-green-400';
                } else if (result === 'kcdo') {
                    label.textContent = 'Đỏ';
                    dot.className = 'w-3 h-3 rounded-full bg-red-400';
                } else {
                    label.textContent = 'Tím';
                    dot.className = 'w-3 h-3 rounded-full bg-purple-400';
                }
            }
        } catch (e) {}
    }
    
    function xdUpdateBetsDisplay() {
        // Update UI to show current bets (optional - can show list of bets)
        // For now, just keep the selection UI
    }

    async function xdLoadRecentResults() {
        try {
            const res = await fetch('{{ route('xanhdo.recent-results') }}', { headers: { 'Accept': 'application/json' }});
            if (!res.ok) return;
            const results = await res.json();
            if (!Array.isArray(results)) return;
            
            const tbody = document.getElementById('recentResultsBody');
            if (!tbody) return;
            
            tbody.innerHTML = '';
            
            results.forEach(item => {
                const row = document.createElement('tr');
                row.className = 'border-b border-gray-800';
                
                // Round number
                const tdNo = document.createElement('td');
                tdNo.className = 'py-3 px-2';
                tdNo.textContent = item.round_number || '-';
                
                // Time
                const tdTime = document.createElement('td');
                tdTime.className = 'py-3 px-2';
                tdTime.textContent = item.time || '-';
                
                // Result (number)
                const tdResult = document.createElement('td');
                tdResult.className = 'py-3 px-2';
                tdResult.textContent = item.result !== null && item.result !== undefined ? item.result : '-';
                
                // Colors
                const tdColors = document.createElement('td');
                tdColors.className = 'py-3 px-2';
                const colorsContainer = document.createElement('div');
                colorsContainer.className = 'flex items-center gap-1';
                
                if (Array.isArray(item.winning_colors) && item.winning_colors.length > 0) {
                    item.winning_colors.forEach(color => {
                        const dot = document.createElement('span');
                        dot.className = 'w-3 h-3 rounded-full';
                        if (color === 'daquy') {
                            dot.className += ' bg-purple-400';
                        } else if (color === 'kcxanh') {
                            dot.className += ' bg-green-400';
                        } else if (color === 'kcdo') {
                            dot.className += ' bg-red-400';
                        } else {
                            dot.className += ' bg-gray-500';
                        }
                        colorsContainer.appendChild(dot);
                    });
                }
                
                tdColors.appendChild(colorsContainer);
                
                row.appendChild(tdNo);
                row.appendChild(tdTime);
                row.appendChild(tdResult);
                row.appendChild(tdColors);
                
                tbody.appendChild(row);
            });
        } catch (e) {
            console.error('Error loading recent results:', e);
        }
    }

    function xdShowRecentResults() {
        const popup = document.getElementById('recentResultsPopup');
        if (!popup) return;
        popup.style.display = 'flex';
        xdLoadRecentResults();
    }

    function xdHideRecentResults() {
        const popup = document.getElementById('recentResultsPopup');
        if (!popup) return;
        popup.style.display = 'none';
    }

    async function xdCheckRoundWinnings(roundNumber) {
        // Only check once per round
        if (xdLastCheckedRound === roundNumber) return;
        
        try {
            const res = await fetch(`{{ route('xanhdo.round-winnings') }}?round_number=${roundNumber}`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return;
            
            const data = await res.json();
            if (data && data.has_winnings && data.total_winnings > 0) {
                xdLastCheckedRound = roundNumber;
                xdShowWinningResult(data.total_winnings);
            }
        } catch (e) {
            console.error('Error checking round winnings:', e);
        }
    }

    function xdShowWinningResult(amount) {
        const popup = document.getElementById('winningResultPopup');
        const amountText = document.getElementById('winningAmountText');
        if (!popup || !amountText) return;
        
        // Format amount: + 19$ or + 19.50$
        const formattedAmount = amount % 1 === 0 
            ? `+ ${amount}$` 
            : `+ ${amount.toFixed(2)}$`;
        
        amountText.textContent = formattedAmount;
        popup.style.display = 'flex';
    }

    function xdHideWinningResult() {
        const popup = document.getElementById('winningResultPopup');
        if (!popup) return;
        popup.style.display = 'none';
    }

    function xdTick() {
        const roundNumber = xdCalculateRoundNumber();
        const deadline = xdCalculateRoundDeadline(roundNumber);
        const now = Date.now();
        const remainingSeconds = Math.max(0, Math.floor((deadline - now) / 1000));

        const mm = String(Math.floor(remainingSeconds / 60)).padStart(2, '0');
        const ss = String(remainingSeconds % 60).padStart(2, '0');
        const cd = document.getElementById('countdownText');
        if (cd) cd.textContent = `00:${mm}:${ss}`;

        const cur = document.getElementById('periodCurrent');
        const prev = document.getElementById('periodPrev');
        if (cur) cur.textContent = `No.${roundNumber}`;
        if (prev) prev.textContent = `No.${Math.max(1, roundNumber - 1)}`;

        // Immediate refresh when round flips (avoid waiting for 15s polling)
        if (xdLastRoundNumberSeen === null) {
            xdLastRoundNumberSeen = roundNumber;
        } else if (roundNumber !== xdLastRoundNumberSeen) {
            const prevRound = xdLastRoundNumberSeen;
            xdLastRoundNumberSeen = roundNumber;
            // Clear pending retries
            if (xdRoundFlipRetryTimer) {
                clearTimeout(xdRoundFlipRetryTimer);
                xdRoundFlipRetryTimer = null;
            }
            // Fetch immediately + retry a few times to catch backend finishing timing
            const runRefresh = async (attempt = 0) => {
                await xdLoadBalanceAndLastResult();
                // Check for winnings from previous round (with delay to allow backend to process)
                if (attempt >= 2 && prevRound > 0) {
                    // Wait a bit more for backend to finish processing bets
                    setTimeout(() => xdCheckRoundWinnings(prevRound), 500);
                }
                if (attempt < 4) {
                    xdRoundFlipRetryTimer = setTimeout(() => runRefresh(attempt + 1), 800);
                }
            };
            runRefresh(0);
        }
    }

    async function xdSubmitBet() {
        const amount = parseFloat(document.getElementById('betAmount')?.value || '0');
        if (!amount || amount <= 0) {
            if (window.showToast) window.showToast('Vui lòng nhập giá trị hợp lệ', 'error');
            return;
        }
        
        // Collect all selections - each number and each pick is a separate bet
        const selections = [];
        
        // Add picks (colors) - each pick is a separate bet
        xdSelectedPicks.forEach(pick => {
            const gemType = xdPickToGemType(pick);
            selections.push({
                gem_type: gemType,
                bet_type: 'color',
                bet_value: null
            });
        });
        
        // Add numbers - convert each number to its corresponding gem type
        // Note: Each number selection is a separate bet
        xdSelectedNumbers.forEach(n => {
            const pick = xdNumberToPick(n);
            const gemType = xdPickToGemType(pick);
            selections.push({
                gem_type: gemType,
                bet_type: 'number',
                bet_value: String(n)
            });
        });
        
        if (selections.length === 0) {
            if (window.showToast) window.showToast('Vui lòng chọn ít nhất một lựa chọn', 'error');
            return;
        }

        const confirmBtn = document.getElementById('confirmBtn');
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.classList.add('opacity-60');
            confirmBtn.textContent = 'Đang xử lý...';
        }

        try {
            // Submit all bets sequentially
            let successCount = 0;
            let errorCount = 0;
            const errors = [];
            
            for (let i = 0; i < selections.length; i++) {
                const selection = selections[i];
                try {
                    const response = await window.csrfFetch('{{ route('xanhdo.bet') }}', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: JSON.stringify({
                            gem_type: selection.gem_type,
                            amount: amount,
                            bet_type: selection.bet_type,
                            bet_value: selection.bet_value
                        }),
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        successCount++;
                    } else {
                        errorCount++;
                        const errorMsg = data.error || data.message || 'Lỗi không xác định';
                        errors.push(errorMsg);
                        console.error(`Bet ${i + 1} failed:`, errorMsg);
                    }
                } catch (e) {
                    errorCount++;
                    errors.push(e.message || 'Lỗi kết nối');
                    console.error(`Bet ${i + 1} exception:`, e);
                }
            }

            if (successCount > 0) {
                if (window.showToast) {
                    if (errorCount === 0) {
                        window.showToast(`Đặt cược thành công ${successCount} lựa chọn!`, 'success');
                    } else {
                        window.showToast(`Đặt cược thành công ${successCount}/${selections.length} lựa chọn. ${errorCount} lựa chọn thất bại.`, 'warning');
                        console.log('Errors:', errors);
                    }
                }
                // Clear selection after successful bet
                xdSelectedPicks = [];
                xdSelectedNumbers = [];
                document.querySelectorAll('[data-pick]').forEach(btn => btn.classList.remove('xd-selected'));
                document.querySelectorAll('[data-num]').forEach(el => el.classList.remove('xd-selected'));
                xdUpdateCalc();
                await xdLoadBalanceAndLastResult();
            } else {
                const errorMsg = errors.length > 0 ? errors[0] : 'Không thể đặt cược. Vui lòng thử lại.';
                if (window.showToast) window.showToast(errorMsg, 'error');
            }
        } catch (e) {
            console.error('Submit bet error:', e);
            if (window.showToast) window.showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
        } finally {
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.classList.remove('opacity-60');
                confirmBtn.textContent = 'Xác nhận';
            }
        }
    }

    function xdBuildNumberGrid() {
        const grid = document.getElementById('numberGrid');
        if (!grid) return;
        grid.innerHTML = '';
        for (let n = 0; n <= 9; n++) {
            const pick = xdNumberToPick(n);
            const bg =
                (pick === 'xanh') ? 'bg-green-500/90' :
                (pick === 'do') ? 'bg-red-500/90' :
                'bg-purple-600/90';
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.dataset.num = String(n);
            btn.className = `xd-num rounded-sm py-2 text-center text-white font-semibold ${bg}`;
            btn.innerHTML = `<div class="text-base">${n}</div><div class="text-xs text-white/80 mt-1">9.75x</div>`;
            btn.addEventListener('click', () => xdToggleNumber(n));
            grid.appendChild(btn);
        }
    }

    document.addEventListener('DOMContentLoaded', async () => {
        xdBuildNumberGrid();

        document.querySelectorAll('[data-pick]').forEach(btn => {
            btn.addEventListener('click', () => {
                xdTogglePick(btn.dataset.pick);
            });
        });

        document.getElementById('betAmount')?.addEventListener('input', xdUpdateCalc);
        document.getElementById('btnDiv3')?.addEventListener('click', () => {
            const el = document.getElementById('betAmount');
            if (!el) return;
            const v = parseFloat(el.value || '0') || 0;
            el.value = String(Math.max(0.01, v / 3).toFixed(2));
            xdUpdateCalc();
        });
        document.getElementById('btnMul3')?.addEventListener('click', () => {
            const el = document.getElementById('betAmount');
            if (!el) return;
            const v = parseFloat(el.value || '0') || 0;
            el.value = String(Math.max(0.01, v * 3).toFixed(2));
            xdUpdateCalc();
        });
        document.getElementById('confirmBtn')?.addEventListener('click', xdSubmitBet);

        // Refresh balance button (like explore)
        document.getElementById('xdRefreshBalanceBtn')?.addEventListener('click', async () => {
            const icon = document.getElementById('xdRefreshBalanceIcon');
            if (icon) icon.classList.add('refresh-spinning');
            try {
                await xdLoadBalanceAndLastResult();
            } finally {
                if (icon) icon.classList.remove('refresh-spinning');
            }
        });
        
        // Recent results popup
        document.getElementById('lastResultDot')?.addEventListener('click', xdShowRecentResults);
        document.getElementById('showRecentResultsBtn')?.addEventListener('click', xdShowRecentResults);
        document.getElementById('closeRecentResults')?.addEventListener('click', xdHideRecentResults);
        document.getElementById('recentResultsPopup')?.addEventListener('click', (e) => {
            if (e.target.id === 'recentResultsPopup') {
                xdHideRecentResults();
            }
        });

        // Winning result popup
        document.getElementById('closeWinningResult')?.addEventListener('click', xdHideWinningResult);
        document.getElementById('winningResultPopup')?.addEventListener('click', (e) => {
            if (e.target.id === 'winningResultPopup') {
                xdHideWinningResult();
            }
        });

        // Don't auto-select, let user choose
        await xdLoadRates();
        await xdLoadBalanceAndLastResult();
        xdTick();
        setInterval(xdTick, 1000);
        setInterval(xdLoadBalanceAndLastResult, 15000);
    });
</script>
@endpush


