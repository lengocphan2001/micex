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

        .gem-card.selected[data-gem-type="kcxanh"] {
            background: #0170CC;
        }

        .gem-card.selected[data-gem-type="daquy"] {
            background: #7312E9;
        }

        .gem-card.selected[data-gem-type="kcdo"] {
            background: #FE4555;
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
        <h1 class="text-white text-base font-semibold">Khai thác 60s</h1>
        <div class="w-6"></div>
    </header>
@endsection

@section('content')
    <div class="space-y-0">
        <!-- Top Blue Header -->
        <div class="bg-[#4262FF] rounded-t-xl h-2"></div>

        <!-- Miner Video (Black Center Area) -->
        <div class="bg-black rounded-none overflow-hidden min-h-[253px] relative">
            <!-- MP4 cho 30 giây đầu -->
            <video id="minerVideoFirst30" class="object-cover w-full h-fit" muted autoplay loop playsinline preload="auto"
                style="opacity: 1; display: none;" aria-label="Miner Start">
                <source src="{{ asset('videos/222.mp4') }}" type="video/mp4">
            </video>
            <!-- MP4 cho 30 giây cuối -->
            <video id="minerVideoLast30" class="object-cover w-full h-fit" muted autoplay loop playsinline preload="auto"
                style="opacity: 1; display: none;" aria-label="Miner End">
                <source src="{{ asset('videos/111.mp4') }}" type="video/mp4">
            </video>
        </div>

        <!-- Bottom Blue Footer -->
        <div class="bg-[#4262FF] rounded-b-xl p-4 flex items-center">
            <!-- Left: Round Number -->
            <div class="flex-1 text-white text-sm font-medium flex justify-start">
                <span class="text-base italic" id="roundNumber">No : -</span>
            </div>
            
            <!-- Vertical Dashed Divider (Center) -->
            <div class="border-l-2 border-dashed border-[#37383B] h-8 mx-4"></div>
            
            <!-- Right: Timer -->
            <div class="flex-1 flex items-center justify-end gap-1">
                <!-- Minutes: First digit -->
                <div class="bg-white text-[#3958F5] rounded w-6 h-8 flex items-center justify-center font-bold text-base shadow"
                    id="minute1">0</div>
                <!-- Minutes: Second digit -->
                <div class="bg-white text-[#3958F5] rounded w-6 h-8 flex items-center justify-center font-bold text-base shadow"
                    id="minute2">0</div>
                <!-- Colon separator -->
                <div class="bg-white text-[#3958F5] rounded w-6 h-8 flex items-center justify-center font-bold text-base shadow text-center">
                    :
                </div>
                <!-- Seconds: First digit -->
                <div class="bg-white text-[#3958F5] rounded w-6 h-8 flex items-center justify-center font-bold text-base shadow"
                    id="second1">0</div>
                <!-- Seconds: Second digit -->
                <div class="bg-white text-[#3958F5] rounded w-6 h-8 flex items-center justify-center font-bold text-base shadow"
                    id="second2">0</div>
            </div>
        </div>


        <!-- Recent Rounds Results -->
        <!-- Tabs -->
        <div class="flex items-center gap-8 px-8 p-3">
            <button id="tab-search" class="tab-button text-white font-semibold border-b-2 border-blue-500 cursor-pointer flex items-center gap-2"
                onclick="switchTab('search')">
                <span>Search</span>
                <svg id="tab-search-icon" class="w-[34px] h-[34px] transition-transform duration-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 34 34" fill="none">
                    <path d="M17 12.3958C17.5808 12.3958 18.0625 12.8775 18.0625 13.4583L18.0625 21.9583C18.0625 22.5392 17.5808 23.0208 17 23.0208C16.4192 23.0208 15.9375 22.5392 15.9375 21.9583L15.9375 13.4583C15.9375 12.8775 16.4192 12.3958 17 12.3958Z" fill="white"/>
                    <path d="M17.0001 10.9792C17.2693 10.9792 17.5385 11.0783 17.751 11.2908L22.001 15.5408C22.4118 15.9517 22.4118 16.6317 22.001 17.0425C21.5901 17.4533 20.9101 17.4533 20.4993 17.0425L17.0001 13.5433L13.501 17.0425C13.0901 17.4533 12.4101 17.4533 11.9993 17.0425C11.5885 16.6317 11.5885 15.9517 11.9993 15.5408L16.2493 11.2908C16.4618 11.0783 16.731 10.9792 17.0001 10.9792Z" fill="white"/>
                </svg>
            </button>
            <button id="tab-signal" class="tab-button text-gray-400 font-semibold cursor-pointer flex items-center gap-2"
                onclick="switchTab('signal')">
                <span>Signal</span>
                <svg id="tab-signal-icon" class="w-[34px] h-[34px] transition-transform duration-300 rotate-180" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 34 34" fill="none">
                    <path d="M17 12.3958C17.5808 12.3958 18.0625 12.8775 18.0625 13.4583L18.0625 21.9583C18.0625 22.5392 17.5808 23.0208 17 23.0208C16.4192 23.0208 15.9375 22.5392 15.9375 21.9583L15.9375 13.4583C15.9375 12.8775 16.4192 12.3958 17 12.3958Z" fill="white"/>
                    <path d="M17.0001 10.9792C17.2693 10.9792 17.5385 11.0783 17.751 11.2908L22.001 15.5408C22.4118 15.9517 22.4118 16.6317 22.001 17.0425C21.5901 17.4533 20.9101 17.4533 20.4993 17.0425L17.0001 13.5433L13.501 17.0425C13.0901 17.4533 12.4101 17.4533 11.9993 17.0425C11.5885 16.6317 11.5885 15.9517 11.9993 15.5408L16.2493 11.2908C16.4618 11.0783 16.731 10.9792 17.0001 10.9792Z" fill="white"/>
                </svg>
            </button>
        </div>

        <!-- Tab Content: Search -->
        <div id="tab-content-search" class="tab-content space-y-4 px-2  pb-4">
            <!-- Recent Rounds Results -->
            <div class="flex flex-col items-center gap-1">
                <!-- Badge container (outside) -->
                <div id="recentRoundsBadges" class="flex items-center justify-center gap-1.5 w-full px-2">
                    <!-- Badges will be populated by JavaScript -->
                </div>
                
                <!-- Rounds container -->
                <div class="flex items-center justify-center w-full">
                    <div id="recentRoundsContainer" class="w-full rounded-[40px] bg-[#111111] py-2 flex items-center justify-center gap-1.5 overflow-x-auto border border-[#3958F5]" style="opacity: 1; transform: rotate(0deg);">
                        <!-- Rounds will be populated by JavaScript (14 items on desktop, 12 items on mobile) -->
                        <div class="text-gray-400 text-xs p-1">Đang tải...</div>
                    </div>
                </div>
            </div>

            <!-- Gem Cards -->
            
        </div>

        <div id="tab-content-signal" class="tab-content flex justify-center hidden space-y-4 px-4 pb-4">
            <!-- Signal Grid: Hiển thị 48 rounds gần nhất (3 cột x 4 hàng x 4 items), mỗi round là 1 icon -->
            <div id="signalGrid" class="grid grid-cols-3 gap-8">
                <!-- Sẽ được tạo động từ API -->
            </div>
        </div>

        <div class="px-4 py-6 bg-[#111111] border-b border-white/10">
            <div class="grid grid-cols-3 gap-2" id="gemCards">
                <!-- Cards will be populated by JavaScript -->
            </div>
        </div>

        

        <!-- Balance Display -->
        <div class="px-4 py-3">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center gap-2">
                    <p class="text-[#FFFFFFB2] text-[14px] font-medium">
                        Số dư:
                    </p>
                    <span id="userBalance" class="text-white text-[16px] font-medium">
                        {{ number_format(auth()->user()->balance ?? 0, 2, '.', ',') }}$
                    </span>
                </div>
                
                <button onclick="refreshBalance()" class="text-center cursor-pointer hover:opacity-80 transition-opacity">
                    <svg id="refreshBalanceIcon" xmlns="http://www.w3.org/2000/svg" width="15" height="16" viewBox="0 0 15 16" fill="none" class="transition-transform duration-300">
                        <path d="M1.56689 11.1755C1.81326 11.5861 2.11437 11.9693 2.45655 12.3115C4.975 14.83 9.06747 14.83 11.5996 12.3115C12.6261 11.285 13.2147 9.98464 13.4063 8.65698" stroke="#707797" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M0.649902 6.82298C0.841523 5.48164 1.43008 4.19498 2.45662 3.16844C4.97507 0.64999 9.06754 0.64999 11.5997 3.16844C11.9555 3.5243 12.243 3.90757 12.4893 4.3045" stroke="#707797" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M1.30664 14.8299V11.1755H4.9611" stroke="#707797" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12.7492 0.650024V4.30449H9.09473" stroke="#707797" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Amount input -->
        <div class="space-y-3 px-4">
            <div class="flex items-center gap-3">
                <div class="flex-1 px-3 flex items-center justify-between relative"
                    style="height: 47px; border-radius: 5px; border: 0.5px solid #FFFFFF80;">
                    <input type="number" min="0.01" step="0.01" value="10" id="betAmount"
                        class="bg-transparent text-white w-full outline-none pr-16" placeholder="Giá trị">
                    <button onclick="setMaxBetAmount()" class="absolute right-3 text-[#3958F5] font-medium text-sm hover:opacity-80">
                        All
                    </button>
                </div>
                <button id="confirmBetBtn" onclick="placeBet()"
                    class="text-white font-semibold cursor-pointer hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed transition-opacity whitespace-nowrap"
                    style="height: 47px; width: 100px; border-radius: 10px; background: #3958F5; padding-left: 16px; padding-right: 16px;">Đặt cược</button>
            </div>
            <div id="betInfo" class="text-xs text-gray-400">
                <p>Bạn đã đặt cược: <span id="betGemType" class="text-white"></span> - <span id="betAmountDisplay"
                        class="text-red-600"></span> đá quý</p>
                <p>Nếu thắng, bạn sẽ nhận: <span id="betPayout" class="text-green-400"></span> đá quý</p>
            </div>
        </div>

        <!-- Tab Content: Signal -->
        
    </div>
@endsection


@push('scripts')
    <script>
        // Gem types configuration - payout rates will be updated from API
        // 3 đá thường: user có thể đặt cược
        // 3 đá nổ hũ: chỉ admin set, user không thể đặt cược
        const GEM_TYPES = {
            'kcxanh': {
                name: 'Kim Cương Xanh',
                icon: '{{ asset('images/icons/kcxanh.png') }}',
                randomRate: 40,
                payoutRate: 1.95
            },
            'daquy': {
                name: 'Đá Quý',
                icon: '{{ asset('images/icons/daquy.png') }}',
                randomRate: 30,
                payoutRate: 5.95
            },
            'kcdo': {
                name: 'Kim Cương Đỏ',
                icon: '{{ asset('images/icons/kcdo.png') }}',
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
                // Map giá trị cũ sang giá trị mới (backward compatibility)
                const typeMap = {
                    'thachanh': 'kcxanh',
                    'kimcuong': 'kcdo'
                };
            
                
                gemTypes.forEach(gem => {
                    // Map type nếu là giá trị cũ
                    const mappedType = typeMap[gem.type] || gem.type;
                    
                    if (GEM_TYPES[mappedType]) {
                        GEM_TYPES[mappedType].payoutRate = parseFloat(gem.payout_rate);
                        GEM_TYPES[mappedType].randomRate = parseFloat(gem.random_rate); // Cập nhật random rate từ API
                    } else {
                        console.warn(`GEM_TYPES['${mappedType}'] not found for gem type:`, gem);
                    }
                });
                
                // Debug: Log final random rates for bettable gems
                const bettableGems = ['kcxanh', 'daquy', 'kcdo'];
                
                bettableGems.forEach(type => {
                    if (GEM_TYPES[type]) {
                        
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
        let signalGridRounds = []; // Lưu 48 rounds để hiển thị trong grid Signal (chỉ ở client)
        let signalTabLoaded = false; // Flag để biết tab Signal đã load chưa
        let minerVideoPhase = null; // phase hiện tại của miner video để tránh reset liên tục
        let randomIconInterval = null; // Interval để random icon nhanh trong 30 giây cuối


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
            if (!container) return;
            
            container.innerHTML = '';

            // Chỉ hiển thị 3 đá thường mà user có thể đặt cược
            const bettableGemTypes = ['kcxanh', 'daquy', 'kcdo'];

            bettableGemTypes.forEach(gemType => {
                const gem = GEM_TYPES[gemType];
                if (!gem) {
                    console.warn(`GEM_TYPES['${gemType}'] not found`);
                    return;
                }

                const card = document.createElement('button');
                card.className =
                    'gem-card bg-gray-800 text-white rounded-sm py-3 text-sm cursor-pointer transition-colors';
                card.onclick = () => selectGemType(gemType);
                card.innerHTML = `
                <span class="text-white text-xs font-medium">${gem.name}</span><br>
                <span class="text-[#FFFFFF80] text-xs font-medium payout-rate">${gem.payoutRate}x</span>
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

                // Reset icon in current round display
                const currentIconImg = document.getElementById('currentRoundIconImg');
                if (currentIconImg) {
                    currentIconImg.style.display = 'none';
                }

                // Clear random icon interval when new round starts
                if (randomIconInterval) {
                    clearInterval(randomIconInterval);
                    randomIconInterval = null;
                }

                // Reload recent rounds to update the list with finished round
                loadRecentRoundsDisplay();

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

                // KHÔNG random ở client nữa - kết quả sẽ được quyết định ở backend
                // Chỉ hiển thị "Chờ kết quả..." trong 30 giây cuối

                // Giây 60: Gọi API để random kết quả (nếu admin không set) hoặc lấy kết quả admin đã set
                if (currentSecond === 60 || countdown === 0) {
                    // Round đã finish, call API để random/lấy kết quả
                    if (!currentRound._checkingBetResult) {
                        currentRound._checkingBetResult = true;

                        // Gọi API ngay để random kết quả dựa vào tổng tiền đặt cược
                        (async () => {
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
                                    
                                    // Update current round display to show final result
                                    updateCurrentRoundDisplay();
                                } else {
                                    // Nếu API không trả về result, thử lại sau 1 giây
                                    setTimeout(async () => {
                                        try {
                                            const retryResponse = await fetch(
                                                `{{ route('explore.round-result') }}?round_number=${currentRound.round_number}`
                                            );
                                            const retryData = await retryResponse.json();
                                            if (retryData.result) {
                                                currentRound.final_result = retryData.result;
                                                if (retryData.admin_set_result) {
                                                    currentRound.admin_set_result = retryData.admin_set_result;
                                                }
                                                updateFinalResultCard();
                                                appendRoundToSignalGrid(currentRound.round_number, retryData.result);
                                                updateCurrentRoundDisplay();
                                            }
                                        } catch (retryError) {
                                            console.error('Error retrying round result:', retryError);
                                        }
                                    }, 1000);
                                }
                            } catch (error) {
                                console.error('Error fetching round result:', error);
                                // Thử lại sau 1 giây
                                setTimeout(async () => {
                                    try {
                                        const retryResponse = await fetch(
                                            `{{ route('explore.round-result') }}?round_number=${currentRound.round_number}`
                                        );
                                        const retryData = await retryResponse.json();
                                        if (retryData.result) {
                                            currentRound.final_result = retryData.result;
                                            if (retryData.admin_set_result) {
                                                currentRound.admin_set_result = retryData.admin_set_result;
                                            }
                                            updateFinalResultCard();
                                            appendRoundToSignalGrid(currentRound.round_number, retryData.result);
                                            updateCurrentRoundDisplay();
                                        }
                                    } catch (retryError) {
                                        console.error('Error retrying round result:', retryError);
                                    }
                                }, 1000);
                            }

                            currentRound._checkingBetResult = false;
                        })();
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

            // Update display (truyền countdown để tính remainingSeconds chính xác)
            updateRoundDisplay(currentSecond, phase, 0, countdown);

        // Refresh result card each tick (để 5s cuối chuyển sang “Chờ kết quả...”)
        updateFinalResultCard();

            // Radar image chỉ hiển thị cố định, không cần update
        }

        // Update round display
        // Countdown được tính toán dựa trên deadline (BASE_TIME)
        // Tất cả thiết bị sẽ hiển thị giống nhau vì dùng cùng BASE_TIME
        function updateRoundDisplay(currentSecond = null, phase = null, breakRemaining = null, countdown = null) {
            if (!currentRound) {
                return;
            }

            const sec = currentSecond !== null ? currentSecond : (currentRound.current_second || 0);
            const ph = phase !== null ? phase : (currentRound.phase || 'break');

            // Update round number (tính từ BASE_TIME)
            const roundNumberEl = document.getElementById('roundNumber');
            if (roundNumberEl) {
                const clientRoundNumber = calculateRoundNumber();
                roundNumberEl.textContent = `No : ${clientRoundNumber}`;
            }

            // Update countdown - tính từ countdown được truyền vào hoặc từ deadline
            let remainingSeconds = 0;
            if (countdown !== null) {
                // Sử dụng countdown được truyền vào (chính xác hơn)
                remainingSeconds = Math.max(0, countdown);
            } else if (ph === 'betting' || ph === 'result') {
                // Tính countdown từ deadline nếu không có countdown được truyền vào
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

            // Update bet button based on remainingSeconds and sec
            const confirmBtn = document.getElementById('confirmBetBtn');
            if (confirmBtn) {
                // Nếu round đã finish (remainingSeconds <= 0), enable button ngay
                if (remainingSeconds <= 0) {
                    if (myBet || clientBetInfo) {
                        confirmBtn.disabled = true;
                        confirmBtn.textContent = 'Đặt cược';
                    } else {
                        confirmBtn.disabled = false;
                        confirmBtn.textContent = 'Đặt cược';
                    }
                } else if ((sec > 30 && sec <= 60) || remainingSeconds <= 30) {
                    // 30 giây cuối: disable và hiển thị countdown
                    // Bao gồm cả khi sec > 30 hoặc remainingSeconds <= 30 (để hiển thị giây 60, 59)
                    confirmBtn.disabled = true;
                    // Hiển thị đếm ngược trên button (bao gồm cả giây 60 và 59)
                    const mins = Math.floor(remainingSeconds / 60);
                    const secs = remainingSeconds % 60;
                    const formattedTime = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
                    confirmBtn.textContent = formattedTime;
                } else if (myBet || clientBetInfo) {
                    // Đã đặt cược
                    confirmBtn.disabled = true;
                    confirmBtn.textContent = 'Đặt cược';
                } else {
                    // 30 giây đầu (sec <= 30 và remainingSeconds > 30): enable button
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = 'Đặt cược';
                }
            }

            // Update video source based on current second
            updateMinerVideo(sec);
            
            // Update current round display in recent rounds container
            updateCurrentRoundDisplay();
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
        // CHỈ random từ 3 đá bettable (kcxanh, daquy, kcdo), KHÔNG bao gồm đá nổ hũ
        function getGemForSecond(seed, second) {
            if (!seed) return 'kcxanh';

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

            // CHỈ sử dụng random rates từ 3 đá bettable (kcxanh, daquy, kcdo)
            // KHÔNG bao gồm đá nổ hũ (thachanhtim, ngusac, cuoc)
            const bettableGems = ['kcxanh', 'daquy', 'kcdo'];
            const rates = [];
            let totalRate = 0;
            
            bettableGems.forEach(type => {
                if (GEM_TYPES[type] && GEM_TYPES[type].randomRate) {
                    const rate = GEM_TYPES[type].randomRate;
                    totalRate += rate;
                    rates.push({
                        type: type,
                        rate: rate,
                        cumulative: totalRate
                    });
                } else {
                    console.warn(`getGemForSecond: GEM_TYPES['${type}'] not found or randomRate is missing`);
                }
            });

            // Normalize rand to totalRate range
            const normalizedRand = (rand / 100) * totalRate;

            // Debug log (chỉ log một vài lần để tránh spam)
            if (second % 10 === 0 || second === 31 || second === 60) {
            }

            for (const item of rates) {
                if (normalizedRand <= item.cumulative) {
                    if (second % 10 === 0 || second === 31 || second === 60) {
                        
                    }
                    return item.type;
                }
            }

            console.warn(`getGemForSecond: No gem selected for second ${second}, returning 'kcxanh' as fallback`);
            return 'kcxanh';
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
        const gemTypesArray = ['kcxanh', 'daquy', 'kcdo'];

        // Màu sắc cho mỗi loại đá (để tạo hiệu ứng nhấp nháy)
        const gemColors = {
            'kcxanh': 'rgba(1, 112, 204, 0.8)', // Blue (Kim Cương Xanh)
            'daquy': 'rgba(0, 191, 255, 0.8)', // Deep Sky Blue
            'kcdo': 'rgba(254, 69, 85, 0.8)', // Red (Kim Cương Đỏ)
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

            // Map giá trị cũ sang giá trị mới (backward compatibility)
            const resultMap = {
                'thachanh': 'kcxanh',
                'kimcuong': 'kcdo'
            };
            gemType = resultMap[gemType] || gemType;

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

            // Map giá trị cũ sang giá trị mới (backward compatibility)
            const resultMap = {
                'thachanh': 'kcxanh',
                'kimcuong': 'kcdo'
            };
            if (resultToShow) {
                resultToShow = resultMap[resultToShow] || resultToShow;
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
                // Map giá trị cũ sang giá trị mới (backward compatibility)
                const resultMap = {
                    'thachanh': 'kcxanh',
                    'kimcuong': 'kcdo'
                };
                const mappedPreviousResult = resultMap[previousRoundResult] || previousRoundResult;
                const gem = GEM_TYPES[mappedPreviousResult];
                if (gem) {
                    startResultGemBlinkAnimation(mappedPreviousResult);
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

        // Set max bet amount (All button)
        function setMaxBetAmount() {
            const balanceEl = document.getElementById('userBalance');
            if (balanceEl) {
                // Lấy text từ element và parse số
                const balanceText = balanceEl.textContent.replace(/[^0-9.]/g, '');
                const balance = parseFloat(balanceText) || 0;
                // Set giá trị tối đa
                document.getElementById('betAmount').value = balance.toFixed(2);
            }
        }

        // Refresh balance
        async function refreshBalance() {
            const refreshIcon = document.getElementById('refreshBalanceIcon');
            
            // Bắt đầu xoay icon
            if (refreshIcon) {
                refreshIcon.classList.add('refresh-spinning');
            }
            
            try {
                // Gọi API để lấy số dư mới nhất
                const response = await fetch('{{ route('explore.my-bet') }}');
                const data = await response.json();
                
                if (data.balance !== undefined) {
                    const balanceEl = document.getElementById('userBalance');
                    if (balanceEl) {
                        balanceEl.textContent = parseFloat(data.balance).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '$';
                    }
                }
            } catch (error) {
                console.error('Error refreshing balance:', error);
            } finally {
                // Dừng xoay icon
                if (refreshIcon) {
                    refreshIcon.classList.remove('refresh-spinning');
                }
            }
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

        // Tab switching function with toggle
        function switchTab(tabName) {
            const tabContent = document.getElementById('tab-content-' + tabName);
            const tabButton = document.getElementById('tab-' + tabName);
            const tabIcon = document.getElementById('tab-' + tabName + '-icon');
            const isCurrentlyActive = tabButton.classList.contains('text-white');
            const isCurrentlyVisible = !tabContent.classList.contains('hidden');

            // Nếu click vào tab đang active và đang hiển thị, toggle ẩn/hiện
            if (isCurrentlyActive && isCurrentlyVisible) {
                // Toggle visibility
                tabContent.classList.toggle('hidden');
                // Toggle icon rotation (rotate 180deg when hidden)
                if (tabIcon) {
                    if (tabContent.classList.contains('hidden')) {
                        tabIcon.classList.add('rotate-180');
                    } else {
                        tabIcon.classList.remove('rotate-180');
                    }
                }
                return;
            }

            // Nếu click vào tab khác hoặc tab đang ẩn, chuyển tab như bình thường
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });

            // Remove active state from all tabs and reset icons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('text-white', 'border-b-2', 'border-blue-500');
                button.classList.add('text-gray-400');
            });
            document.querySelectorAll('[id$="-icon"]').forEach(icon => {
                icon.classList.add('rotate-180');
            });

            // Show selected tab content
            tabContent.classList.remove('hidden');

            // Add active state to selected tab
            tabButton.classList.remove('text-gray-400');
            tabButton.classList.add('text-white', 'border-b-2', 'border-blue-500');

            // Rotate icon for active tab (pointing up when visible)
            if (tabIcon) {
                tabIcon.classList.remove('rotate-180');
            }

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
        // Server trả về 48 rounds: Cột 1+2 (32 rounds), Cột 3 (16 rounds)
        // Logic shift giữ nguyên như cũ, chỉ đổi từ 5 items/hàng thành 4 items/hàng
        async function loadRecentRounds() {
            try {
                const response = await fetch('{{ route('explore.signal-grid-rounds') }}');
                const rounds = await response.json();

                if (rounds && Array.isArray(rounds)) {
                    // Lấy tất cả 48 rounds từ server
                    signalGridRounds = rounds;
                    updateSignalGridWithRounds();
                }
            } catch (error) {
                signalGridRounds = [];
                updateSignalGridWithRounds();
            }
        }
        
        // Load and display recent rounds results (14 items on desktop, 12 items on mobile)
        async function loadRecentRoundsDisplay() {
            const container = document.getElementById('recentRoundsContainer');
            const badgesContainer = document.getElementById('recentRoundsBadges');
            if (!container || !badgesContainer) return;
            
            // Detect desktop vs mobile (using window width, typically 768px is the breakpoint)
            const isDesktop = window.innerWidth >= 768;
            const historyRoundsCount = isDesktop ? 13 : 11; // Desktop: 13 history + 1 current = 14, Mobile: 11 history + 1 current = 12
            
            try {
                // Try to use signalGridRounds if available, otherwise fetch from API
                let rounds = [];
                if (signalGridRounds && signalGridRounds.length > 0) {
                    // Get last N rounds (oldest to newest, left to right)
                    rounds = signalGridRounds.slice(-historyRoundsCount);
                } else {
                    const response = await fetch('{{ route('explore.signal-grid-rounds') }}');
                    const allRounds = await response.json();
                    if (allRounds && Array.isArray(allRounds) && allRounds.length > 0) {
                        rounds = allRounds.slice(-historyRoundsCount);
                    }
                }
                
                // Clear containers
                container.innerHTML = '';
                badgesContainer.innerHTML = '';
                
                // Display history rounds
                rounds.forEach((round, index) => {
                    let result = round.admin_set_result || round.final_result || 'kcxanh';
                    
                    // Map giá trị cũ sang giá trị mới (backward compatibility)
                    const resultMap = {
                        'thachanh': 'kcxanh',
                        'kimcuong': 'kcdo'
                    };
                    result = resultMap[result] || result;
                    
                    const gem = GEM_TYPES[result] || GEM_TYPES['kcxanh'];
                    
                    // Badge (only for the last round in history - index 11, outside container)
                    if (index === rounds.length - 1) {
                        const badge = document.createElement('div');
                        badge.className = 'flex flex-col items-center';
                        
                        const payoutText = document.createElement('span');
                        payoutText.className = 'text-gray-500 text-[10px]';
                        payoutText.textContent = gem.payoutRate ? gem.payoutRate.toFixed(2) + 'x' : '1.95x';
                        
                        const arrow = document.createElement('div');
                        arrow.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M13.4391 6H8.76658H4.55908C3.83908 6 3.47908 6.87 3.98908 7.38L7.87408 11.265C8.49658 11.8875 9.50908 11.8875 10.1316 11.265L11.6091 9.7875L14.0166 7.38C14.5191 6.87 14.1591 6 13.4391 6Z" fill="#636465"/></svg>';
                        
                        badge.appendChild(payoutText);
                        badge.appendChild(arrow);
                        badgesContainer.appendChild(badge);
                    } else {
                        // Empty space for badge alignment
                        const emptyBadge = document.createElement('div');
                        emptyBadge.className = 'flex-shrink-0';
                        emptyBadge.style.width = '24px'; // Approximate badge width
                        badgesContainer.appendChild(emptyBadge);
                    }
                    
                    // Icon container (inside container)
                    const roundIcon = document.createElement('div');
                    roundIcon.className = 'flex items-center justify-center bg-[#24253A] rounded-full w-6 h-6 p-0.5 flex-shrink-0';
                    
                    const iconImg = document.createElement('img');
                    iconImg.src = gem.icon;
                    iconImg.alt = gem.name;
                    iconImg.className = 'w-6 h-6 object-contain';
                    
                    roundIcon.appendChild(iconImg);
                    container.appendChild(roundIcon);
                });
                
                // Add empty space for badge alignment (for current round position)
                const emptyBadgeCurrent = document.createElement('div');
                emptyBadgeCurrent.className = 'flex-shrink-0';
                emptyBadgeCurrent.style.width = '24px';
                badgesContainer.appendChild(emptyBadgeCurrent);
                
                // Add current round icon container (inside container, will be updated dynamically)
                // Background always visible, only icon is hidden/shown
                const currentRoundIcon = document.createElement('div');
                currentRoundIcon.className = 'flex items-center justify-center bg-[#24253A] rounded-full w-6 h-6 p-0.5 flex-shrink-0';
                currentRoundIcon.id = 'currentRoundIcon';
                
                const currentIconImg = document.createElement('img');
                // Set empty src initially to ensure no icon is displayed
                currentIconImg.src = '';
                currentIconImg.alt = 'Current';
                currentIconImg.className = 'w-6 h-6 object-contain';
                currentIconImg.id = 'currentRoundIconImg';
                // Always hidden by default when append new empty slot
                currentIconImg.style.display = 'none';
                currentIconImg.style.visibility = 'hidden';
                currentIconImg.style.opacity = '0';
                
                currentRoundIcon.appendChild(currentIconImg);
                container.appendChild(currentRoundIcon);
                
                // Update current round display (will show icon if needed)
                updateCurrentRoundDisplay();
            } catch (error) {
                console.error('Error loading recent rounds:', error);
                container.innerHTML = '<div class="text-gray-400 text-xs">Lỗi tải dữ liệu</div>';
            }
        }
        
        // Get random gem from 3 bettable gems only (kcxanh, daquy, kcdo)
        function getRandomBettableGem(seed, second) {
            if (!seed) return 'kcxanh';
            
            const bettableGems = ['kcxanh', 'daquy', 'kcdo'];
            
            // Hash function for random
            const string = seed + '_' + second + '_bettable';
            let hash = 0;
            for (let i = 0; i < string.length; i++) {
                const char = string.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & 0x7FFFFFFF;
            }
            hash = (hash * 31 + second * 17) & 0x7FFFFFFF;
            const rand = (Math.abs(hash) % 10000) % 100 + 1;
            
            // Calculate rates for 3 bettable gems
            const rates = [];
            let totalRate = 0;
            bettableGems.forEach(type => {
                if (GEM_TYPES[type]) {
                    const rate = GEM_TYPES[type].randomRate || 33.33;
                    totalRate += rate;
                    rates.push({ type, rate, cumulative: totalRate });
                }
            });
            
            // Normalize to 100
            const normalizedRand = (rand / 100) * totalRate;
            
            for (const item of rates) {
                if (normalizedRand <= item.cumulative) {
                    return item.type;
                }
            }
            
            return 'kcxanh';
        }
        
        // Random icon quickly (called every 200ms during last 30 seconds)
        function randomIconQuickly() {
            if (!currentRound) {
                if (randomIconInterval) {
                    clearInterval(randomIconInterval);
                    randomIconInterval = null;
                }
                return;
            }
            
            const currentIconImg = document.getElementById('currentRoundIconImg');
            if (!currentIconImg) return;
            
            const sec = currentRound.current_second || 0;
            const finalResult = currentRound.admin_set_result || currentRound.final_result;
            
            // Only random if in last 30 seconds and no final result yet
            if (sec > 30 && sec <= 60 && !finalResult) {
                // Use current time in milliseconds for faster random variation
                const timeBasedSeed = currentRound.seed + '_' + Date.now() + '_' + Math.random();
                const randomResult = getRandomBettableGem(timeBasedSeed, sec);
                const gem = GEM_TYPES[randomResult] || GEM_TYPES['kcxanh'];
                
                currentIconImg.src = gem.icon;
                currentIconImg.alt = gem.name;
                currentIconImg.style.opacity = '1';
            } else {
                // Stop interval if conditions are no longer met
                if (randomIconInterval) {
                    clearInterval(randomIconInterval);
                    randomIconInterval = null;
                }
            }
        }
        
        // Update current round display (called every second when in last 30 seconds)
        function updateCurrentRoundDisplay() {
            if (!currentRound) {
                // Clear interval if round is null
                if (randomIconInterval) {
                    clearInterval(randomIconInterval);
                    randomIconInterval = null;
                }
                return;
            }
            
            const currentRoundIcon = document.getElementById('currentRoundIcon');
            const currentIconImg = document.getElementById('currentRoundIconImg');
            if (!currentRoundIcon || !currentIconImg) return;
            
            const sec = currentRound.current_second || 0;
            const ph = currentRound.phase || 'break';
            
            // Background always visible, only show/hide icon
            let finalResult = currentRound.admin_set_result || currentRound.final_result;
            
            // Map giá trị cũ sang giá trị mới (backward compatibility)
            if (finalResult) {
                const resultMap = {
                    'thachanh': 'kcxanh',
                    'kimcuong': 'kcdo'
                };
                finalResult = resultMap[finalResult] || finalResult;
            }
            
            // If round has finished result (sec >= 60 and has result), show it
            if (finalResult && sec >= 60) {
                // Stop random interval
                if (randomIconInterval) {
                    clearInterval(randomIconInterval);
                    randomIconInterval = null;
                }
                
                const gem = GEM_TYPES[finalResult] || GEM_TYPES['kcxanh'];
                currentIconImg.src = gem.icon;
                currentIconImg.alt = gem.name;
                currentIconImg.style.display = 'block';
                currentIconImg.style.visibility = 'visible';
                currentIconImg.style.opacity = '1';
            } else if (sec > 30 && sec <= 60 && !finalResult) {
                // Start fast random interval for last 30 seconds
                if (!randomIconInterval) {
                    randomIconInterval = setInterval(randomIconQuickly, 200); // Update every 200ms
                }
                currentIconImg.style.display = 'block';
                currentIconImg.style.visibility = 'visible';
                currentIconImg.style.opacity = '1';
            } else {
                // Stop random interval and hide icon
                if (randomIconInterval) {
                    clearInterval(randomIconInterval);
                    randomIconInterval = null;
                }
                // Hide icon during first 30 seconds (1-30), break phase, or when no result yet
                currentIconImg.style.display = 'none';
                currentIconImg.style.visibility = 'hidden';
                currentIconImg.style.opacity = '0';
                // Clear src to ensure no icon is displayed
                currentIconImg.src = '';
            }
        }

        // Append round result mới vào signal grid (lưu vào server)
        async function appendRoundToSignalGrid(roundNumber, result) {
            if (!result) return;

            // Map giá trị cũ sang giá trị mới (backward compatibility)
            const resultMap = {
                'thachanh': 'kcxanh',
                'kimcuong': 'kcdo'
            };
            result = resultMap[result] || result;

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
                    // Server trả về tất cả rounds sau khi append (có thể < 48 nếu chưa đầy, hoặc = 48 nếu đã shift)
                    // Cập nhật signalGridRounds từ server response
                    // Server đã xử lý shift nếu cần, nên chỉ cần cập nhật từ response
                    signalGridRounds = data.rounds;
                    // Đảm bảo không vượt quá 48 rounds
                    if (signalGridRounds.length > 48) {
                        signalGridRounds = signalGridRounds.slice(-48);
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
                        // Logic shift giữ nguyên như cũ (chỉ đổi từ 5 items/hàng thành 4 items/hàng):
                        // - Cột 1+2: rounds[0-31] (mỗi cột 16 rounds = 4 hàng x 4 items)
                        // - Cột 3: rounds[32-47] (16 rounds = 4 hàng x 4 items)
                        // Khi cột 3 đầy (48 rounds), server sẽ shift: Cột 1 = rounds[16-31], Cột 2 = rounds[32-47], Cột 3 trống
                        signalGridRounds.push({
                            round_number: roundNumber,
                            final_result: result,
                        });

                        // Nếu vượt quá 48 rounds, giữ 48 rounds cuối (server sẽ xử lý shift)
                        if (signalGridRounds.length > 48) {
                            signalGridRounds = signalGridRounds.slice(-48);
                        }
                    }
                    updateSignalGridWithRounds();
                }
            }
        }

        // Update signal grid
        // Tab signal là một slider không bao giờ dừng
        // Layout: 3 cột, mỗi cột 4 hàng x 4 items = 16 items/cột (thay đổi từ 5 items/hàng)
        // Logic shift giữ nguyên như cũ:
        // - Cột 1+2 (cột 0+1 trong code): rounds[0-31] (mỗi cột 16 rounds = 4 hàng x 4 items)
        // - Cột 3 (cột 2 trong code): rounds[32-47] (16 rounds = 4 hàng x 4 items)
        // - Khi cột 3 đầy (48 rounds), server sẽ shift: Cột 1 = rounds[16-31], Cột 2 = rounds[32-47], Cột 3 trống và bắt đầu fill lại
        // Tổng: 3 cột x 4 hàng x 4 items = 48 items
        function updateSignalGridWithRounds() {
            const signalGrid = document.getElementById('signalGrid');
            if (!signalGrid) return;

            // Clear grid
            signalGrid.innerHTML = '';

            // Tạo 3 cột
            const columns = [];
            for (let col = 0; col < 3; col++) {
                const columnDiv = document.createElement('div');
                columnDiv.className = 'flex flex-col gap-0.5';
                // Đảm bảo mỗi cột là hình vuông: 4 hàng x 4 items
                // Chiều rộng = 4 items * (w-6 + gap-0.5) = 4 * (24px + 2px) = 104px
                // Chiều cao = 4 hàng * (h-6 + gap-0.5) = 4 * (24px + 2px) = 104px
                columnDiv.style.aspectRatio = '1 / 1';
                columns.push(columnDiv);
                signalGrid.appendChild(columnDiv);
            }

            // Tạo 3 cột, mỗi cột có 4 hàng, mỗi hàng 4 items = 16 items/cột
            // Fill theo cột dọc: cột 1 hàng 1, cột 1 hàng 2, ... cột 2 hàng 1, cột 2 hàng 2, ...
            // Layout: 3 cột x 4 hàng x 4 items = 48 items
            // Cột 1 (colIndex=0): rounds[0-15] (fill dọc: hàng 1: 0-3, hàng 2: 4-7, hàng 3: 8-11, hàng 4: 12-15)
            // Cột 2 (colIndex=1): rounds[16-31] (fill dọc: hàng 1: 16-19, hàng 2: 20-23, hàng 3: 24-27, hàng 4: 28-31)
            // Cột 3 (colIndex=2): rounds[32-47] (fill dọc: hàng 1: 32-35, hàng 2: 36-39, hàng 3: 40-43, hàng 4: 44-47)
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

                    // Tạo 4 items cho mỗi hàng
                    for (let itemInRow = 0; itemInRow < 4; itemInRow++) {
                        // Tính index trong mảng signalGridRounds
                        // Fill theo cột dọc: colIndex * 16 + rowIndex * 4 + itemInRow
                        // Cột 0: rounds[0-15], Cột 1: rounds[16-31], Cột 2: rounds[32-47]
                        const roundIndex = colIndex * 16 + rowIndex * 4 + itemInRow;

                        // Tạo item
                        const iconDiv = document.createElement('div');
                        iconDiv.className = 'flex items-center justify-center bg-[#24253A] rounded-full w-6 h-6 p-0.5';

                        // Hiển thị icon nếu có round tại vị trí này
                        // Đảm bảo roundIndex hợp lệ và có data
                        if (roundIndex < signalGridRounds.length &&
                            signalGridRounds[roundIndex] &&
                            signalGridRounds[roundIndex].final_result) {
                            let result = signalGridRounds[roundIndex].final_result;
                            
                            // Map giá trị cũ sang giá trị mới (backward compatibility)
                            const resultMap = {
                                'thachanh': 'kcxanh',
                                'kimcuong': 'kcdo'
                            };
                            result = resultMap[result] || result;
                            
                            const gem = GEM_TYPES[result];
                            if (gem) {
                                const iconImg = document.createElement('img');
                                iconImg.src = gem.icon;
                                iconImg.alt = gem.name;
                                iconImg.className = 'w-6 h-6 object-contain';
                                iconDiv.appendChild(iconImg);
                            } else {
                                // Fallback
                                const iconImg = document.createElement('img');
                                iconImg.src = '{{ asset('images/icons/kcxanh.png') }}';
                                iconImg.alt = 'Kim Cương Xanh';
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
