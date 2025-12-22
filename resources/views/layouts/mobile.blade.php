<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Micex')</title>
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        body, * {
            font-family: 'Inter', sans-serif;
        }
        
        /* Prevent body scroll - only allow main content to scroll */
        html, body {
            height: 100%;
            overflow: hidden;
            position: fixed;
            width: 100%;
        }
        
        /* Smooth scrolling for main content */
        main {
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior: contain;
        }
        
        /* Hide scrollbar but keep functionality */
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        
        /* Global Result Popup Animation - Bottom Slide Up */
        #resultPopup {
            align-items: flex-end;
            justify-content: center;
            padding-bottom: 0;
            z-index: 9999 !important;
            position: fixed !important;
            left: 0;
            right: 0;
        }
        
        @media (min-width: 768px) {
            #resultPopup {
                left: auto;
                right: auto;
                max-width: 450px;
                margin-left: auto;
                margin-right: auto;
            }
        }
        
        #resultPopup {
            display: none;
        }
        
        #resultPopup.show {
            display: flex !important;
        }
        
        #resultPopup .popup-content {
            transform: translateY(100%);
            opacity: 0;
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.4s ease-out;
            width: 100%;
        }
        
        @media (min-width: 768px) {
            #resultPopup .popup-content {
                max-width: 450px;
                margin-left: auto;
                margin-right: auto;
            }
        }
        
        #resultPopup.show .popup-content {
            transform: translateY(0);
            opacity: 1;
        }
    </style>
