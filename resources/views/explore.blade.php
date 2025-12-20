@extends('layouts.mobile')

@section('title', 'Khám phá - Micex')

@push('styles')
<style>
    .card-shadow {
        box-shadow: 0 10px 30px rgba(0,0,0,0.25);
    }
    .gem-card {
        transition: all 0.3s ease;
    }
    .gem-card.selected {
        border: 2px solid #3b82f6;
        background: rgba(59, 130, 246, 0.1);
    }
    
    /* Result Popup Animation */
    #resultPopup.show {
        display: flex !important;
    }
    
    #resultPopup.show > div:last-child {
        transform: translateY(0);
        opacity: 1;
    }
</style>
@endpush

@section('header')
<header class="w-full px-4 py-4 flex items-center justify-between bg-gray-900 border-b border-gray-800">
    <div class="flex items-center gap-2">
        <button onclick="history.back()" class="text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <h1 class="text-white text-base font-semibold">Trò Chơi</h1>
    </div>
</header>
@endsection

@section('content')
<div class="px-4 py-4 space-y-4">
    <!-- Top stats -->
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-[#2d59ff] rounded-xl p-2 card-shadow flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="">
                    <i class="fas fa-wallet text-white text-4xl"></i>
                </div>
                <div>
                    <p class="text-xs text-white/90">Đá quý</p>
                    <p class="text-lg font-bold text-white" id="userBalance">{{ number_format(auth()->user()->balance ?? 0, 2, '.', ',') }}$</p>
                </div>
            </div>
            <div class="flex-shrink-0">
                <img src="{{ asset('images/icons/coin_asset.png') }}" alt="Gem" class="pl-2 w-10 h-10 object-contain" style="filter: drop-shadow(0 0 8px rgba(59, 130, 246, 0.6)) drop-shadow(0 0 12px rgba(59, 130, 246, 0.4));">
            </div>
        </div>
        <div class="bg-blue-500 rounded-xl p-2 card-shadow">
            <p class="text-xs text-white/90 mb-2 text-center">Thời gian còn lại để khai thác</p>
            <div class="flex items-center justify-center gap-2">
                <!-- Minutes: First digit -->
                <div class="bg-white text-gray-900 rounded-md w-12 h-8 flex items-center justify-center font-bold text-lg shadow" id="minute1">0</div>
                <!-- Minutes: Second digit -->
                <div class="bg-white text-gray-900 rounded-md w-12 h-8 flex items-center justify-center font-bold text-lg shadow" id="minute2">0</div>
                <!-- Colon separator -->
                <div class="bg-white text-gray-900 rounded-md w-10 h-8 flex items-center justify-center font-bold text-lg shadow">:</div>
                <!-- Seconds: First digit -->
                <div class="bg-white text-gray-900 rounded-md w-12 h-8 flex items-center justify-center font-bold text-lg shadow" id="second1">0</div>
                <!-- Seconds: Second digit -->
                <div class="bg-white text-gray-900 rounded-md w-12 h-8 flex items-center justify-center font-bold text-lg shadow" id="second2">0</div>
            </div>
        </div>
    </div>
    <p style="font-family: Inter; font-weight: 500; font-style: italic; font-size: 14px; line-height: 100%; letter-spacing: 0%;" id="roundNumber">Kỳ số : -</p>

    <!-- Miner Video -->
    <div class="rounded-2xl overflow-hidden card-shadow">
        <video class="object-cover" autoplay loop muted playsinline style="width: 419px; height: 284px; border-radius: 10px; opacity: 1;">
            <source src="{{ asset('videos/mined.mp4') }}" type="video/mp4">
        </video>
    </div>

    <!-- Tabs -->
    <div class="flex items-center gap-8 px-1">
        <button id="tab-search" class="tab-button text-white font-semibold border-b-2 border-blue-500 pb-2" onclick="switchTab('search')">Search</button>
        <button id="tab-signal" class="tab-button text-gray-400 font-semibold pb-2" onclick="switchTab('signal')">Signal</button>
    </div>
    
    <!-- Tab Content: Search -->
    <div id="tab-content-search" class="tab-content space-y-4">
        <!-- Cards row - Radar with current result -->
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-gray-800 rounded-xl card-shadow">
                <div class="flex">
                    <img src="{{ asset('images/icons/bigrada.png') }}" alt="Radar" class="w-28 h-28 object-contain">
                    <div class="flex items-start gap-2 py-4" id="radarResult">
                        <img src="{{ asset('images/icons/thachanh.png') }}" alt="Current Result" class="w-6 h-6 object-contain" id="currentGemIcon">
                        <p class="text-white font-semibold text-xs" id="currentGemPercent">-</p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-800 rounded-xl p-4 card-shadow flex items-center" id="finalResultCard">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/icons/thachanhtim.png') }}" alt="Kết quả" class="w-14 h-14 object-contain" id="finalResultIcon">
                    <div>
                        <p class="text-white font-semibold" id="finalResultName">Chờ kết quả...</p>
                        <p class="text-blue-400 text-sm" id="finalResultPayout"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Separator -->
        <hr class="border-dotted border-white/30 border-t-2 my-4">

        <!-- Gem Cards -->
        <div class="grid grid-cols-3 gap-2" id="gemCards">
            <!-- Cards will be populated by JavaScript -->
        </div>

        <!-- Amount input -->
        <div class="bg-gray-800 rounded-xl p-4 card-shadow space-y-3">
            <div class="text-sm text-gray-300">Số lượng <span class="text-blue-400">💎</span></div>
            <div class="flex items-center gap-3">
                <div class="flex-1 bg-gray-900 rounded-xl px-3 py-3 flex items-center justify-between">
                    <input type="number" min="0.01" step="0.01" value="10" id="betAmount" class="bg-transparent text-white w-full outline-none" placeholder="Nhập số lượng">
                    <button onclick="clearBetAmount()" class="text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <button id="confirmBetBtn" onclick="placeBet()" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-5 py-3 rounded-xl min-w-[110px] disabled:opacity-50 disabled:cursor-not-allowed">Xác nhận</button>
            </div>
            <div id="betInfo" class="text-xs text-gray-400 hidden">
                <p>Bạn đã đặt cược: <span id="betGemType" class="text-white"></span> - <span id="betAmountDisplay" class="text-white"></span> đá quý</p>
                <p>Nếu thắng, bạn sẽ nhận: <span id="betPayout" class="text-green-400"></span> đá quý</p>
            </div>
        </div>
    </div>
    
    <!-- Tab Content: Signal -->
    <div id="tab-content-signal" class="tab-content hidden">
        <!-- Signal Grid: 3 cột, mỗi cột 4 hàng, mỗi hàng 5 items (tổng 60 icon) -->
        <div id="signalGrid" class="grid grid-cols-3 gap-1">
            <!-- 3 cột sẽ được tạo động -->
        </div>
    </div>
</div>

<!-- Result Popup (Modal Center) -->
<div id="resultPopup" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50" onclick="closeResultPopup()"></div>
    
    <!-- Popup Content -->
    <div class="relative bg-[#1e3a8a] rounded-3xl shadow-2xl pb-8 w-full max-w-[419px] mx-4 transform translate-y-4 opacity-0 transition-all duration-300 ease-out">
        <!-- Miner Character -->
        <div class="flex justify-center -mt-16 mb-4">
            <img src="{{ asset('images/result_image.png') }}" alt="Miner" class="w-32 h-32 object-contain">
        </div>
        
        <!-- Content -->
        <div class="px-6 text-center">
            <h2 id="resultTitle" class="text-white text-lg font-semibold mb-2">Chúc mừng bạn !</h2>
            <p id="resultAmount" class="text-green-400 text-3xl font-bold mb-4">+0 USDT</p>
            <p id="resultMessage" class="text-white text-sm mb-6">Phần thưởng đã được sử lý thành công và chuyển đến ví của bạn.</p>
            
            <!-- Confirm Button -->
            <button onclick="closeResultPopup()" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-8 py-3 rounded-xl w-full">
                Xác nhận
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Gem types configuration - payout rates will be updated from API
    const GEM_TYPES = {
        'thachanh': { name: 'Thạch Anh', icon: '{{ asset("images/icons/thachanh.png") }}', randomRate: 30, payoutRate: 2.0 },
        'thachanhtim': { name: 'Thạch Anh Tím', icon: '{{ asset("images/icons/thachanhtim.png") }}', randomRate: 25, payoutRate: 2.5 },
        'ngusac': { name: 'Ngũ Sắc', icon: '{{ asset("images/icons/ngusac.png") }}', randomRate: 20, payoutRate: 3.0 },
        'daquy': { name: 'Đá Quý', icon: '{{ asset("images/icons/daquy.png") }}', randomRate: 15, payoutRate: 4.0 },
        'cuoc': { name: 'Cuốc', icon: '{{ asset("images/icons/cuoc.png") }}', randomRate: 7, payoutRate: 5.0 },
        'kimcuong': { name: 'Kim Cương', icon: '{{ asset("images/icons/kimcuong.png") }}', randomRate: 3, payoutRate: 5.95 },
    };
    
    // Update payout rates from API response
    function updatePayoutRates(gemTypes) {
        if (gemTypes && Array.isArray(gemTypes)) {
            gemTypes.forEach(gem => {
                if (GEM_TYPES[gem.type]) {
                    GEM_TYPES[gem.type].payoutRate = parseFloat(gem.payout_rate);
                }
            });
            // Update UI with new payout rates
            updateGemCardsPayoutRates();
        }
    }
    
    // Update gem cards display with current payout rates
    function updateGemCardsPayoutRates() {
        const gemCards = document.querySelectorAll('.gem-card');
        gemCards.forEach(card => {
            const gemType = card.dataset.gemType;
            if (gemType && GEM_TYPES[gemType]) {
                const payoutRateEl = card.querySelector('.payout-rate');
                if (payoutRateEl) {
                    payoutRateEl.textContent = `${GEM_TYPES[gemType].payoutRate}x`;
                }
            }
        });
    }

    let currentRound = null;
    let selectedGemType = null;
    let myBet = null;
    let clientTimerInterval = null;
    let roundResults = []; // Mảng lưu tất cả kết quả random từ giây 1-60
    let hasSavedResults = false; // Flag để tránh gọi API nhiều lần
    let isPollingBet = false; // Flag để tránh polling bet nhiều lần

    // Initialize
    document.addEventListener('DOMContentLoaded', async function() {
        initializeGemCards();
        
        // Load round first, then start timer
        await loadCurrentRound();
        loadMyBet();
        
        // Client-side timer runs every second for UI updates (no API calls)
        // Start timer after round is loaded to avoid showing wrong countdown
        clientTimerInterval = setInterval(updateClientTimer, 1000);
        
        // Update immediately after loading
        updateClientTimer();
    });

    // Initialize gem cards
    function initializeGemCards() {
        const container = document.getElementById('gemCards');
        container.innerHTML = '';
        
        Object.keys(GEM_TYPES).forEach(gemType => {
            const gem = GEM_TYPES[gemType];
            const card = document.createElement('button');
            card.className = 'gem-card bg-gray-800 text-white rounded-xl py-3 text-sm hover:bg-gray-700 transition-colors';
            card.onclick = () => selectGemType(gemType);
            card.innerHTML = `
                ${gem.name}<br>
                <span class="text-gray-400 text-xs payout-rate">${gem.payoutRate}x</span>
            `;
            card.dataset.gemType = gemType;
            container.appendChild(card);
        });
    }

    // Select gem type
    function selectGemType(gemType) {
        // Remove previous selection
        document.querySelectorAll('.gem-card').forEach(card => {
            card.classList.remove('selected');
        });
        
        // Add selection to clicked card
        const card = document.querySelector(`[data-gem-type="${gemType}"]`);
        if (card) {
            card.classList.add('selected');
        }
        
        selectedGemType = gemType;
    }

    // Load current round
    async function loadCurrentRound() {
        try {
            const response = await fetch('{{ route("explore.current-round") }}');
            const data = await response.json();
            
            if (data.round) {
                const previousRoundId = currentRound?.id;
                currentRound = {
                    id: data.round.id,
                    round_number: data.round.round_number,
                    seed: data.round.seed,
                    status: data.round.status,
                    phase: data.round.phase,
                    current_second: data.round.current_second || 0,
                    final_result: data.round.final_result,
                    admin_set_result: data.round.admin_set_result,
                    started_at: data.round.started_at ? new Date(data.round.started_at) : null,
                    break_until: data.round.break_until ? new Date(data.round.break_until) : null,
                    is_in_break: data.round.is_in_break || false,
                };
                
                // Update payout rates from API response
                if (data.gem_types && Array.isArray(data.gem_types)) {
                    updatePayoutRates(data.gem_types);
                }
                
                // Reset results array và flag khi load round mới
                if (previousRoundId !== currentRound.id) {
                    roundResults = [];
                    hasSavedResults = false;
                    isPollingBet = false;
                    
                    // Reset bet info khi chuyển sang round mới
                    myBet = null;
                    hideMyBet();
                    clearBetAmount();
                    selectedGemType = null;
                    
                    // Clear gem card selection
                    document.querySelectorAll('.gem-card').forEach(card => {
                        card.classList.remove('selected');
                    });
                    
                    // Clear signal grid
                    const signalGrid = document.getElementById('signalGrid');
                    if (signalGrid) {
                        signalGrid.innerHTML = '';
                    }
                    
                    // Load bet của round mới (chỉ khi có round mới)
                    loadMyBet();
                }
                
                // Reset loading flag
                if (currentRound._loadingNewRound) {
                    currentRound._loadingNewRound = false;
                }
                
                // Calculate current second and phase immediately after loading
                let initialSecond = 0;
                let initialPhase = 'break';
                
                if (currentRound.status === 'running' && currentRound.started_at) {
                    const now = new Date();
                    const startedAt = new Date(currentRound.started_at);
                    
                    if (!isNaN(startedAt.getTime())) {
                        const elapsed = Math.floor((now - startedAt) / 1000);
                        if (elapsed >= 0 && elapsed < 120) {
                            initialSecond = Math.min(60, Math.max(0, elapsed + 1));
                            if (initialSecond <= 30) {
                                initialPhase = 'betting';
                            } else {
                                initialPhase = 'result';
                            }
                        }
                    }
                } else if (currentRound.status === 'finished' && currentRound.break_until) {
                    const now = new Date();
                    const breakUntil = new Date(currentRound.break_until);
                    if (!isNaN(breakUntil.getTime()) && now < breakUntil) {
                        initialPhase = 'break';
                    }
                }
                
                // Update display with calculated values
                updateRoundDisplay(initialSecond, initialPhase);
                updateFinalResultCard(); // Update final result card when loading round
                
                // Update radar and signal grid if needed
                if (initialPhase === 'betting' || initialPhase === 'result') {
                    updateRadarResult(initialSecond);
                    updateSignalGrid(initialSecond, initialPhase);
                }
            }
        } catch (error) {
            console.error('Error loading round:', error);
        }
    }

    // Save final result and all results array to server when round ends (chỉ gọi 1 lần)
    async function saveRoundResult(roundId, finalResult, results) {
        try {
            const response = await fetch('{{ route("explore.save-result") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    round_id: roundId,
                    final_result: finalResult,
                    results: results, // Mảng kết quả từ giây 1-60
                }),
            });
            
            if (!response.ok) {
                console.error('Failed to save round result:', response.status);
                return false;
            }
            
            const data = await response.json();
            return data.success === true;
        } catch (error) {
            console.error('Error saving round result:', error);
            return false;
        }
    }
    
    // Client-side timer that runs every second (no API calls)
    // Tất cả thiết bị tính toán countdown giống nhau dựa trên started_at từ server
    async function updateClientTimer() {
        if (!currentRound) return;
        
        let currentSecond = 0;
        let phase = 'break';
        let shouldLoadNewRound = false;
        let breakRemaining = 0;
        
        if (currentRound.status === 'running' && currentRound.started_at) {
            const now = new Date();
            const startedAt = new Date(currentRound.started_at);
            
            // Validate started_at: must be a valid date
            if (isNaN(startedAt.getTime())) {
                console.warn('Invalid started_at date:', currentRound.started_at);
                updateRoundDisplay(0, 'break');
                return;
            }
            
            // Calculate elapsed time in seconds (chính xác đến giây)
            // Tất cả thiết bị sẽ tính toán giống nhau vì dùng cùng started_at từ server
            // Sử dụng getTime() để đảm bảo tính toán chính xác (milliseconds)
            // Không phụ thuộc vào timezone của client, chỉ so sánh timestamp
            const elapsed = Math.floor((now.getTime() - startedAt.getTime()) / 1000);
            
            // If elapsed is negative (started_at is in the future), wait
            if (elapsed < 0) {
                updateRoundDisplay(0, 'break');
                return;
            }
            
            // Calculate current second (cap at 60)
            // elapsed + 1 vì giây đầu tiên là giây 1, không phải giây 0
            // Tất cả thiết bị sẽ có cùng currentSecond vì dùng cùng started_at
            currentSecond = Math.min(60, Math.max(0, elapsed + 1));
            
            if (currentSecond <= 30) {
                phase = 'betting';
            } else {
                phase = 'result';
            }
            
            // Chỉ lưu kết quả random vào mảng từ giây 31-60 (30 giây cuối)
            if (currentSecond > 30 && currentSecond <= 60) {
                const gemType = getGemForSecond(currentRound.seed, currentSecond);
                if (!roundResults[currentSecond - 1]) {
                    roundResults[currentSecond - 1] = gemType;
                }
            }
            
            // If round just finished (reached second 60)
            if (currentSecond >= 60) {
                // Nếu round vẫn running, cần save result (chỉ 1 thiết bị save) hoặc load round mới
                if (currentRound.status === 'running') {
                    if (!hasSavedResults) {
                        // Thiết bị này sẽ save result (chỉ 1 thiết bị save)
                        const completeResults = [];
                        for (let i = 0; i < 60; i++) {
                            if (i < 30) {
                                completeResults[i] = null;
                            } else {
                                if (roundResults[i]) {
                                    completeResults[i] = roundResults[i];
                                } else {
                                    completeResults[i] = getGemForSecond(currentRound.seed, i + 1);
                                }
                            }
                        }
                        
                        let finalResult = completeResults[59];
                        if (currentRound.admin_set_result) {
                            finalResult = currentRound.admin_set_result;
                            completeResults[59] = currentRound.admin_set_result;
                        }
                        
                        // Save to server (chỉ gọi 1 lần)
                        hasSavedResults = true;
                        const saved = await saveRoundResult(currentRound.id, finalResult, completeResults);
                        // Sau khi save, đợi một chút rồi load round mới
                        // Tất cả thiết bị sẽ check bet result sau khi load round mới
                        if (!currentRound._loadingNewRound) {
                            currentRound._loadingNewRound = true;
                            // Đợi 1 giây để đảm bảo server đã xử lý xong round finish
                            // Tất cả thiết bị đều dùng cùng delay để popup hiển thị cùng lúc
                            setTimeout(async () => {
                                await loadCurrentRound();
                                updateFinalResultCard();
                                
                                // Tất cả thiết bị đều check bet result cùng lúc sau khi round finish
                                if (!isPollingBet) {
                                    isPollingBet = true;
                                    loadMyBet(true); // Immediate call
                                    
                                    // Chỉ poll nếu bet status vẫn là pending (chờ server xử lý)
                                    let pollCount = 0;
                                    const pollInterval = setInterval(() => {
                                        pollCount++;
                                        // Chỉ poll nếu chưa có kết quả (status vẫn pending)
                                        if (myBet && myBet.status === 'pending') {
                                            loadMyBet(true); // Immediate call
                                        } else {
                                            // Đã có kết quả, dừng poll
                                            clearInterval(pollInterval);
                                            isPollingBet = false;
                                        }
                                        
                                        // Dừng poll sau 3 lần (6 giây) để tránh gọi quá nhiều
                                        if (pollCount >= 3) {
                                            clearInterval(pollInterval);
                                            isPollingBet = false;
                                        }
                                    }, 2000);
                                }
                            }, 1000); // Tất cả thiết bị đều đợi 1 giây
                        }
                    } else {
                        // Thiết bị này đã save hoặc thiết bị khác đã save
                        // Load round mới và check bet result cùng lúc (sau khi round finish)
                        if (!currentRound._loadingNewRound) {
                            currentRound._loadingNewRound = true;
                            // Đợi cùng thời gian (1 giây) để đảm bảo tất cả thiết bị check bet result cùng lúc
                            setTimeout(async () => {
                                await loadCurrentRound();
                                updateFinalResultCard();
                                
                                // Tất cả thiết bị đều check bet result cùng lúc sau khi round finish
                                if (!isPollingBet) {
                                    isPollingBet = true;
                                    loadMyBet(true); // Immediate call
                                    
                                    // Chỉ poll nếu bet status vẫn là pending (chờ server xử lý)
                                    let pollCount = 0;
                                    const pollInterval = setInterval(() => {
                                        pollCount++;
                                        // Chỉ poll nếu chưa có kết quả (status vẫn pending)
                                        if (myBet && myBet.status === 'pending') {
                                            loadMyBet(true); // Immediate call
                                        } else {
                                            // Đã có kết quả, dừng poll
                                            clearInterval(pollInterval);
                                            isPollingBet = false;
                                        }
                                        
                                        // Dừng poll sau 3 lần (6 giây) để tránh gọi quá nhiều
                                        if (pollCount >= 3) {
                                            clearInterval(pollInterval);
                                            isPollingBet = false;
                                        }
                                    }, 2000);
                                }
                            }, 1000); // Tất cả thiết bị đều đợi 1 giây
                        }
                    }
                    return; // Return để không update display nữa
                }
            }
        } else if (currentRound.status === 'finished') {
            phase = 'break';
            
            if (currentRound.break_until) {
                const now = new Date();
                const breakUntil = new Date(currentRound.break_until);
                
                if (!isNaN(breakUntil.getTime())) {
                    breakRemaining = Math.max(0, Math.floor((breakUntil.getTime() - now.getTime()) / 1000));
                    if (breakRemaining > 0) {
                        // Still in break - chỉ update display, không gọi API
                        updateRoundDisplay(0, 'break', breakRemaining);
                        return;
                    } else {
                        // Break finished, load new round
                        shouldLoadNewRound = true;
                    }
                } else {
                    // Invalid break_until, load new round
                    shouldLoadNewRound = true;
                }
            } else {
                // No break time set, load new round
                shouldLoadNewRound = true;
            }
            
            // Reset bet when loading new round
            if (shouldLoadNewRound) {
                myBet = null;
                hideMyBet();
                clearBetAmount();
                selectedGemType = null;
                document.querySelectorAll('.gem-card').forEach(card => {
                    card.classList.remove('selected');
                });
            }
        } else if (currentRound.status === 'pending') {
            // Round is pending - chỉ update display, không gọi API
            // Server sẽ tự động start round khi break time hết
            phase = 'break';
            updateRoundDisplay(0, 'break');
            
            // Chỉ check khi break_until đã hết (nếu có)
            if (currentRound.break_until) {
                const now = new Date();
                const breakUntil = new Date(currentRound.break_until);
                if (!isNaN(breakUntil.getTime()) && now.getTime() >= breakUntil.getTime()) {
                    // Break time passed, load round (server should have started it)
                    shouldLoadNewRound = true;
                }
            } else {
                // No break time, check if round should start (wait a bit)
                setTimeout(() => {
                    if (currentRound && currentRound.status === 'pending') {
                        loadCurrentRound();
                    }
                }, 1000);
            }
            
            if (shouldLoadNewRound) {
                loadCurrentRound();
                return;
            }
            return;
        }
        
        if (shouldLoadNewRound) {
            loadCurrentRound();
            return;
        }
        
        // Update current second in round object
        currentRound.current_second = currentSecond;
        currentRound.phase = phase;
        
        // Update display
        updateRoundDisplay(currentSecond, phase, breakRemaining);
        
        // Update radar result (random based on seed - giống nhau trên tất cả thiết bị)
        if (phase === 'betting' || phase === 'result') {
            updateRadarResult(currentSecond);
            // Update signal grid
            updateSignalGrid(currentSecond, phase);
        }
    }

    // Update round display
    // Countdown được tính toán chính xác dựa trên started_at từ server
    // Tất cả thiết bị sẽ hiển thị giống nhau
    function updateRoundDisplay(currentSecond = null, phase = null, breakRemaining = null) {
        if (!currentRound) {
            return;
        }
        
        const sec = currentSecond !== null ? currentSecond : (currentRound.current_second || 0);
        const ph = phase !== null ? phase : (currentRound.phase || 'break');
        
        // Update round number
        const roundNumberEl = document.getElementById('roundNumber');
        if (roundNumberEl) {
            roundNumberEl.textContent = `Kỳ số : ${currentRound.round_number || '-'}`;
        }
        
        // Update countdown - tính toán chính xác dựa trên started_at
        let remainingSeconds = 0;
        if (ph === 'break' && breakRemaining !== null) {
            // Break time remaining
            remainingSeconds = breakRemaining;
        } else if (ph === 'break' && currentRound.break_until) {
            // Calculate break remaining from break_until
            const now = new Date();
            const breakUntil = new Date(currentRound.break_until);
            if (!isNaN(breakUntil.getTime())) {
                remainingSeconds = Math.max(0, Math.floor((breakUntil.getTime() - now.getTime()) / 1000));
            }
        } else if (ph === 'betting' || ph === 'result') {
            // Calculate remaining seconds based on started_at
            // Đảm bảo tất cả thiết bị tính toán giống nhau bằng cách dùng started_at từ server
            if (currentRound.started_at) {
                const now = new Date();
                const startedAt = new Date(currentRound.started_at);
                if (!isNaN(startedAt.getTime())) {
                    // Tính toán chính xác: elapsed time từ started_at đến now
                    const elapsed = Math.floor((now.getTime() - startedAt.getTime()) / 1000);
                    // Remaining = 60 - elapsed (đảm bảo >= 0)
                    remainingSeconds = Math.max(0, 60 - elapsed);
                } else {
                    // Fallback nếu started_at không hợp lệ
                    remainingSeconds = Math.max(0, 60 - sec);
                }
            } else {
                // Fallback nếu không có started_at
                remainingSeconds = Math.max(0, 60 - sec);
            }
        }
        
        const minutes = Math.floor(remainingSeconds / 60);
        const seconds = remainingSeconds % 60;
        
        const minute1El = document.getElementById('minute1');
        const minute2El = document.getElementById('minute2');
        const second1El = document.getElementById('second1');
        const second2El = document.getElementById('second2');
        
        if (minute1El) minute1El.textContent = Math.floor(minutes / 10);
        if (minute2El) minute2El.textContent = minutes % 10;
        if (second1El) second1El.textContent = Math.floor(seconds / 10);
        if (second2El) second2El.textContent = seconds % 10;
        
        // Update bet button based on phase
        const confirmBtn = document.getElementById('confirmBetBtn');
        if (confirmBtn) {
            if (ph === 'break') {
                confirmBtn.disabled = true;
                confirmBtn.textContent = 'Đang nghỉ giữa các phiên';
            } else if (ph === 'result' || sec > 30) {
                confirmBtn.disabled = true;
                confirmBtn.textContent = 'Hết thời gian đặt cược';
            } else if (myBet) {
                confirmBtn.disabled = true;
                confirmBtn.textContent = 'Đã đặt cược';
            } else {
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Xác nhận';
            }
        }
    }

    // Get gem type for a specific second based on seed
    // This must match the server-side logic exactly
    // Improved hash function to avoid consecutive duplicates
    function getGemForSecond(seed, second) {
        if (!seed) return 'thachanh';
        
        // If it's the last second (60) and admin has set a result, use that
        if (second === 60 && currentRound && currentRound.admin_set_result) {
            return currentRound.admin_set_result;
        }
        
        // Improved hash function with better distribution
        const string = seed + '_' + second;
        let hash = 0;
        for (let i = 0; i < string.length; i++) {
            const char = string.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & 0x7FFFFFFF; // Convert to 32bit integer
        }
        
        // Add second to hash for better variation
        hash = (hash * 31 + second * 17) & 0x7FFFFFFF;
        
        // Convert to 1-100 range with better distribution
        const rand = (Math.abs(hash) % 10000) % 100 + 1;
        
        const rates = [
            { type: 'thachanh', rate: 30 },
            { type: 'thachanhtim', rate: 25 },
            { type: 'ngusac', rate: 20 },
            { type: 'daquy', rate: 15 },
            { type: 'cuoc', rate: 7 },
            { type: 'kimcuong', rate: 3 },
        ];
        
        let cumulative = 0;
        for (const item of rates) {
            cumulative += item.rate;
            if (rand <= cumulative) {
                return item.type;
            }
        }
        
        return 'thachanh';
    }
    
    // Update radar result (client-side random based on seed)
    // Hiển thị % của tất cả các đá (tổng 100%) thay vì random rate
    function updateRadarResult(currentSecond = null) {
        if (!currentRound) {
            return;
        }
        
        const sec = currentSecond !== null ? currentSecond : (currentRound.current_second || 0);
        const phase = currentRound.phase || 'break';
        
        const icon = document.getElementById('currentGemIcon');
        const percent = document.getElementById('currentGemPercent');
        
        // 30 giây đầu: chỉ hiển thị radar cố định (không random)
        if (sec <= 30 && phase === 'betting') {
            // Hiển thị radar icon và tổng % của tất cả các đá
            if (icon) {
                // Giữ nguyên icon radar hoặc không thay đổi
            }
            if (percent) {
                // Hiển thị tổng % của tất cả các đá (30+25+20+15+7+3 = 100%)
                percent.textContent = '100%';
            }
            return;
        }
        
        // 30 giây cuối: random và hiển thị kết quả
        if (sec > 30 && sec <= 60) {
            // Get gem type for current second based on seed (chỉ random từ giây 31-60)
            const gemType = getGemForSecond(currentRound.seed, sec);
            const gem = GEM_TYPES[gemType];
            
            if (gem) {
                if (icon) {
                    icon.src = gem.icon;
                    icon.alt = gem.name;
                }
                if (percent) {
                    // Hiển thị tổng % của tất cả các đá (100%) thay vì random rate
                    percent.textContent = '100%';
                }
            }
            return;
        }
        
        // Round finished: show final result
        if (currentRound.final_result) {
            const gem = GEM_TYPES[currentRound.final_result];
            if (gem) {
                if (icon) {
                    icon.src = gem.icon;
                    icon.alt = gem.name;
                }
                if (percent) {
                    percent.textContent = 'Kết quả';
                }
            }
        }
    }
    
    // Update signal grid - 3 cột, mỗi cột 4 hàng, mỗi hàng 5 items (tổng 60 icon)
    // Hiển thị theo hàng ngang: hàng 1 của cả 3 cột, rồi hàng 2 của cả 3 cột, ...
    function updateSignalGrid(currentSecond, phase) {
        if (!currentRound) return;
        
        const signalGrid = document.getElementById('signalGrid');
        if (!signalGrid) return;
        
        const sec = currentSecond || 0;
        
        // Clear grid và rebuild từ đầu
        signalGrid.innerHTML = '';
        
        // Tạo 3 cột
        const columns = [];
        for (let col = 0; col < 3; col++) {
            const columnDiv = document.createElement('div');
            columnDiv.className = 'flex flex-col gap-0.5';
            columns.push(columnDiv);
            signalGrid.appendChild(columnDiv);
        }
        
        // Mỗi cột có 5 hàng, mỗi hàng có 4 items
        // Tổng: 3 cột x 5 hàng x 4 items = 60 items
        // Hiển thị theo hàng ngang: item 0-11 (hàng 1), item 12-23 (hàng 2), ...
        for (let i = 0; i < sec && i < 60; i++) {
            // Tính toán vị trí theo hàng ngang
            const rowIndex = Math.floor(i / 12); // Hàng ngang (0-4): mỗi hàng có 12 items (4 items x 3 cột)
            const itemInRow = i % 12; // Item trong hàng ngang (0-11)
            const colIndex = Math.floor(itemInRow / 4); // Cột (0-2): mỗi cột 4 items trong hàng
            const itemInColRow = itemInRow % 4; // Item trong hàng của cột (0-3)
            
            // Tạo hàng trong cột nếu chưa có
            let rowDiv = columns[colIndex].children[rowIndex];
            if (!rowDiv) {
                rowDiv = document.createElement('div');
                rowDiv.className = 'grid grid-cols-4 gap-0.5';
                columns[colIndex].appendChild(rowDiv);
            }
            
            // Tạo item trong hàng
            const iconDiv = document.createElement('div');
            
            let iconSrc = '';
            let iconAlt = '';
            
            if (i < 30) {
                // 30 giây đầu: hiển thị icon radar
                iconSrc = '{{ asset("images/icons/bigrada.png") }}';
                iconAlt = 'Radar';
            } else {
                // 30 giây cuối: hiển thị đá đã random
                const gemType = getGemForSecond(currentRound.seed, i + 1);
                const gem = GEM_TYPES[gemType];
                if (gem) {
                    iconSrc = gem.icon;
                    iconAlt = gem.name;
                } else {
                    iconSrc = '{{ asset("images/icons/thachanh.png") }}';
                    iconAlt = 'Thạch Anh';
                }
            }
            
            // Thêm background gray và rounded-full cho icon container
            iconDiv.className = 'flex items-center justify-center bg-gray-700 rounded-full w-8 h-8 p-0.5';
            
            const iconImg = document.createElement('img');
            iconImg.src = iconSrc;
            iconImg.alt = iconAlt;
            iconImg.className = 'w-6 h-6 object-contain'; // Icon size nhỏ hơn
            
            iconDiv.appendChild(iconImg);
            rowDiv.appendChild(iconDiv);
        }
    }
    
    // Update final result card
    function updateFinalResultCard() {
        if (!currentRound) {
            return;
        }
        
        const finalResultIcon = document.getElementById('finalResultIcon');
        const finalResultName = document.getElementById('finalResultName');
        const finalResultPayout = document.getElementById('finalResultPayout');
        
        // If round has finished and has final result
        if (currentRound.status === 'finished' && currentRound.final_result) {
            const gem = GEM_TYPES[currentRound.final_result];
            if (gem) {
                if (finalResultIcon) {
                    finalResultIcon.src = gem.icon;
                    finalResultIcon.alt = gem.name;
                    finalResultIcon.style.display = 'block'; // Đảm bảo icon được hiển thị
                }
                if (finalResultName) {
                    finalResultName.textContent = gem.name;
                }
                if (finalResultPayout) {
                    finalResultPayout.textContent = `${gem.payoutRate}x`;
                }
            }
        } else {
            // Round chưa kết thúc hoặc chưa có kết quả - chỉ hiển thị text, không hiển thị icon
            if (finalResultIcon) {
                finalResultIcon.style.display = 'none';
            }
            if (finalResultName) {
                finalResultName.textContent = 'Chờ kết quả...';
            }
            if (finalResultPayout) {
                finalResultPayout.textContent = '-';
            }
        }
    }

    // Load my bet
    let previousBetStatus = null; // Track previous bet status to detect changes
    let loadMyBetTimeout = null; // Debounce timeout
    let isLoadingMyBet = false; // Flag to prevent concurrent calls
    
    async function loadMyBet(immediate = false) {
        // Debounce: chỉ gọi API sau 300ms nếu không phải immediate
        if (!immediate) {
            if (loadMyBetTimeout) {
                clearTimeout(loadMyBetTimeout);
            }
            loadMyBetTimeout = setTimeout(() => {
                loadMyBet(true);
            }, 300);
            return;
        }
        
        // Prevent concurrent calls
        if (isLoadingMyBet) {
            return;
        }
        
        isLoadingMyBet = true;
        try {
            const response = await fetch('{{ route("explore.my-bet") }}');
            const data = await response.json();
            
            // Update balance if provided
            if (data.balance !== undefined) {
                const balanceEl = document.getElementById('userBalance');
                if (balanceEl) {
                    balanceEl.textContent = parseFloat(data.balance).toLocaleString('vi-VN') + '$';
                }
            }
            
            if (data.bet) {
                const newStatus = data.bet.status;
                
                // Check if status changed from pending to won/lost
                if (previousBetStatus === 'pending' && (newStatus === 'won' || newStatus === 'lost')) {
                    // Status just changed, show popup
                    myBet = data.bet;
                    displayMyBet();
                } else {
                    // Normal update
                    myBet = data.bet;
                    displayMyBet();
                }
                
                previousBetStatus = newStatus;
            } else {
                myBet = null;
                previousBetStatus = null;
                hideMyBet();
            }
        } catch (error) {
            console.error('Error loading my bet:', error);
        } finally {
            isLoadingMyBet = false;
        }
    }

    // Display my bet
    function displayMyBet() {
        if (!myBet) {
            hideMyBet();
            return;
        }
        
        // Kiểm tra xem bet có thuộc round hiện tại không
        if (currentRound && myBet.round_id && myBet.round_id !== currentRound.id) {
            // Bet không thuộc round hiện tại, ẩn đi
            myBet = null;
            hideMyBet();
            return;
        }
        
        const gem = GEM_TYPES[myBet.gem_type];
        if (!gem) return;
        
        const betInfo = document.getElementById('betInfo');
        if (!betInfo) return;
        
        // Clear previous status messages (giữ lại structure HTML ban đầu)
        const statusMessages = betInfo.querySelectorAll('p.mt-2');
        statusMessages.forEach(msg => msg.remove());
        
        // Update bet info
        const betGemTypeEl = document.getElementById('betGemType');
        const betAmountDisplayEl = document.getElementById('betAmountDisplay');
        const betPayoutEl = document.getElementById('betPayout');
        
        if (betGemTypeEl) betGemTypeEl.textContent = gem.name;
        if (betAmountDisplayEl) betAmountDisplayEl.textContent = parseFloat(myBet.amount).toLocaleString('vi-VN');
        if (betPayoutEl) betPayoutEl.textContent = parseFloat(myBet.payout_amount || (myBet.amount * myBet.payout_rate)).toLocaleString('vi-VN');
        
        betInfo.classList.remove('hidden');
        
        // Select the gem card
        selectGemType(myBet.gem_type);
        
        // Disable input
        const betAmountInput = document.getElementById('betAmount');
        if (betAmountInput) {
            betAmountInput.value = myBet.amount;
            betAmountInput.disabled = true;
        }
        
        // Show status
        const statusEl = document.createElement('p');
        statusEl.className = 'mt-2';
        if (myBet.status === 'won') {
            statusEl.className += ' text-green-400';
            statusEl.textContent = '🎉 Bạn đã thắng!';
        } else if (myBet.status === 'lost') {
            statusEl.className += ' text-red-400';
            statusEl.textContent = '😔 Bạn đã thua';
        } else {
            statusEl.className += ' text-yellow-400';
            statusEl.textContent = '⏳ Đang chờ kết quả...';
        }
        betInfo.appendChild(statusEl);
        
        // Show result popup chỉ khi status thay đổi từ pending sang won/lost
        if (previousBetStatus === 'pending' && (myBet.status === 'won' || myBet.status === 'lost')) {
            if (myBet.status === 'won') {
                showResultPopup('won', myBet.payout_amount || (myBet.amount * myBet.payout_rate));
            } else if (myBet.status === 'lost') {
                showResultPopup('lost', myBet.amount);
            }
        }
    }
    
    // Show result popup
    function showResultPopup(result, amount) {
        const popup = document.getElementById('resultPopup');
        const titleEl = document.getElementById('resultTitle');
        const amountEl = document.getElementById('resultAmount');
        const messageEl = document.getElementById('resultMessage');
        
        if (!popup || !titleEl || !amountEl || !messageEl) return;
        
        if (result === 'won') {
            titleEl.textContent = 'Chúc mừng bạn !';
            amountEl.textContent = `+${parseFloat(amount).toFixed(2)} USDT`;
            amountEl.className = 'text-green-400 text-3xl font-bold mb-4';
            messageEl.textContent = 'Phần thưởng đã được sử lý thành công và chuyển đến ví của bạn.';
        } else if (result === 'lost') {
            titleEl.textContent = 'Rất tiếc !';
            amountEl.textContent = `-${parseFloat(amount).toFixed(2)} USDT`;
            amountEl.className = 'text-red-400 text-3xl font-bold mb-4';
            messageEl.textContent = 'Bạn đã thua cược. Chúc may mắn lần sau!';
        }
        
        // Show popup - remove hidden class first
        popup.classList.remove('hidden');
        // Trigger animation by adding show class after a small delay
        setTimeout(() => {
            popup.classList.add('show');
        }, 10);
        
        // Auto hide after 3 seconds
        setTimeout(() => {
            closeResultPopup();
        }, 3000);
    }
    
    // Close result popup
    function closeResultPopup() {
        const popup = document.getElementById('resultPopup');
        if (popup) {
            popup.classList.remove('show');
            // Hide after animation completes
            setTimeout(() => {
                popup.classList.add('hidden');
            }, 300);
        }
    }

    // Hide my bet
    function hideMyBet() {
        document.getElementById('betInfo').classList.add('hidden');
        document.getElementById('betAmount').disabled = false;
    }

    // Clear bet amount
    function clearBetAmount() {
        document.getElementById('betAmount').value = '';
    }

    // Place bet
    async function placeBet() {
        if (!selectedGemType) {
            if (typeof showToast === 'function') {
                showToast('Vui lòng chọn loại đá quý để đặt cược', 'error');
            } else {
                alert('Vui lòng chọn loại đá quý để đặt cược');
            }
            return;
        }
        
        const amount = parseFloat(document.getElementById('betAmount').value);
        if (!amount || amount <= 0) {
            if (typeof showToast === 'function') {
                showToast('Vui lòng nhập số lượng đá quý hợp lệ', 'error');
            } else {
                alert('Vui lòng nhập số lượng đá quý hợp lệ');
            }
            return;
        }
        
        const confirmBtn = document.getElementById('confirmBetBtn');
        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Đang xử lý...';
        
        try {
            const response = await fetch('{{ route("explore.bet") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({
                    gem_type: selectedGemType,
                    amount: amount,
                }),
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
                if (typeof showToast === 'function') {
                    showToast(data.message, 'success');
                } else {
                    alert(data.message);
                }
                
                // Update balance
                if (data.new_balance !== undefined) {
                    document.getElementById('userBalance').textContent = parseFloat(data.new_balance).toLocaleString('vi-VN') + '$';
                }
                
                // Reload my bet (immediate call after bet)
                loadMyBet(true);
            } else {
                if (typeof showToast === 'function') {
                    showToast(data.error || 'Có lỗi xảy ra khi đặt cược', 'error');
                } else {
                    alert(data.error || 'Có lỗi xảy ra khi đặt cược');
                }
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Xác nhận';
            }
        } catch (error) {
            console.error('Error placing bet:', error);
            if (typeof showToast === 'function') {
                showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
            } else {
                alert('Có lỗi xảy ra. Vui lòng thử lại.');
            }
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Xác nhận';
        }
    }

    // Tab switching function
    function switchTab(tabName) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        
        // Remove active state from all tabs
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('text-white', 'border-b-2', 'border-blue-500');
            button.classList.add('text-gray-400');
        });
        
        // Show selected tab content
        document.getElementById('tab-content-' + tabName).classList.remove('hidden');
        
        // Add active state to selected tab
        const activeTab = document.getElementById('tab-' + tabName);
        activeTab.classList.remove('text-gray-400');
        activeTab.classList.add('text-white', 'border-b-2', 'border-blue-500');
    }

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (clientTimerInterval) {
            clearInterval(clientTimerInterval);
        }
    });
</script>
@endpush
