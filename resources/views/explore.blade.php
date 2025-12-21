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
<header class="w-full px-4 py-4 flex items-center justify-center bg-gray-900 border-b border-gray-800">
    <h1 class="text-white text-base font-semibold">Trò Chơi</h1>
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
                <img src="{{ asset('images/icons/coin_asset.png') }}" alt="Gem" class="pl-2 w-8 h-8 object-contain" style="filter: drop-shadow(0 0 8px rgba(59, 130, 246, 0.6)) drop-shadow(0 0 12px rgba(59, 130, 246, 0.4));">
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
            <div class="bg-[#111111] rounded-xl card-shadow">
                <div class="flex">
                    <img src="{{ asset('images/icons/bigrada.png') }}" alt="Radar" class="w-24 h-24 object-contain">
                    <div class="flex items-center justify-center gap-2 py-2" id="radarResult">
                        <img src="{{ asset('images/icons/thachanh.png') }}" alt="Current Result" class="w-10 h-10 object-contain" id="currentGemIcon">
                        <p class="text-white font-semibold text-xs" id="currentGemPercent"></p>
                    </div>
                </div>
            </div>
            <div class="bg-[#111111] rounded-xl p-4 card-shadow flex flex-col items-center justify-center gap-1" id="finalResultCard">
                <!-- Icon nhấp nháy lần lượt các loại đá (ở trên) -->
                <img src="{{ asset('images/icons/thachanh.png') }}" alt="Kết quả" class="w-10 h-10 object-contain flex-shrink-0" id="finalResultIcon" style="display: block;">
                <!-- Chữ "Chờ kết quả..." (ở dưới) -->
                <div class="text-center min-h-[40px] flex flex-col items-center justify-center">
                    <p class="text-white font-semibold" id="finalResultName">Chờ kết quả...</p>
                    <p class="text-blue-400 text-sm" id="finalResultPayout"></p>
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
        <div class="space-y-3">
            <div class="text-sm text-gray-300 flex items-center gap-1">
                <p class="text-[#3958F5] font-medium text-sm leading-none tracking-normal">Số lượng </p>
                <img src="{{ asset('images/icons/coin_asset.png') }}" alt="Gem" class="w-4 h-4 object-contain">
            </div>
            <div class="flex items-center gap-3">
                <div class="flex-1 px-3 flex items-center justify-between" style="width: 281px; height: 47px; border-radius: 5px; border: 0.5px solid #FFFFFF80;">
                    <input type="number" min="0.01" step="0.01" value="10" id="betAmount" class="bg-transparent text-white w-full outline-none" placeholder="Nhập số lượng">
                    <button onclick="clearBetAmount()" class="text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <button id="confirmBetBtn" onclick="placeBet()" class="text-white font-semibold cursor-pointer hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed transition-opacity whitespace-nowrap" style="height: 47px; border-radius: 10px; background: #3958F5; padding-left: 16px; padding-right: 16px;">Xác nhận</button>
            </div>
            <div id="betInfo" class="text-xs text-gray-400 hidden">
                <p>Bạn đã đặt cược: <span id="betGemType" class="text-white"></span> - <span id="betAmountDisplay" class="text-red-600"></span> đá quý</p>
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
            <p id="resultMessage" class="text-white text-sm mb-6">Phần thưởng đã được xử lý thành công và chuyển đến ví của bạn.</p>
            
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
    let roundResults = []; // Mảng lưu tất cả kết quả random từ giây 1-60 (chỉ để hiển thị)
    let isPollingBet = false; // Flag để tránh polling bet nhiều lần

    // Initialize
    document.addEventListener('DOMContentLoaded', async function() {
        initializeGemCards();
        
        // Khởi tạo round với seed tính từ round_number (không cần gọi API)
        const clientRoundNumber = calculateRoundNumber();
        const seed = 'round_' + clientRoundNumber; // Seed deterministic từ round_number
        
        currentRound = {
            round_number: clientRoundNumber,
            seed: seed,
            status: 'pending',
            phase: 'break',
            current_second: 0,
            final_result: null,
            admin_set_result: null,
            deadline: calculateRoundDeadline(clientRoundNumber),
        };
        
        // Load bet để lấy final_result nếu có
        await loadMyBet();
        
        // Update final result card để hiển thị animation nếu cần
        updateFinalResultCard();
        
        // Client-side timer runs every second for UI updates (no API calls)
        clientTimerInterval = setInterval(updateClientTimer, 1000);
        
        // Update immediately
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

    // Base time để tính round number và deadline
    // Mặc định: 2025-01-01 00:00:00 UTC (có thể lấy từ server nếu cần)
    const BASE_TIME = new Date('2025-01-01T00:00:00Z').getTime();
    const ROUND_DURATION = 60; // 60 giây mỗi round
    const BREAK_TIME = 10; // 10 giây break time giữa các phiên
    const TOTAL_CYCLE = ROUND_DURATION + BREAK_TIME; // 70 giây mỗi cycle (60 + 10)
    
    // Tính round number dựa trên base time
    function calculateRoundNumber() {
        const now = Date.now();
        const elapsed = Math.floor((now - BASE_TIME) / 1000); // Elapsed seconds
        return Math.floor(elapsed / TOTAL_CYCLE) + 1;
    }
    
    // Tính deadline cho round hiện tại
    function calculateRoundDeadline(roundNumber) {
        // Round start time = BASE_TIME + (roundNumber - 1) * TOTAL_CYCLE
        const roundStartTime = BASE_TIME + ((roundNumber - 1) * TOTAL_CYCLE * 1000);
        // Deadline = roundStartTime + ROUND_DURATION (60 giây)
        return roundStartTime + (ROUND_DURATION * 1000);
    }
    
    // Khởi tạo round mới với seed tính từ round_number (không cần gọi API)
    function initializeRound(roundNumber) {
        const seed = 'round_' + roundNumber; // Seed deterministic từ round_number
        
        const previousRoundNumber = currentRound?.round_number;
        
        currentRound = {
            round_number: roundNumber,
            seed: seed,
            status: 'pending',
            phase: 'break',
            current_second: 0,
            final_result: null,
            admin_set_result: null,
            deadline: calculateRoundDeadline(roundNumber),
        };
        
        // Reset results array và flag khi load round mới
        if (previousRoundNumber !== roundNumber) {
            roundResults = [];
            isPollingBet = false;
            
            // Reset checking bet result flag khi round mới bắt đầu
            if (currentRound._checkingBetResult) {
                currentRound._checkingBetResult = false;
            }
            
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
            
            // Reset final result về null khi round mới bắt đầu
            currentRound.final_result = null;
            currentRound.admin_set_result = null;
            
            // Reset final result card về "Chờ kết quả..." khi round mới bắt đầu
            updateFinalResultCard();
            
            // Load bet của round mới
            loadMyBet();
        }
    }

    // Client-side timer tính toán dựa trên deadline (mặc định)
    // Tất cả thiết bị tính toán giống nhau vì dùng cùng BASE_TIME
    async function updateClientTimer() {
        if (!currentRound) {
            // Khởi tạo round nếu chưa có
            const clientRoundNumber = calculateRoundNumber();
            initializeRound(clientRoundNumber);
            return;
        }
        
        const now = Date.now();
        const clientRoundNumber = calculateRoundNumber();
        const deadline = calculateRoundDeadline(clientRoundNumber);
        const countdown = Math.max(0, Math.floor((deadline - now) / 1000)); // Countdown in seconds
        
        // Update round number nếu thay đổi
        if (currentRound.round_number !== clientRoundNumber) {
            // Round mới bắt đầu, khởi tạo round mới với seed tính từ round_number
            if (currentRound._checkingBetResult) {
                currentRound._checkingBetResult = false;
            }
            initializeRound(clientRoundNumber);
            return;
        }
        
        // Tính current second từ countdown
        let currentSecond = 0;
        let phase = 'break';
        
        if (countdown > 0 && countdown <= ROUND_DURATION) {
            // Round đang chạy
            currentSecond = ROUND_DURATION - countdown + 1; // +1 vì giây đầu tiên là giây 1
            
            if (currentSecond <= 30) {
                phase = 'betting';
            } else {
                phase = 'result';
            }
            
            // Chỉ lưu kết quả random vào mảng từ giây 31-59 (29 giây cuối)
            // KHÔNG lưu kết quả cho giây 60, đợi kết quả từ server (admin_set_result hoặc final_result)
            if (currentSecond > 30 && currentSecond < 60) {
                // Giây 31-59: lưu random bình thường
                const gemType = getGemForSecond(currentRound.seed, currentSecond);
                if (!roundResults[currentSecond - 1]) {
                    roundResults[currentSecond - 1] = gemType;
                }
            }
            
            // Giây 60: KHÔNG lưu random, đợi kết quả từ server
            // Nếu có admin_set_result hoặc final_result, lưu vào roundResults[59]
            if (currentSecond === 60) {
                const resultToShow = currentRound.admin_set_result || currentRound.final_result;
                if (resultToShow) {
                    roundResults[59] = resultToShow;
                }
            }
            
            // Nếu round vừa finish (countdown = 0 hoặc currentSecond >= 60)
            if (currentSecond >= 60 || countdown === 0) {
                // Round đã finish, call API để lấy admin_set_result
                // Nếu có admin_set_result thì dùng, nếu không thì dùng random
                if (!currentRound._checkingBetResult) {
                    currentRound._checkingBetResult = true;
                    
                    // Đợi một chút để server xử lý xong round finish
                    setTimeout(async () => {
                        // Call API để lấy admin_set_result từ server
                        try {
                            const response = await fetch('/api/explore/current-round', {
                                method: 'GET',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                },
                            });
                            
                            if (response.ok) {
                                const data = await response.json();
                                if (data.round) {
                                    // Cập nhật admin_set_result và final_result từ server
                                    if (data.round.admin_set_result !== undefined) {
                                        currentRound.admin_set_result = data.round.admin_set_result;
                                    }
                                    if (data.round.final_result !== undefined) {
                                        currentRound.final_result = data.round.final_result;
                                    }
                                    
                                    // Nếu có admin_set_result, dùng admin_set_result
                                    // Nếu không có admin_set_result, dùng final_result hoặc tính random
                                    if (currentRound.admin_set_result) {
                                        currentRound.final_result = currentRound.admin_set_result;
                                    } else if (!currentRound.final_result) {
                                        // Không có admin_set_result và final_result, tính random từ seed
                                        currentRound.final_result = getGemForSecond(currentRound.seed, 60);
                                    }
                                }
                            }
                        } catch (error) {
                            console.error('Error fetching round result:', error);
                            // Nếu call API lỗi, dùng random
                            if (!currentRound.final_result) {
                                currentRound.final_result = getGemForSecond(currentRound.seed, 60);
                            }
                        }
                        
                        // Update final result card
                        updateFinalResultCard();
                        
                        // Check bet result của round vừa finish
                        if (!isPollingBet && myBet && myBet.status === 'pending') {
                            isPollingBet = true;
                            
                            // Poll để check bet result cho đến khi có kết quả
                            let pollCount = 0;
                            const maxPolls = 10; // Poll tối đa 10 lần (20 giây)
                            
                            const pollInterval = setInterval(async () => {
                                pollCount++;
                                
                                // Load bet để check status và lấy final_result từ server
                                await loadMyBet(true);
                                
                                // Nếu bet đã có kết quả (won/lost), dừng poll
                                if (myBet && (myBet.status === 'won' || myBet.status === 'lost')) {
                                    clearInterval(pollInterval);
                                    isPollingBet = false;
                                    currentRound._checkingBetResult = false;
                                    
                                    // Cập nhật final_result và admin_set_result từ myBet nếu có
                                    if (myBet.round && currentRound) {
                                        // Cập nhật admin_set_result
                                        if (myBet.round.admin_set_result !== undefined) {
                                            const previousAdminSetResult = currentRound.admin_set_result;
                                            currentRound.admin_set_result = myBet.round.admin_set_result;
                                            
                                            // Nếu admin_set_result thay đổi và round đang chạy, cập nhật lại roundResults[59] (giây 60)
                                            if (previousAdminSetResult !== currentRound.admin_set_result) {
                                                roundResults[59] = currentRound.admin_set_result; // Index 59 = giây 60
                                            }
                                        }
                                        // Cập nhật final_result (ưu tiên admin_set_result nếu có)
                                        if (myBet.round.final_result) {
                                            currentRound.final_result = myBet.round.final_result;
                                        } else if (currentRound.admin_set_result && !currentRound.final_result) {
                                            // Nếu có admin_set_result nhưng chưa có final_result, dùng admin_set_result
                                            currentRound.final_result = currentRound.admin_set_result;
                                        }
                                        updateFinalResultCard();
                                    }
                                    
                                    // Hiển thị result popup
                                    if (myBet.status === 'won') {
                                        showResultPopup('won', myBet.payout_amount || (myBet.amount * myBet.payout_rate));
                                    } else if (myBet.status === 'lost') {
                                        showResultPopup('lost', myBet.amount);
                                    }
                                } else if (pollCount >= maxPolls) {
                                    // Đã poll đủ số lần, dừng
                                    clearInterval(pollInterval);
                                    isPollingBet = false;
                                    currentRound._checkingBetResult = false;
                                }
                            }, 2000); // Poll mỗi 2 giây
                        } else {
                            // Không có bet hoặc bet đã có kết quả
                            currentRound._checkingBetResult = false;
                            
                            // Nếu có bet và đã có kết quả, hiển thị popup
                            if (myBet && (myBet.status === 'won' || myBet.status === 'lost')) {
                                // Cập nhật final_result và admin_set_result từ myBet nếu có
                                if (myBet.round && currentRound) {
                                    // Cập nhật admin_set_result
                                    if (myBet.round.admin_set_result !== undefined) {
                                        const previousAdminSetResult = currentRound.admin_set_result;
                                        currentRound.admin_set_result = myBet.round.admin_set_result;
                                        
                                        // Nếu admin_set_result thay đổi và round đang chạy, cập nhật lại roundResults[59] (giây 60)
                                        if (previousAdminSetResult !== currentRound.admin_set_result) {
                                            roundResults[59] = currentRound.admin_set_result; // Index 59 = giây 60
                                        }
                                    }
                                    // Cập nhật final_result (ưu tiên admin_set_result nếu có)
                                    if (myBet.round.final_result) {
                                        currentRound.final_result = myBet.round.final_result;
                                    } else if (currentRound.admin_set_result && !currentRound.final_result) {
                                        // Nếu có admin_set_result nhưng chưa có final_result, dùng admin_set_result
                                        currentRound.final_result = currentRound.admin_set_result;
                                    }
                                    updateFinalResultCard();
                                }
                                
                                if (myBet.status === 'won') {
                                    showResultPopup('won', myBet.payout_amount || (myBet.amount * myBet.payout_rate));
                                } else if (myBet.status === 'lost') {
                                    showResultPopup('lost', myBet.amount);
                                }
                            }
                        }
                    }, 1000);
                }
                return;
            }
        } else if (countdown > ROUND_DURATION) {
            // Chưa đến thời gian round này (break time)
            phase = 'break';
            currentSecond = 0;
        } else {
            // Round đã finish, đang trong break time (10 giây)
            phase = 'break';
            currentSecond = 0;
            // Không cần load lại round ở đây vì đã xử lý ở trên (dòng 422-480)
        }
        
        // Update current second in round object
        currentRound.current_second = currentSecond;
        currentRound.phase = phase;
        
        // Update display
        updateRoundDisplay(currentSecond, phase, countdown > ROUND_DURATION ? countdown - ROUND_DURATION : 0);
        
        // Update radar result (random based on seed - giống nhau trên tất cả thiết bị)
        if (phase === 'betting' || phase === 'result') {
            updateRadarResult(currentSecond);
            // Update signal grid
            updateSignalGrid(currentSecond, phase);
        } else if (phase === 'break') {
            // Trong 10 giây break time, chỉ hiển thị "Chờ kết quả...", không hiển thị kết quả
            // Vẫn hiển thị signal grid với đủ 60 items (icon thứ 60 sẽ hiển thị radar nếu chưa có kết quả)
            updateSignalGrid(60, 'break');
        }
    }

    // Update round display
    // Countdown được tính toán dựa trên deadline (BASE_TIME)
    // Tất cả thiết bị sẽ hiển thị giống nhau vì dùng cùng BASE_TIME
    function updateRoundDisplay(currentSecond = null, phase = null, breakRemaining = null) {
        if (!currentRound) {
            return;
        }
        
        const sec = currentSecond !== null ? currentSecond : (currentRound.current_second || 0);
        const ph = phase !== null ? phase : (currentRound.phase || 'break');
        
        // Update round number (tính từ BASE_TIME)
        const roundNumberEl = document.getElementById('roundNumber');
        if (roundNumberEl) {
            const clientRoundNumber = calculateRoundNumber();
            roundNumberEl.textContent = `Kỳ số : ${clientRoundNumber}`;
        }
        
        // Update countdown - tính từ deadline
        let remainingSeconds = 0;
        if (ph === 'break' && breakRemaining !== null) {
            // Break time remaining
            remainingSeconds = breakRemaining;
        } else if (ph === 'betting' || ph === 'result') {
            // Tính countdown từ deadline
            const now = Date.now();
            const clientRoundNumber = calculateRoundNumber();
            const deadline = calculateRoundDeadline(clientRoundNumber);
            remainingSeconds = Math.max(0, Math.floor((deadline - now) / 1000));
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
            // Chỉ giây 60 mới dùng admin_set_result nếu có, các giây khác (31-59) vẫn hiển thị random bình thường
            let gemType;
            if (sec === 60 && currentRound.admin_set_result) {
                // Giây 60: ưu tiên admin_set_result nếu có
                gemType = currentRound.admin_set_result;
            } else {
                // Các giây khác (31-59) hoặc giây 60 nếu chưa có admin_set_result: hiển thị random
                gemType = getGemForSecond(currentRound.seed, sec);
            }
            
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
        // Ưu tiên admin_set_result nếu có, nếu không thì dùng final_result
        const resultToShow = currentRound.admin_set_result || currentRound.final_result;
        if (resultToShow) {
            const gem = GEM_TYPES[resultToShow];
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
        
        // Tính số giây cần hiển thị
        // Nếu round đã finish hoặc currentSecond >= 60, hiển thị đủ 60 items
        // Nếu round đang chạy, hiển thị từ 1 đến currentSecond
        let sec = currentSecond || 0;
        
        // Kiểm tra xem round đã finish chưa (dựa trên countdown)
        const clientRoundNumber = calculateRoundNumber();
        const deadline = calculateRoundDeadline(clientRoundNumber);
        const now = Date.now();
        const countdown = Math.max(0, Math.floor((deadline - now) / 1000));
        const isRoundFinished = countdown === 0 || countdown > ROUND_DURATION || (currentRound && currentRound.final_result);
        
        // Nếu round đã finish, hiển thị đủ 60 items
        if (isRoundFinished || sec >= 60) {
            sec = 60;
        }
        
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
        // Hiển thị tất cả 60 items (i từ 0 đến 59, tương ứng giây 1 đến 60)
        const maxItems = Math.min(sec, 60);
        for (let i = 0; i < maxItems; i++) {
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
                iconSrc = '{{ asset("images/icons/rada.png") }}';
                iconAlt = 'Radar';
            } else if (i < 59) {
                // Giây 31-59: hiển thị random bình thường
                let gemType;
                if (roundResults[i]) {
                    // Dùng kết quả đã lưu trong roundResults
                    gemType = roundResults[i];
                } else {
                    // Tính từ seed nếu chưa có trong roundResults
                    gemType = getGemForSecond(currentRound.seed, i + 1);
                }
                
                const gem = GEM_TYPES[gemType];
                if (gem) {
                    iconSrc = gem.icon;
                    iconAlt = gem.name;
                } else {
                    iconSrc = '{{ asset("images/icons/thachanh.png") }}';
                    iconAlt = 'Thạch Anh';
                }
            } else {
                // Icon thứ 60 (i === 59): Ưu tiên admin_set_result, nếu không có thì dùng final_result
                // KHÔNG hiển thị random cho icon này, đợi kết quả từ server
                const resultToShow = currentRound.admin_set_result || currentRound.final_result;
                if (resultToShow) {
                    const gem = GEM_TYPES[resultToShow];
                    if (gem) {
                        iconSrc = gem.icon;
                        iconAlt = gem.name;
                    } else {
                        iconSrc = '{{ asset("images/icons/thachanh.png") }}';
                        iconAlt = 'Thạch Anh';
                    }
                } else {
                    // Chưa có kết quả, hiển thị radar icon
                    iconSrc = '{{ asset("images/icons/rada.png") }}';
                    iconAlt = 'Radar';
                }
            }
            
            // Thêm background gray và rounded-full cho icon container
            // Tăng kích cỡ cho icon rada (30 giây đầu)
            const isRadaIcon = iconSrc && iconSrc.includes('rada.png');
            const containerSize = 'w-8 h-8';
            iconDiv.className = `flex items-center justify-center bg-gray-700 rounded-full ${containerSize} p-0.5`;
            
            const iconImg = document.createElement('img');
            iconImg.src = iconSrc;
            iconImg.alt = iconAlt;
            // Tăng kích cỡ icon rada
            const iconSize = isRadaIcon ? 'w-8 h-8' : 'w-6 h-6';
            iconImg.className = `${iconSize} object-contain`;
            
            iconDiv.appendChild(iconImg);
            rowDiv.appendChild(iconDiv);
        }
    }
    
    // Animation nhấp nháy các loại đá khi chờ kết quả
    let gemBlinkInterval = null;
    let currentBlinkGemIndex = 0;
    const gemTypesArray = ['thachanh', 'thachanhtim', 'ngusac', 'daquy', 'cuoc', 'kimcuong'];
    
    // Màu sắc cho mỗi loại đá (để tạo hiệu ứng nhấp nháy)
    const gemColors = {
        'thachanh': 'rgba(255, 255, 255, 0.8)',
        'thachanhtim': 'rgba(138, 43, 226, 0.8)', // Purple
        'ngusac': 'rgba(255, 215, 0, 0.8)', // Gold
        'daquy': 'rgba(0, 191, 255, 0.8)', // Deep Sky Blue
        'cuoc': 'rgba(255, 20, 147, 0.8)', // Deep Pink
        'kimcuong': 'rgba(255, 255, 255, 1)', // White (diamond)
    };
    
    function startGemBlinkAnimation() {
        // Dừng animation cũ nếu có
        if (gemBlinkInterval) {
            clearInterval(gemBlinkInterval);
        }
        
        const finalResultIcon = document.getElementById('finalResultIcon');
        if (!finalResultIcon) return;
        
        currentBlinkGemIndex = 0;
        
        // Cập nhật icon ngay lập tức
        updateBlinkGem();
        
        // Tạo animation nhấp nháy mỗi 500ms
        gemBlinkInterval = setInterval(() => {
            currentBlinkGemIndex = (currentBlinkGemIndex + 1) % gemTypesArray.length;
            updateBlinkGem();
        }, 500);
    }
    
    // Animation nhấp nháy cho đá kết quả (chỉ nhấp nháy một loại đá)
    function startResultGemBlinkAnimation(gemType) {
        // Dừng animation cũ nếu có
        if (gemBlinkInterval) {
            clearInterval(gemBlinkInterval);
        }
        
        const finalResultIcon = document.getElementById('finalResultIcon');
        if (!finalResultIcon) return;
        
        const gem = GEM_TYPES[gemType];
        if (!gem) return;
        
        // Cập nhật icon ngay lập tức
        updateResultBlinkGem(gemType);
        
        // Tạo animation nhấp nháy mỗi 500ms (chỉ nhấp nháy đá kết quả)
        gemBlinkInterval = setInterval(() => {
            updateResultBlinkGem(gemType);
        }, 500);
    }
    
    function stopGemBlinkAnimation() {
        if (gemBlinkInterval) {
            clearInterval(gemBlinkInterval);
            gemBlinkInterval = null;
        }
    }
    
    function updateBlinkGem() {
        const finalResultIcon = document.getElementById('finalResultIcon');
        if (!finalResultIcon) return;
        
        const gemType = gemTypesArray[currentBlinkGemIndex];
        const gem = GEM_TYPES[gemType];
        
        if (gem) {
            finalResultIcon.src = gem.icon;
            finalResultIcon.alt = gem.name;
            finalResultIcon.style.display = 'block';
            
            // Thêm hiệu ứng nhấp nháy theo màu của đá với animation rõ ràng hơn
            const gemColor = gemColors[gemType] || 'rgba(255, 255, 255, 0.8)';
            
            // Tạo hiệu ứng nhấp nháy bằng cách thay đổi opacity và filter
            finalResultIcon.style.filter = `drop-shadow(0 0 15px ${gemColor}) drop-shadow(0 0 30px ${gemColor}) brightness(1.2)`;
            finalResultIcon.style.transition = 'all 0.3s ease';
            finalResultIcon.style.animation = 'gemBlink 0.5s ease-in-out';
            
            // Thêm keyframe animation nếu chưa có
            if (!document.getElementById('gemBlinkStyle')) {
                const style = document.createElement('style');
                style.id = 'gemBlinkStyle';
                style.textContent = `
                    @keyframes gemBlink {
                        0%, 100% { opacity: 1; transform: scale(1); }
                        50% { opacity: 0.7; transform: scale(1.1); }
                    }
                `;
                document.head.appendChild(style);
            }
        }
    }
    
    function updateResultBlinkGem(gemType) {
        const finalResultIcon = document.getElementById('finalResultIcon');
        if (!finalResultIcon) return;
        
        const gem = GEM_TYPES[gemType];
        if (!gem) return;
        
        finalResultIcon.src = gem.icon;
        finalResultIcon.alt = gem.name;
        finalResultIcon.style.display = 'block';
        
        // Thêm hiệu ứng nhấp nháy theo màu của đá với animation rõ ràng hơn
        const gemColor = gemColors[gemType] || 'rgba(255, 255, 255, 0.8)';
        
        // Tạo hiệu ứng nhấp nháy bằng cách thay đổi opacity và filter
        finalResultIcon.style.filter = `drop-shadow(0 0 15px ${gemColor}) drop-shadow(0 0 30px ${gemColor}) brightness(1.2)`;
        finalResultIcon.style.transition = 'all 0.3s ease';
        finalResultIcon.style.animation = 'gemBlink 0.5s ease-in-out';
        
        // Thêm keyframe animation nếu chưa có
        if (!document.getElementById('gemBlinkStyle')) {
            const style = document.createElement('style');
            style.id = 'gemBlinkStyle';
            style.textContent = `
                @keyframes gemBlink {
                    0%, 100% { opacity: 1; transform: scale(1); }
                    50% { opacity: 0.7; transform: scale(1.1); }
                }
            `;
            document.head.appendChild(style);
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
        
        // Kiểm tra xem round đã finish chưa (dựa trên countdown)
        const clientRoundNumber = calculateRoundNumber();
        const deadline = calculateRoundDeadline(clientRoundNumber);
        const now = Date.now();
        const countdown = Math.max(0, Math.floor((deadline - now) / 1000));
        const isRoundFinished = countdown === 0 || countdown > ROUND_DURATION;
        
        // Kiểm tra xem có đang trong break time không (10 giây sau khi round finish)
        // Break time: khi countdown > ROUND_DURATION (tức là đã qua 60 giây của round, đang trong 10 giây break)
        const isInBreakTime = countdown > ROUND_DURATION && countdown <= TOTAL_CYCLE;
        
        // Xác định kết quả cần hiển thị:
        // 1. Ưu tiên admin_set_result nếu có
        // 2. Nếu không có admin_set_result, dùng final_result
        // 3. Nếu không có cả hai, tính random từ seed (giây 60)
        let resultToShow = null;
        if (currentRound.admin_set_result) {
            // Admin đã set result, dùng admin_set_result
            resultToShow = currentRound.admin_set_result;
        } else if (currentRound.final_result) {
            // Có final_result từ server, dùng final_result
            resultToShow = currentRound.final_result;
        } else if (isRoundFinished) {
            // Round đã finish nhưng chưa có kết quả, tính random từ seed (giây 60)
            resultToShow = getGemForSecond(currentRound.seed, 60);
            // Lưu vào currentRound để dùng lại
            if (!currentRound.final_result) {
                currentRound.final_result = resultToShow;
            }
        }
        
        // Nếu đang trong break time (10 giây đầu sau khi round finish), chỉ hiển thị "Chờ kết quả..." với animation nhấp nháy
        // Nếu đã qua break time hoặc round đang chạy và có kết quả, hiển thị kết quả
        if (isInBreakTime) {
            // Trong 10 giây break time, hiển thị animation nhấp nháy các loại đá
            startGemBlinkAnimation();
            if (finalResultName) {
                finalResultName.textContent = 'Chờ kết quả...';
            }
            if (finalResultPayout) {
                finalResultPayout.textContent = '';
            }
        } else if (resultToShow) {
            // Có kết quả và không trong break time, hiển thị kết quả với animation nhấp nháy
            const gem = GEM_TYPES[resultToShow];
            if (gem) {
                // Bắt đầu animation nhấp nháy cho đá kết quả
                startResultGemBlinkAnimation(resultToShow);
                if (finalResultName) {
                    finalResultName.textContent = gem.name;
                }
                if (finalResultPayout) {
                    finalResultPayout.textContent = `${gem.payoutRate}x`;
                }
            } else {
                console.warn('Gem type not found:', resultToShow);
                // Nếu không tìm thấy gem type, hiển thị animation nhấp nháy tất cả các loại đá
                startGemBlinkAnimation();
                if (finalResultName) {
                    finalResultName.textContent = 'Chờ kết quả...';
                }
                if (finalResultPayout) {
                    finalResultPayout.textContent = '';
                }
            }
        } else {
            // Chưa có kết quả (round chưa finish), hiển thị animation nhấp nháy
            startGemBlinkAnimation();
            if (finalResultName) {
                finalResultName.textContent = 'Chờ kết quả...';
            }
            if (finalResultPayout) {
                finalResultPayout.textContent = '';
            }
        }
    }

    // Load my bet
    let previousBetStatus = null; // Track previous bet status to detect changes
    let loadMyBetTimeout = null; // Debounce timeout
    let isLoadingMyBet = false; // Flag to prevent concurrent calls
    let lastMyBetLoadTime = 0; // Timestamp của lần load bet cuối cùng
    
    async function loadMyBet(immediate = false) {
        // Throttle: chỉ cho phép gọi mỗi 1 giây (trừ khi immediate)
        const now = Date.now();
        if (!immediate && (isLoadingMyBet || (now - lastMyBetLoadTime < 1000))) {
            return;
        }
        
        // Debounce: chỉ gọi API sau 500ms nếu không phải immediate
        if (!immediate) {
            if (loadMyBetTimeout) {
                clearTimeout(loadMyBetTimeout);
            }
            loadMyBetTimeout = setTimeout(() => {
                loadMyBet(true);
            }, 500);
            return;
        }
        
        // Prevent concurrent calls
        if (isLoadingMyBet) {
            return;
        }
        
        isLoadingMyBet = true;
        lastMyBetLoadTime = now;
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
        
        // Kiểm tra xem bet có thuộc round hiện tại không (so sánh round_number)
        if (currentRound && myBet.round_number && myBet.round_number !== currentRound.round_number) {
            // Bet không thuộc round hiện tại, ẩn đi
            myBet = null;
            hideMyBet();
            return;
        }
        
        // Cập nhật final_result và admin_set_result từ myBet nếu có
        if (myBet.round && currentRound) {
            // Cập nhật admin_set_result
            if (myBet.round.admin_set_result !== undefined) {
                currentRound.admin_set_result = myBet.round.admin_set_result;
            }
            // Cập nhật final_result (ưu tiên admin_set_result nếu có)
            // LUÔN ưu tiên admin_set_result nếu có
            if (currentRound.admin_set_result) {
                // Admin đã set result, LUÔN dùng admin_set_result
                currentRound.final_result = currentRound.admin_set_result;
            } else if (myBet.round.final_result) {
                // Chỉ dùng final_result từ server nếu chưa có admin_set_result
                currentRound.final_result = myBet.round.final_result;
            }
            updateFinalResultCard();
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
        if (myBet.status === 'won') {
            const statusEl = document.createElement('p');
            statusEl.className = 'mt-2 text-green-400';
            statusEl.textContent = '🎉 Bạn đã thắng!';
            betInfo.appendChild(statusEl);
        }
        
        // Update previousBetStatus để track changes
        const currentStatus = myBet.status;
        
        // Show result popup chỉ khi status thay đổi từ pending sang won/lost
        // Hoặc khi load bet và đã có kết quả (won/lost) nhưng chưa hiển thị popup
        if ((previousBetStatus === 'pending' && (currentStatus === 'won' || currentStatus === 'lost')) ||
            ((currentStatus === 'won' || currentStatus === 'lost') && !myBet._popupShown)) {
            
            if (currentStatus === 'won') {
                showResultPopup('won', myBet.payout_amount || (myBet.amount * myBet.payout_rate));
                myBet._popupShown = true; // Đánh dấu đã hiển thị popup
            } else if (currentStatus === 'lost') {
                showResultPopup('lost', myBet.amount);
                myBet._popupShown = true; // Đánh dấu đã hiển thị popup
            }
        }
        
        // Update previousBetStatus
        previousBetStatus = currentStatus;
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
            messageEl.textContent = 'Phần thưởng đã được xử lý thành công và chuyển đến ví của bạn.';
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
        
        // Auto hide after 10 seconds (để hiển thị kết quả trong break time)
        setTimeout(() => {
            closeResultPopup();
        }, 10000);
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