</head>
<body class="bg-[#181A20] md:bg-gray-800 h-screen w-screen overflow-hidden flex items-center justify-center">
    <div class="w-full md:max-w-[450px] h-full flex flex-col mx-auto bg-gray-900 md:shadow-2xl text-white relative">
        <!-- Fixed Header -->
        <div class="fixed top-0 left-0 right-0 z-40 bg-gray-900 md:left-1/2 md:-translate-x-1/2 md:max-w-[450px]">
        @yield('header')
        </div>

        <!-- Scrollable Main Content -->
        <main class="flex-1 overflow-y-auto hide-scrollbar text-base leading-relaxed" style="background-color: #181A20; padding-top: 64px; padding-bottom: 80px; height: 100%;">
            @yield('content')
        </main>

        <!-- Fixed Bottom Nav -->
        @include('components.bottom-nav')
    </div>

    @include('components.toast')

    <!-- Global Result Popup (Modal Bottom) - Hiển thị từ bất kỳ trang nào -->
    <div id="resultPopup" class="fixed bottom-0 left-0 right-0 z-[9999] flex items-end justify-center hidden">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50" onclick="closeGlobalResultPopup()"></div>
        
        <!-- Popup Content -->
        <div class="popup-content relative bg-gradient-to-b from-[#2d1b69] to-[#1a0f3d] rounded-t-3xl shadow-2xl mb-0">
            <!-- Top Right Payout Rate Badge -->
            <div class="absolute top-4 right-4 bg-blue-500/80 rounded-lg px-3 py-1">
                <span id="resultPayoutRate" class="text-white text-sm font-semibold">1.95x</span>
            </div>
            
            <!-- Miner Character -->
            <div class="flex justify-center -mt-16 mb-4">
                <img src="{{ asset('images/result_image.png') }}" alt="Miner" class="w-32 h-32 object-contain">
            </div>
            
            <!-- Content -->
            <div class="px-6 pt-4 pb-6 text-center">
                <h2 id="resultTitle" class="text-white text-xl font-bold mb-3">Chúc mừng bạn !</h2>
                <p id="resultAmount" class="text-green-400 text-3xl font-bold mb-3">+0 USDT</p>
                <p id="resultMessage" class="text-white/90 text-sm mb-6 leading-relaxed">Phần thưởng đã được xử lý thành công và chuyển đến ví của bạn.</p>
                
                <!-- Confirm Button -->
                <button onclick="closeGlobalResultPopup()" class="bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white font-semibold px-8 py-3 rounded-xl w-full transition-colors">
                    Xác nhận
                </button>
            </div>
        </div>
    </div>

    @stack('scripts')
    
    <!-- Global Scripts for Result Popup -->
    <script>
        // Global function to show result popup from any page
        function showGlobalResultPopup(result, amount, payoutRate = null) {
            if (result !== 'won') return; // Chỉ hiển thị khi thắng
            
            const popup = document.getElementById('resultPopup');
            const titleEl = document.getElementById('resultTitle');
            const amountEl = document.getElementById('resultAmount');
            const messageEl = document.getElementById('resultMessage');
            const payoutRateEl = document.getElementById('resultPayoutRate');
            
            if (!popup) return;
            
            // Sanitize numbers to tránh NaN khi payoutRate/amount undefined
            const safePayoutRate = payoutRate !== null && !isNaN(payoutRate) ? Number(payoutRate) : null;
            const safeAmount = amount !== null && !isNaN(amount) ? Number(amount) : 0;

            // Update content
            if (titleEl) titleEl.textContent = 'Chúc mừng bạn !';
            if (amountEl) amountEl.textContent = '+' + safeAmount.toFixed(2) + ' USDT';
            if (messageEl) messageEl.textContent = 'Phần thưởng đã được xử lý thành công và chuyển đến ví của bạn.';
            if (payoutRateEl && safePayoutRate !== null) {
                payoutRateEl.textContent = safePayoutRate.toFixed(2) + 'x';
            }
            
            // Remove hidden class first
            popup.classList.remove('hidden');
            
            // Show popup first (display: flex)
            popup.style.display = 'flex';
            
            // Force reflow to ensure the element is rendered before adding show class
            // This ensures the animation triggers properly
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    popup.classList.add('show');
                });
            });
            
            // Auto close after 10 seconds
            setTimeout(() => {
                closeGlobalResultPopup();
            }, 10000);
        }
        
        // Global function to close result popup
        function closeGlobalResultPopup() {
            const popup = document.getElementById('resultPopup');
            if (popup) {
                // Remove show class to trigger exit animation
                popup.classList.remove('show');
                
                // Hide after animation completes
                setTimeout(() => {
                    popup.style.display = 'none';
                    popup.classList.add('hidden');
                }, 400);
            }
        }
        
        // Round finish detection and bet result checking
        // Wrap in IIFE to avoid conflicts with explore page
        (function() {
            let roundFinishCheckInterval = null;
            let lastCheckedRoundNumber = null;
            
            // Base time để tính round number và deadline
            const LAYOUT_BASE_TIME = new Date('2025-01-01T00:00:00Z').getTime();
            const LAYOUT_ROUND_DURATION = 60; // 60 giây mỗi round
            
            // Tính round number dựa trên base time
            // Sử dụng hàm từ explore nếu có, nếu không thì tự tính
            function layoutCalculateRoundNumber() {
                if (typeof calculateRoundNumber === 'function') {
                    return calculateRoundNumber();
                }
                const now = Date.now();
                const elapsed = Math.floor((now - LAYOUT_BASE_TIME) / 1000);
                return Math.floor(elapsed / LAYOUT_ROUND_DURATION) + 1;
            }
            
            // Tính deadline cho round hiện tại
            // Sử dụng hàm từ explore nếu có, nếu không thì tự tính
            function layoutCalculateRoundDeadline(roundNumber) {
                if (typeof calculateRoundDeadline === 'function') {
                    return calculateRoundDeadline(roundNumber);
                }
                const roundStartTime = LAYOUT_BASE_TIME + ((roundNumber - 1) * LAYOUT_ROUND_DURATION * 1000);
                return roundStartTime + (LAYOUT_ROUND_DURATION * 1000);
            }
            
            // Handle round finish event
            async function handleRoundFinish(roundNumber) {
            try {
                // Get client bet info from localStorage
                const clientBetInfoStr = localStorage.getItem('clientBetInfo');
                if (!clientBetInfoStr) return;
                
                const clientBetInfo = JSON.parse(clientBetInfoStr);
                if (!clientBetInfo || !clientBetInfo.round_number) return;
                
                // Only process if bet is for this round
                if (clientBetInfo.round_number !== roundNumber) return;
                
                // Check if we already showed popup for this round
                const popupShownForRound = localStorage.getItem('resultPopupShownForRound');
                if (popupShownForRound && parseInt(popupShownForRound) === roundNumber) {
                    return; // Already shown
                }
                
                // Fetch round result from API
                const response = await fetch(`/api/explore/round-result?round_number=${roundNumber}`);
                if (!response.ok) return;
                
                const data = await response.json();
                if (!data.result) return;
                
                const finalResult = data.result;
                
                // Check if user won
                const jackpotTypes = ['thachanhtim', 'ngusac', 'cuoc'];
                const isJackpot = jackpotTypes.includes(finalResult);
                const isWin = isJackpot || (clientBetInfo.gem_type === finalResult);
                
                if (isWin) {
                    // Get payout rate
                    let payoutRate = clientBetInfo.payout_rate;
                    if (isJackpot) {
                        // Fetch gem types to get jackpot payout rate
                        try {
                            const gemTypesResponse = await fetch('/api/explore/gem-types');
                            if (gemTypesResponse.ok) {
                                const gemTypesData = await gemTypesResponse.json();
                                const gemList = Array.isArray(gemTypesData) ? gemTypesData : gemTypesData.gem_types;
                                if (Array.isArray(gemList)) {
                                    const jackpotGem = gemList.find(g => g.type === finalResult);
                                    if (jackpotGem && jackpotGem.payout_rate) {
                                        payoutRate = parseFloat(jackpotGem.payout_rate);
                                    }
                                }
                            }
                        } catch (e) {
                            // fallback below
                        }
                        // Fallback nếu vẫn chưa có
                        if (!payoutRate || isNaN(payoutRate)) {
                            const defaultJackpotRates = { thachanhtim: 10, ngusac: 20, cuoc: 50 };
                            payoutRate = defaultJackpotRates[finalResult] || clientBetInfo.payout_rate || 1.95;
                        }
                    }
                    
                    const safePayoutRate = payoutRate && !isNaN(payoutRate) ? Number(payoutRate) : 1.95;
                    const safeAmountBet = clientBetInfo.amount && !isNaN(clientBetInfo.amount) ? Number(clientBetInfo.amount) : 0;
                    const payoutAmount = safeAmountBet * safePayoutRate;
                    
                    // Show popup
                    showGlobalResultPopup('won', payoutAmount, safePayoutRate);
                    
                    // Mark as shown
                    localStorage.setItem('resultPopupShownForRound', roundNumber.toString());
                    
                    // Refresh balance after winning
                    // Try to call loadMyBet from explore if available, otherwise fetch balance directly
                    setTimeout(() => {
                        if (typeof loadMyBet === 'function') {
                            // Call loadMyBet from explore page
                            loadMyBet(true);
                        } else {
                            // Fallback: fetch balance directly
                            fetch('/api/explore/my-bet')
                                .then(response => response.json())
                                .then(data => {
                                    if (data.balance !== undefined) {
                                        const balanceEl = document.getElementById('userBalance');
                                        if (balanceEl) {
                                            balanceEl.textContent = parseFloat(data.balance).toLocaleString('vi-VN') + '$';
                                        }
                                    }
                                })
                                .catch(error => {
                                    // Silent fail
                                });
                        }
                    }, 1500);
                    
                    // Clear client bet info after showing
                    setTimeout(() => {
                        localStorage.removeItem('clientBetInfo');
                    }, 1000);
                } else {
                    // User lost, just clear the storage
                    localStorage.removeItem('clientBetInfo');
                }
            } catch (error) {
                // Silent fail
                console.error('Error handling round finish:', error);
            }
        }
        
        // Check for round finish (client-side timer)
        function startRoundFinishDetection() {
            // Clear existing interval
            if (roundFinishCheckInterval) {
                clearInterval(roundFinishCheckInterval);
            }
            
            // Check every second
            roundFinishCheckInterval = setInterval(() => {
                try {
                    const now = Date.now();
                    const currentRoundNumber = layoutCalculateRoundNumber();
                    const deadline = layoutCalculateRoundDeadline(currentRoundNumber);
                    const countdown = Math.max(0, Math.floor((deadline - now) / 1000));
                    
                    // Calculate current second
                    let currentSecond = 0;
                    if (countdown > 0 && countdown <= LAYOUT_ROUND_DURATION) {
                        currentSecond = LAYOUT_ROUND_DURATION - countdown + 1;
                    }
                    
                    // Check if round just finished (currentSecond >= 60 or countdown === 0)
                    if (currentSecond >= 60 || countdown === 0) {
                        // Only handle once per round
                        if (lastCheckedRoundNumber !== currentRoundNumber) {
                            lastCheckedRoundNumber = currentRoundNumber;
                            
                            // Wait a bit for server to process round finish
                            setTimeout(() => {
                                handleRoundFinish(currentRoundNumber);
                            }, 1000);
                        }
                    }
                } catch (error) {
                    // Silent fail
                }
            }, 1000);
        }
        
        // Start detection when page loads
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', startRoundFinishDetection);
        } else {
            startRoundFinishDetection();
        }
        
        // Stop detection when page unloads
        window.addEventListener('beforeunload', () => {
            if (roundFinishCheckInterval) {
                clearInterval(roundFinishCheckInterval);
            }
        });
        })(); // End IIFE
    </script>
</body>
</html>

