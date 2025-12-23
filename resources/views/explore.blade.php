@extends('layouts.mobile')

@section('title', 'Khám phá - Micex')

@push('styles')
    <style>
        .card-shadow {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }

        .gem-card {
            transition: all 0.3s ease;
        }

        .gem-card.selected {
            border: 2px solid #3b82f6;
            background: rgba(59, 130, 246, 0.1);
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
                        <p class="text-lg font-bold text-white" id="userBalance">
                            {{ number_format(auth()->user()->balance ?? 0, 2, '.', ',') }}$</p>
                    </div>
                </div>
            </div>
            <div class="bg-blue-500 rounded-xl p-2 card-shadow">
                <p class="text-xs text-white/90 mb-2 text-center">Thời gian còn lại để khai thác</p>
                <div class="flex items-center justify-center gap-2">
                    <!-- Minutes: First digit -->
                    <div class="bg-white text-gray-900 rounded-md w-12 h-8 flex items-center justify-center font-bold text-lg shadow"
                        id="minute1">0</div>
                    <!-- Minutes: Second digit -->
                    <div class="bg-white text-gray-900 rounded-md w-12 h-8 flex items-center justify-center font-bold text-lg shadow"
                        id="minute2">0</div>
                    <!-- Colon separator -->
                    <div
                        class="bg-white text-gray-900 rounded-md w-10 h-8 flex items-center justify-center font-bold text-lg shadow">
                        :</div>
                    <!-- Seconds: First digit -->
                    <div class="bg-white text-gray-900 rounded-md w-12 h-8 flex items-center justify-center font-bold text-lg shadow"
                        id="second1">0</div>
                    <!-- Seconds: Second digit -->
                    <div class="bg-white text-gray-900 rounded-md w-12 h-8 flex items-center justify-center font-bold text-lg shadow"
                        id="second2">0</div>
                </div>
            </div>
        </div>
        <p style="font-family: Inter; font-weight: 500; font-style: italic; font-size: 14px; line-height: 100%; letter-spacing: 0%;"
            id="roundNumber">Kỳ số : -</p>

        <!-- Miner Video -->
        <div class="rounded-2xl overflow-hidden card-shadow min-h-[384px]">
            <!-- MP4 cho 30 giây đầu -->
            <video id="minerVideoFirst30" class="object-cover" muted autoplay loop playsinline preload="auto"
                style="width: 419px; height: 384px; border-radius: 10px; opacity: 1; display: none;" aria-label="Miner Start">
                <source src="{{ asset('videos/222.mp4') }}" type="video/mp4">
            </video>
            <!-- MP4 cho 30 giây cuối -->
            <video id="minerVideoLast30" class="object-cover" muted autoplay loop playsinline preload="auto"
                style="width: 419px; height: 384px; border-radius: 10px; opacity: 1; display: none;" aria-label="Miner End">
                <source src="{{ asset('videos/111.mp4') }}" type="video/mp4">
            </video>
        </div>


        <!-- Recent Rounds Results -->
        <div class="flex items-center justify-center">
            <div id="recentRoundsContainer" class="w-full rounded-[40px] bg-[#111111] py-2 flex items-center justify-center gap-1.5 overflow-x-auto" style="opacity: 1; transform: rotate(0deg);">
                <!-- 13 rounds will be populated by JavaScript -->
                <div class="text-gray-400 text-xs p-1">Đang tải...</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex items-center gap-8 px-1">
            <button id="tab-search" class="tab-button text-white font-semibold border-b-2 border-blue-500 pb-2"
                onclick="switchTab('search')">Search</button>
            <button id="tab-signal" class="tab-button text-gray-400 font-semibold pb-2"
                onclick="switchTab('signal')">Signal</button>
        </div>

        <!-- Tab Content: Search -->
        <div id="tab-content-search" class="tab-content space-y-4">
            <!-- Cards row - Radar with current result -->
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-[#111111] rounded-xl card-shadow flex items-center justify-center overflow-hidden">
                    <video class="w-full h-full object-cover" autoplay muted loop playsinline preload="auto" aria-label="Radar">
                        <source src="{{ asset('videos/333.mp4') }}" type="video/mp4">
                    </video>
                </div>
                <div class="bg-[#111111] rounded-xl p-2 card-shadow flex flex-col items-center justify-center gap-2"
                    id="finalResultCard">
                    <!-- Icon nhấp nháy lần lượt các loại đá (ở trên) -->
                    <img src="{{ asset('images/icons/thachanh.png') }}" alt="Kết quả"
                        class="w-10 h-10 object-contain flex-shrink-0" id="finalResultIcon" style="display: block;">
                    <!-- Chữ "Chờ kết quả..." (ở dưới) -->
                    <div class="text-center flex flex-col items-center justify-center" style="min-height: 32px;">
                        <p class="text-white font-medium text-xs" id="finalResultName"></p>
                        <p class="text-gray-400 text-xs" id="finalResultPayout"></p>
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
                    <div class="flex-1 px-3 flex items-center justify-between"
                        style="width: 281px; height: 47px; border-radius: 5px; border: 0.5px solid #FFFFFF80;">
                        <input type="number" min="0.01" step="0.01" value="10" id="betAmount"
                            class="bg-transparent text-white w-full outline-none" placeholder="Nhập số lượng">
                        <button onclick="clearBetAmount()" class="text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <button id="confirmBetBtn" onclick="placeBet()"
                        class="text-white font-semibold cursor-pointer hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed transition-opacity whitespace-nowrap"
                        style="height: 47px; border-radius: 10px; background: #3958F5; padding-left: 16px; padding-right: 16px;">Đặt cược</button>
                </div>
                <div id="betInfo" class="text-xs text-gray-400 hidden">
                    <p>Bạn đã đặt cược: <span id="betGemType" class="text-white"></span> - <span id="betAmountDisplay"
                            class="text-red-600"></span> đá quý</p>
                    <p>Nếu thắng, bạn sẽ nhận: <span id="betPayout" class="text-green-400"></span> đá quý</p>
                </div>
            </div>
        </div>

        <!-- Tab Content: Signal -->
        <div id="tab-content-signal" class="tab-content hidden">
            <!-- Signal Grid: Hiển thị 30 rounds gần nhất, mỗi round là 1 icon -->
            <div id="signalGrid" class="grid grid-cols-3 gap-0.5">
                <!-- Sẽ được tạo động từ API -->
            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <script>
        // Gem types configuration - payout rates will be updated from API
        // 3 đá thường: user có thể đặt cược
        // 3 đá nổ hũ: chỉ admin set, user không thể đặt cược
        const GEM_TYPES = {
            'thachanh': {
                name: 'Thạch Anh',
                icon: '{{ asset('images/icons/thachanh.png') }}',
                randomRate: 40,
                payoutRate: 1.95
            },
            'daquy': {
                name: 'Đá Quý',
                icon: '{{ asset('images/icons/daquy.png') }}',
                randomRate: 30,
                payoutRate: 5.95
            },
            'kimcuong': {
                name: 'Kim Cương',
                icon: '{{ asset('images/icons/kimcuong.png') }}',
                randomRate: 30,
                payoutRate: 1.95
            },
            // 3 đá nổ hũ (chỉ để hiển thị trong signal grid, user không thể đặt cược)
            'thachanhtim': {
                name: 'Thạch Anh Tím',
                icon: '{{ asset('images/icons/thachanhtim.png') }}',
                randomRate: 0,
                payoutRate: 10.00
            },
            'ngusac': {
                name: 'Ngũ Sắc',
                icon: '{{ asset('images/icons/ngusac.png') }}',
                randomRate: 0,
                payoutRate: 20.00
            },
            'cuoc': {
                name: 'Cuốc',
                icon: '{{ asset('images/icons/cuoc.png') }}',
                randomRate: 0,
                payoutRate: 50.00
            },
        };

        // Update payout rates and random rates from API response
        function updatePayoutRates(gemTypes) {
            if (gemTypes && Array.isArray(gemTypes)) {
                gemTypes.forEach(gem => {
                    if (GEM_TYPES[gem.type]) {
                        GEM_TYPES[gem.type].payoutRate = parseFloat(gem.payout_rate);
                        GEM_TYPES[gem.type].randomRate = parseFloat(gem.random_rate); // Cập nhật random rate từ API
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
    // Lưu kết quả round trước để hiển thị khi reload và 30s đầu round mới
    let previousRoundResult = null;
    const storedPrevResult = localStorage.getItem('previousRoundResult');
    if (storedPrevResult) {
        previousRoundResult = storedPrevResult;
    }
        let isPollingBet = false; // Flag để tránh polling bet nhiều lần
        let clientBetInfo = null; // Lưu thông tin bet ở client để layout xử lý khi round finish
        let signalGridRounds = []; // Lưu 60 rounds để hiển thị trong grid Signal (chỉ ở client)
        let signalTabLoaded = false; // Flag để biết tab Signal đã load chưa
        let minerVideoPhase = null; // phase hiện tại của miner video để tránh reset liên tục


        // Initialize
        document.addEventListener('DOMContentLoaded', async function() {
            // Preload assets để tránh khựng khi bắt đầu
            const minerVideoFirst30 = document.getElementById('minerVideoFirst30');
            const minerVideoLast30 = document.getElementById('minerVideoLast30');
            // Với GIF chỉ cần load sớm (loading eager đã đặt trong HTML)

            // Load payout rates from API first
            await loadPayoutRates();

            initializeGemCards();

            // Khởi tạo round với seed tính từ round_number (không cần gọi API)
            const clientRoundNumber = calculateRoundNumber();
            const seed = 'round_' + clientRoundNumber; // Seed deterministic từ round_number

            currentRound = {
                round_number: clientRoundNumber,
                seed: seed,
                status: 'pending',
                phase: 'betting',
                current_second: 0,
                final_result: null,
                admin_set_result: null,
                deadline: calculateRoundDeadline(clientRoundNumber),
            };

            // Load bet khi khởi tạo để hiển thị bet cũ nếu có
            // Đặc biệt quan trọng khi refresh trang: nếu bet đã thắng, sẽ hiển thị popup
            loadMyBet(true);

            // Nếu round đã finish, đảm bảo gọi loadMyBet() để lấy kết quả và hiển thị popup nếu thắng
            // Check xem round hiện tại đã finish chưa (countdown <= 0)
            // deadline là timestamp (số), không phải Date object
            const initialCountdown = Math.max(0, Math.floor((currentRound.deadline - Date.now()) / 1000));
            if (initialCountdown <= 0) {
                // Round đã finish, gọi loadMyBet() để lấy kết quả và hiển thị popup nếu thắng
                setTimeout(() => {
                    loadMyBet(true);
                }, 1500);
            }

            // Update final result card để hiển thị animation nếu cần
            updateFinalResultCard();

            // Client-side timer runs every second for UI updates (no API calls)
            clientTimerInterval = setInterval(updateClientTimer, 1000);

            // Update immediately
            updateClientTimer();
            
            // Initialize video display based on current second
            const initialSecond = currentRound.current_second || 1;
            updateMinerVideo(initialSecond);
            
            // Load recent rounds display
            loadRecentRoundsDisplay();
        });

        // Load payout rates from API
        async function loadPayoutRates() {
            try {
                const response = await fetch('{{ route('explore.gem-types') }}');
                const gemTypes = await response.json();

                if (gemTypes && Array.isArray(gemTypes)) {
                    updatePayoutRates(gemTypes);
                }
            } catch (error) {
                // Sử dụng default rates nếu API fail
            }
        }

        // Initialize gem cards (chỉ hiển thị 3 đá thường, không hiển thị đá nổ hũ)
        function initializeGemCards() {
            const container = document.getElementById('gemCards');
            container.innerHTML = '';

            // Chỉ hiển thị 3 đá thường mà user có thể đặt cược
            const bettableGemTypes = ['thachanh', 'daquy', 'kimcuong'];

            bettableGemTypes.forEach(gemType => {
                const gem = GEM_TYPES[gemType];
                if (!gem) return;

                const card = document.createElement('button');
                card.className =
                    'gem-card bg-gray-800 text-white rounded-xl py-3 text-sm hover:bg-gray-700 transition-colors';
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

        // Tính round number dựa trên base time
        function calculateRoundNumber() {
            const now = Date.now();
            const elapsed = Math.floor((now - BASE_TIME) / 1000); // Elapsed seconds
            return Math.floor(elapsed / ROUND_DURATION) + 1;
        }

        // Tính deadline cho round hiện tại
        function calculateRoundDeadline(roundNumber) {
            // Round start time = BASE_TIME + (roundNumber - 1) * ROUND_DURATION
            const roundStartTime = BASE_TIME + ((roundNumber - 1) * ROUND_DURATION * 1000);
            // Deadline = roundStartTime + ROUND_DURATION (60 giây)
            return roundStartTime + (ROUND_DURATION * 1000);
        }

        // Khởi tạo round mới với seed tính từ round_number (không cần gọi API)
        function initializeRound(roundNumber) {
            const seed = 'round_' + roundNumber; // Seed deterministic từ round_number

            const previousRoundNumber = currentRound?.round_number;

        // Lưu kết quả round trước (nếu có) để hiển thị 30s đầu round mới
        previousRoundResult = currentRound?.admin_set_result || currentRound?.final_result || previousRoundResult;

        currentRound = {
                round_number: roundNumber,
                seed: seed,
                status: 'pending',
                phase: 'betting',
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
                previousBetStatus = null;
                clientBetInfo = null; // Reset client bet info khi round mới
                localStorage.removeItem('clientBetInfo');
                hideMyBet();
                clearBetAmount();
                selectedGemType = null;

                // Hiển thị lại button khi round mới bắt đầu
                const confirmBtn = document.getElementById('confirmBetBtn');
                if (confirmBtn) {
                    confirmBtn.style.display = '';
                    confirmBtn.disabled = false;
                }

                // Clear gem card selection
                document.querySelectorAll('.gem-card').forEach(card => {
                    card.classList.remove('selected');
                });

                // KHÔNG clear signal grid khi round mới bắt đầu
                // Signal grid sẽ được append kết quả mới khi round finish

                // Reset final result về null khi round mới bắt đầu
                currentRound.final_result = null;
                currentRound.admin_set_result = null;

                // Reset final result card về "Chờ kết quả..." khi round mới bắt đầu
                updateFinalResultCard();

                // Reset video về GIF cho 30 giây đầu
                updateMinerVideo(1);

                // Không cần load bet khi round mới bắt đầu (round mới chưa có bet)
                // Chỉ load bet khi user đặt cược hoặc khi round finish
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
            let phase = 'betting';

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
                            // Gọi API để lấy kết quả round (admin_set_result hoặc random)
                            try {
                                const response = await fetch(
                                    `{{ route('explore.round-result') }}?round_number=${currentRound.round_number}`
                                    );
                                const data = await response.json();

                                if (data.result) {
                                    // Cập nhật final_result từ server
                                    currentRound.final_result = data.result;
                                    if (data.admin_set_result) {
                                        currentRound.admin_set_result = data.admin_set_result;
                                    }

                                    // Update final result card
                                    updateFinalResultCard();

                                    // Append kết quả mới vào signal grid
                                    appendRoundToSignalGrid(currentRound.round_number, data.result);
                                } else {
                                    // Nếu API không trả về result, tính từ seed
                                    currentRound.final_result = getGemForSecond(currentRound.seed, 60);
                                    updateFinalResultCard();

                                    // Append kết quả mới vào signal grid
                                    appendRoundToSignalGrid(currentRound.round_number, currentRound
                                        .final_result);
                                }
                            } catch (error) {
                                // Nếu API lỗi, tính từ seed
                                currentRound.final_result = getGemForSecond(currentRound.seed, 60);
                                updateFinalResultCard();

                                // Append kết quả mới vào signal grid
                                appendRoundToSignalGrid(currentRound.round_number, currentRound
                                    .final_result);
                            }

                            currentRound._checkingBetResult = false;
                        }, 1000);
                    }
                    return;
                }
            } else {
                // Round đã finish, chuyển sang round tiếp theo
                phase = 'result';
                currentSecond = 60;
            }

            // Update current second in round object
            currentRound.current_second = currentSecond;
            currentRound.phase = phase;

            // Update display
            updateRoundDisplay(currentSecond, phase, 0);

        // Refresh result card each tick (để 5s cuối chuyển sang “Chờ kết quả...”)
        updateFinalResultCard();

            // Radar image chỉ hiển thị cố định, không cần update
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
            if (ph === 'betting' || ph === 'result') {
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
                if (ph === 'result' || sec > 30) {
                    confirmBtn.disabled = true;
                    // Hiển thị đếm ngược trên button
                    const mins = Math.floor(remainingSeconds / 60);
                    const secs = remainingSeconds % 60;
                    const formattedTime = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
                    confirmBtn.textContent = formattedTime;
                } else if (myBet || clientBetInfo) {
                    confirmBtn.disabled = true;
                    confirmBtn.textContent = 'Đặt cược';
                } else {
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = 'Đặt cược';
                }
            }

            // Update video source based on current second
            updateMinerVideo(sec);
        }

        // Update miner video based on current second
        function updateMinerVideo(currentSecond) {
            const minerVideoFirst30 = document.getElementById('minerVideoFirst30');
            const minerVideoLast30 = document.getElementById('minerVideoLast30');
            
            if (!minerVideoFirst30 || !minerVideoLast30) return;

            const showVideo = (videoToShow, videoToHide) => {
                videoToShow.style.display = 'block';
                videoToHide.style.display = 'none';

                if (videoToHide.pause) {
                    videoToHide.pause();
                }
                if (videoToShow.currentTime !== undefined) {
                    videoToShow.currentTime = 0;
                }
                if (videoToShow.play) {
                    videoToShow.play().catch(() => {});
                }
            };

            let nextPhase = 'idle';
            if (currentSecond >= 1 && currentSecond <= 30) {
                nextPhase = 'first';
            } else if (currentSecond > 30 && currentSecond <= 60) {
                nextPhase = 'last';
            }

            // Tránh restart video liên tục nếu vẫn cùng phase
            if (nextPhase === minerVideoPhase) {
                return;
            }
            minerVideoPhase = nextPhase;

            // 30 giây đầu (1-30): dùng 222.mp4
            // 30 giây cuối (31-60): dùng 111.mp4
            if (nextPhase === 'first' || nextPhase === 'idle') {
                // Hiển thị video đầu, ẩn video cuối
                showVideo(minerVideoFirst30, minerVideoLast30);
            } else if (nextPhase === 'last') {
                // Hiển thị video cuối, ẩn video đầu
                showVideo(minerVideoLast30, minerVideoFirst30);
            } else {
                // Mặc định hiển thị GIF đầu khi chưa bắt đầu round
                showVideo(minerVideoFirst30, minerVideoLast30);
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

            // Sử dụng random rates từ GEM_TYPES (đã được cập nhật từ API)
            // Đảm bảo sắp xếp theo thứ tự để tổng = 100
            const rates = [];
            Object.keys(GEM_TYPES).forEach(type => {
                rates.push({
                    type: type,
                    rate: GEM_TYPES[type].randomRate || 33.33 // Fallback nếu chưa có
                });
            });

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
        // Update signal grid - 3 cột, mỗi cột 4 hàng, mỗi hàng 5 items (tổng 60 icon)
        // Hiển thị theo hàng ngang: hàng 1 của cả 3 cột, rồi hàng 2 của cả 3 cột, ...
        // Function cũ - không dùng nữa, tab Signal giờ hiển thị 30 rounds gần nhất
        // Đã thay thế bằng updateSignalGridWithRounds()
        // Function cũ - không dùng nữa, tab Signal giờ hiển thị 30 rounds gần nhất
        // Đã thay thế bằng updateSignalGridWithRounds()
        function updateSignalGrid(currentSecond, phase) {
            // Không làm gì - tab Signal giờ dùng updateSignalGridWithRounds()
            return;
        }

        // Animation nhấp nháy các loại đá khi chờ kết quả
        let gemBlinkInterval = null;
        let currentBlinkGemIndex = 0;
        const gemTypesArray = ['thachanh', 'daquy', 'kimcuong'];

        // Màu sắc cho mỗi loại đá (để tạo hiệu ứng nhấp nháy)
        const gemColors = {
            'thachanh': 'rgba(255, 255, 255, 0.8)',
            'daquy': 'rgba(0, 191, 255, 0.8)', // Deep Sky Blue
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
                finalResultIcon.style.filter =
                `drop-shadow(0 0 8px ${gemColor}) drop-shadow(0 0 6px ${gemColor}) brightness(1.15)`;
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
            finalResultIcon.style.filter =
                `drop-shadow(0 0 8px ${gemColor}) drop-shadow(0 0 6px ${gemColor}) brightness(1.15)`;
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
            const isRoundFinished = countdown === 0;
            const currentSecond = currentRound.current_second || 0;

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

            // Trạng thái hiển thị:
            // - Nếu có kết quả round hiện tại: hiển thị ngay
            // - 5 giây cuối (56-60) và chưa có kết quả: hiển thị "Chờ kết quả..." + blink
            // - Còn lại: hiển thị kết quả của round trước (nếu có), nếu không có thì chờ

            // Hiển thị kết quả nếu round hiện tại đã có
            if (resultToShow) {
                // Lưu để hiển thị ở round sau và khi reload
                previousRoundResult = resultToShow;
                localStorage.setItem('previousRoundResult', previousRoundResult);

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
                    // Nếu không tìm thấy gem type, hiển thị animation nhấp nháy tất cả các loại đá
                    startGemBlinkAnimation();
                    if (finalResultName) {
                        finalResultName.textContent = '';
                    }
                    if (finalResultPayout) {
                        finalResultPayout.textContent = '';
                    }
                }
                return;
            }

            // 5 giây cuối, chưa có kết quả: random/blink, icon center, ẩn text
            if (currentSecond >= 56 && currentSecond <= 60) {
                const finalResultCard = document.getElementById('finalResultCard');
                if (finalResultCard) {
                    finalResultCard.classList.add('gap-0', 'justify-center');
                }
                startGemBlinkAnimation();
                if (finalResultName) {
                    finalResultName.textContent = '';
                }
                if (finalResultPayout) {
                    finalResultPayout.textContent = '';
                }
                return;
            }
            // Reset layout when không còn trong 5 giây cuối
            const finalResultCard = document.getElementById('finalResultCard');
            if (finalResultCard) {
                finalResultCard.classList.remove('gap-0');
            }

            // Còn lại: hiển thị kết quả round trước (nếu có), nếu không thì chờ
            if (previousRoundResult) {
                const gem = GEM_TYPES[previousRoundResult];
                if (gem) {
                    startResultGemBlinkAnimation(previousRoundResult);
                    if (finalResultName) {
                        finalResultName.textContent = gem.name;
                    }
                    if (finalResultPayout) {
                        finalResultPayout.textContent = `${gem.payoutRate}x`;
                    }
                    return;
                }
            }

            // Fallback: trạng thái chờ
            startGemBlinkAnimation();
            if (finalResultName) {
                finalResultName.textContent = '';
            }
            if (finalResultPayout) {
                finalResultPayout.textContent = '';
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
                const response = await fetch('{{ route('explore.my-bet') }}');
                const data = await response.json();

                // Update balance if provided
                if (data.balance !== undefined) {
                    const balanceEl = document.getElementById('userBalance');
                    if (balanceEl) {
                    balanceEl.textContent = parseFloat(data.balance).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '$';
                    }
                }

                if (data.bet) {
                    const newStatus = data.bet.status;
                    const oldStatus = previousBetStatus;

                    // Update myBet
                    // Nếu bet đã thắng và chưa hiển thị popup, sẽ hiển thị popup ở dưới
                    myBet = data.bet;
                    // Reset _popupShown flag nếu status thay đổi (từ pending -> won)
                    if (oldStatus !== newStatus) {
                        myBet._popupShown = false;
                    } else {
                        // Giữ flag nếu status không đổi (đã hiển thị popup rồi)
                        myBet._popupShown = (myBet._popupShown || false);
                    }

                    // Display bet info
                    displayMyBet();

                    previousBetStatus = newStatus;
                } else {
                    myBet = null;
                    previousBetStatus = null;
                    hideMyBet();
                }
            } catch (error) {} finally {
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
        if (betAmountDisplayEl) betAmountDisplayEl.textContent = parseFloat(myBet.amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (betPayoutEl) betPayoutEl.textContent = parseFloat(myBet.payout_amount || (myBet.amount * myBet.payout_rate))
            .toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

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
            const confirmBtn = document.getElementById('confirmBetBtn');

            // Disable button ngay lập tức khi click, trước khi call API
            confirmBtn.disabled = true;

            if (!selectedGemType) {
                if (typeof showToast === 'function') {
                    showToast('Vui lòng chọn loại đá quý để đặt cược', 'error');
                } else {
                    alert('Vui lòng chọn loại đá quý để đặt cược');
                }
                confirmBtn.disabled = false;
                return;
            }

            const amount = parseFloat(document.getElementById('betAmount').value);
            if (!amount || amount <= 0) {
                if (typeof showToast === 'function') {
                    showToast('Vui lòng nhập số lượng đá quý hợp lệ', 'error');
                } else {
                    alert('Vui lòng nhập số lượng đá quý hợp lệ');
                }
                confirmBtn.disabled = false;
                return;
            }

            // Lưu thông tin bet ở client để hiển thị kết quả ngay lập tức
            if (currentRound) {
                const gem = GEM_TYPES[selectedGemType];
                clientBetInfo = {
                    round_number: currentRound.round_number,
                    gem_type: selectedGemType,
                    gem_name: gem ? gem.name : selectedGemType,
                    amount: amount,
                    payout_rate: gem ? gem.payoutRate : 1.95,
                    placed_at: Date.now()
                };

                // Lưu vào localStorage để layout xử lý khi round finish
                localStorage.setItem('clientBetInfo', JSON.stringify(clientBetInfo));
            }

            // Call API ở background (không await để không block UI)
            const apiCall = fetch('{{ route('explore.bet') }}', {
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
            }).then(async (response) => {
                const data = await response.json();

                if (response.ok && data.success) {
                    // Update balance
                    if (data.new_balance !== undefined) {
                        document.getElementById('userBalance').textContent = parseFloat(data.new_balance)
                            .toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '$';
                    }

                    // Reload my bet (immediate call after bet)
                    loadMyBet(true);
                } else {
                    // Nếu API call fail, xóa client bet info
                    clientBetInfo = null;
                    localStorage.removeItem('clientBetInfo');
                    confirmBtn.disabled = false;
                    if (typeof showToast === 'function') {
                        showToast(data.error || 'Có lỗi xảy ra khi đặt cược', 'error');
                    } else {
                        alert(data.error || 'Có lỗi xảy ra khi đặt cược');
                    }
                }
            }).catch((error) => {
                // Nếu API call fail, xóa client bet info
                clientBetInfo = null;
                localStorage.removeItem('clientBetInfo');
                confirmBtn.disabled = false;
                if (typeof showToast === 'function') {
                    showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
                } else {
                    alert('Có lỗi xảy ra. Vui lòng thử lại.');
                }
            });

            // Không cần await, để API chạy ở background
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

            // Nếu chuyển sang tab Signal, chỉ load lần đầu nếu chưa có data
            if (tabName === 'signal') {
                if (!signalTabLoaded) {
                    loadRecentRounds();
                    signalTabLoaded = true;
                } else {
                    // Nếu đã load rồi, chỉ update grid với data hiện có
                    updateSignalGridWithRounds();
                }
            }
        }

        // Load recent rounds for signal tab (chỉ gọi 1 lần khi mở tab lần đầu)
        // Load từ server để tất cả user thấy giống nhau
        // Server trả về 60 rounds: cột 1+2 (40 rounds) và cột 3 (20 rounds)
        async function loadRecentRounds() {
            try {
                const response = await fetch('{{ route('explore.signal-grid-rounds') }}');
                const rounds = await response.json();

                if (rounds && Array.isArray(rounds)) {
                    // Lấy tất cả 60 rounds từ server
                    signalGridRounds = rounds;
                    updateSignalGridWithRounds();
                }
            } catch (error) {
                signalGridRounds = [];
                updateSignalGridWithRounds();
            }
        }
        
        // Load and display 13 recent rounds results
        async function loadRecentRoundsDisplay() {
            const container = document.getElementById('recentRoundsContainer');
            if (!container) return;
            
            try {
                // Try to use signalGridRounds if available, otherwise fetch from API
                let rounds = [];
                if (signalGridRounds && signalGridRounds.length > 0) {
                    // Get last 13 rounds (oldest to newest, left to right)
                    rounds = signalGridRounds.slice(-13);
                } else {
                    const response = await fetch('{{ route('explore.signal-grid-rounds') }}');
                    const allRounds = await response.json();
                    if (allRounds && Array.isArray(allRounds) && allRounds.length > 0) {
                        rounds = allRounds.slice(-13);
                    }
                }
                
                if (rounds.length === 0) {
                    container.innerHTML = '<div class="text-gray-400 text-xs">Chưa có dữ liệu</div>';
                    return;
                }
                
                // Clear container
                container.innerHTML = '';
                
                // Display 13 rounds (or less if not enough data)
                // Oldest on left, newest on right
                rounds.forEach((round, index) => {
                    const result = round.admin_set_result || round.final_result || 'thachanh';
                    const gem = GEM_TYPES[result] || GEM_TYPES['thachanh'];
                    
                    const roundIcon = document.createElement('div');
                    roundIcon.className = 'flex items-center justify-center bg-gray-700 rounded-full w-6 h-6 p-0.5 flex-shrink-0';
                    
                    const iconImg = document.createElement('img');
                    iconImg.src = gem.icon;
                    iconImg.alt = gem.name;
                    iconImg.className = 'w-6 h-6 object-contain';
                    
                    roundIcon.appendChild(iconImg);
                    container.appendChild(roundIcon);
                });
            } catch (error) {
                console.error('Error loading recent rounds:', error);
                container.innerHTML = '<div class="text-gray-400 text-xs">Lỗi tải dữ liệu</div>';
            }
        }

        // Append round result mới vào signal grid (lưu vào server)
        async function appendRoundToSignalGrid(roundNumber, result) {
            if (!result) return;

            try {
                // Lấy CSRF token từ meta tag hoặc từ form
                let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                // Nếu không tìm thấy trong meta tag, thử lấy từ form
                if (!csrfToken) {
                    const csrfInput = document.querySelector('input[name="_token"]');
                    if (csrfInput) {
                        csrfToken = csrfInput.value;
                    }
                }

                if (!csrfToken) {
                    // Không có CSRF token, không thể gọi API
                    return;
                }

                // Gọi API để append vào server
                const response = await fetch('{{ route('explore.signal-grid-rounds.append') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        round_number: roundNumber,
                        final_result: result,
                    }),
                });

                // Kiểm tra response status
                if (!response.ok) {
                    // Nếu lỗi 419 (CSRF token mismatch), không làm gì
                    if (response.status === 419) {
                        return;
                    }
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                // Kiểm tra content-type trước khi parse JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return;
                }

                const data = await response.json();

                if (data.success && data.rounds) {
                    // Server trả về tất cả rounds sau khi append (có thể < 60 nếu chưa đầy, hoặc = 60 nếu đã shift)
                    // Cập nhật signalGridRounds từ server response
                    // Server đã xử lý shift nếu cần, nên chỉ cần cập nhật từ response
                    signalGridRounds = data.rounds;
                    // Đảm bảo không vượt quá 60 rounds
                    if (signalGridRounds.length > 60) {
                        signalGridRounds = signalGridRounds.slice(-60);
                    }
                    // LUÔN update grid ngay lập tức (không cần check tab)
                    updateSignalGridWithRounds();
                }
            } catch (error) {
                // Fallback: update local nếu API fail (chỉ khi không phải lỗi 419)
                if (!error.message || !error.message.includes('419')) {
                    const existingIndex = signalGridRounds.findIndex(r => r.round_number === roundNumber);
                    if (existingIndex !== -1) {
                        signalGridRounds[existingIndex].final_result = result;
                    } else {
                        // Thêm round mới vào cột 3
                        // Logic: Cột 1+2 (40 rounds), Cột 3 (20 rounds)
                        // Khi cột 3 đầy (60 rounds), server sẽ shift tự động
                        signalGridRounds.push({
                            round_number: roundNumber,
                            final_result: result,
                        });

                        // Nếu vượt quá 60 rounds, giữ 60 rounds cuối (server sẽ xử lý shift)
                        if (signalGridRounds.length > 60) {
                            signalGridRounds = signalGridRounds.slice(-60);
                        }
                    }
                    updateSignalGridWithRounds();
                }
            }
        }

        // Update signal grid
        // Tab signal là một slider không bao giờ dừng
        // Layout: 3 items (cột), mỗi item 4 hàng x 5 cột = 20 slots/item
        // - Item 1: rounds[0-19] (20 rounds, đã fill đầy)
        // - Item 2: rounds[20-39] (20 rounds, đã fill đầy)
        // - Item 3: rounds[40-59] (20 rounds, đang fill)
        // Khi item 3 đầy (60 rounds), server sẽ shift: Item 1 = rounds[20-39], Item 2 = rounds[40-59], Item 3 trống và bắt đầu fill lại
        function updateSignalGridWithRounds() {
            const signalGrid = document.getElementById('signalGrid');
            if (!signalGrid) return;

            // Clear grid
            signalGrid.innerHTML = '';

            // Tạo 3 cột
            const columns = [];
            for (let col = 0; col < 3; col++) {
                const columnDiv = document.createElement('div');
                columnDiv.className = 'flex flex-col gap-1';
                columns.push(columnDiv);
                signalGrid.appendChild(columnDiv);
            }

            // Tạo 3 cột, mỗi cột có 4 hàng, mỗi hàng 5 items = 20 items/cột
            // Fill theo cột dọc: cột 1 hàng 1, cột 1 hàng 2, ... cột 2 hàng 1, cột 2 hàng 2, ...
            // Layout: 3 cột x 4 hàng x 5 items = 60 items
            // Cột 1: rounds[0-19] (fill dọc: hàng 1: 0-4, hàng 2: 5-9, hàng 3: 10-14, hàng 4: 15-19)
            // Cột 2: rounds[20-39] (fill dọc: hàng 1: 20-24, hàng 2: 25-29, hàng 3: 30-34, hàng 4: 35-39)
            // Cột 3: rounds[40-59] (fill dọc: hàng 1: 40-44, hàng 2: 45-49, hàng 3: 50-54, hàng 4: 55-59)
            for (let colIndex = 0; colIndex < 3; colIndex++) {
                // Tạo 4 hàng cho mỗi cột
                for (let rowIndex = 0; rowIndex < 4; rowIndex++) {
                    // Tạo hàng nếu chưa có
                    let rowDiv = columns[colIndex].children[rowIndex];
                    if (!rowDiv) {
                        rowDiv = document.createElement('div');
                        rowDiv.className = 'flex gap-0.5';
                        columns[colIndex].appendChild(rowDiv);
                    }

                    // Tạo 5 items cho mỗi hàng
                    for (let itemInRow = 0; itemInRow < 5; itemInRow++) {
                        // Tính index trong mảng signalGridRounds
                        // Fill theo cột dọc: colIndex * 20 + rowIndex * 5 + itemInRow
                        // Cột 0: rounds[0-19], Cột 1: rounds[20-39], Cột 2: rounds[40-59]
                        const roundIndex = colIndex * 20 + rowIndex * 5 + itemInRow;

                        // Tạo item
                        const iconDiv = document.createElement('div');
                        iconDiv.className = 'flex items-center justify-center bg-gray-700 rounded-full w-6 h-6 p-0.5';

                        // Hiển thị icon nếu có round tại vị trí này
                        // Đảm bảo roundIndex hợp lệ và có data
                        if (roundIndex < signalGridRounds.length &&
                            signalGridRounds[roundIndex] &&
                            signalGridRounds[roundIndex].final_result) {
                            const gem = GEM_TYPES[signalGridRounds[roundIndex].final_result];
                            if (gem) {
                                const iconImg = document.createElement('img');
                                iconImg.src = gem.icon;
                                iconImg.alt = gem.name;
                                iconImg.className = 'w-6 h-6 object-contain';
                                iconDiv.appendChild(iconImg);
                            } else {
                                // Fallback
                                const iconImg = document.createElement('img');
                                iconImg.src = '{{ asset('images/icons/thachanh.png') }}';
                                iconImg.alt = 'Thạch Anh';
                                iconImg.className = 'w-6 h-6 object-contain';
                                iconDiv.appendChild(iconImg);
                            }
                        }
                        // Nếu không có round, chỉ hiển thị background (trống)

                        rowDiv.appendChild(iconDiv);
                    }
                }
            }
            
            // Update recent rounds display when signal grid is updated
            loadRecentRoundsDisplay();
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (clientTimerInterval) {
                clearInterval(clientTimerInterval);
            }
        });
    </script>
@endpush
