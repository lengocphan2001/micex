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
            
            // Update content
            if (titleEl) titleEl.textContent = 'Chúc mừng bạn !';
            if (amountEl) amountEl.textContent = '+' + parseFloat(amount).toFixed(2) + ' USDT';
            if (messageEl) messageEl.textContent = 'Phần thưởng đã được xử lý thành công và chuyển đến ví của bạn.';
            if (payoutRateEl && payoutRate) {
                payoutRateEl.textContent = parseFloat(payoutRate).toFixed(2) + 'x';
            }
            
            // Show popup
            popup.classList.add('show');
            
            // Auto close after 10 seconds
            setTimeout(() => {
                closeGlobalResultPopup();
            }, 10000);
        }
        
        // Global function to close result popup
        function closeGlobalResultPopup() {
            const popup = document.getElementById('resultPopup');
            if (popup) {
                popup.classList.remove('show');
                setTimeout(() => {
                    popup.classList.add('hidden');
                }, 400);
            }
        }
        
        // Check bet result from any page (polling)
        let betResultCheckInterval = null;
        let lastCheckedRoundNumber = null;
        
        function startBetResultPolling() {
            // Clear existing interval
            if (betResultCheckInterval) {
                clearInterval(betResultCheckInterval);
            }
            
            // Check every 2 seconds
            betResultCheckInterval = setInterval(async () => {
                try {
                    // Get client bet info from localStorage
                    const clientBetInfoStr = localStorage.getItem('clientBetInfo');
                    if (!clientBetInfoStr) return;
                    
                    const clientBetInfo = JSON.parse(clientBetInfoStr);
                    if (!clientBetInfo || !clientBetInfo.round_number) return;
                    
                    // Check if we already showed popup for this round
                    const popupShownForRound = localStorage.getItem('resultPopupShownForRound');
                    if (popupShownForRound && parseInt(popupShownForRound) === clientBetInfo.round_number) {
                        return; // Already shown
                    }
                    
                    // Fetch current round to check if it's finished
                    const response = await fetch('/api/explore/current-round');
                    if (!response.ok) return;
                    
                    const data = await response.json();
                    if (!data.round) return;
                    
                    const currentRound = data.round;
                    
                    // If round is finished and we have a bet for it
                    if (currentRound.status === 'finished' && 
                        currentRound.round_number === clientBetInfo.round_number &&
                        currentRound.final_result) {
                        
                        // Check if user won
                        const jackpotTypes = ['thachanhtim', 'ngusac', 'cuoc'];
                        const isJackpot = jackpotTypes.includes(currentRound.final_result);
                        const isWin = isJackpot || (clientBetInfo.gem_type === currentRound.final_result);
                        
                        if (isWin) {
                            // Get payout rate
                            let payoutRate = clientBetInfo.payout_rate;
                            if (isJackpot) {
                                // Fetch gem types to get jackpot payout rate
                                const gemTypesResponse = await fetch('/api/explore/gem-types');
                                if (gemTypesResponse.ok) {
                                    const gemTypesData = await gemTypesResponse.json();
                                    const jackpotGem = gemTypesData.gem_types.find(g => g.type === currentRound.final_result);
                                    if (jackpotGem) {
                                        payoutRate = parseFloat(jackpotGem.payout_rate);
                                    }
                                }
                            }
                            
                            const payoutAmount = clientBetInfo.amount * payoutRate;
                            
                            // Show popup
                            showGlobalResultPopup('won', payoutAmount, payoutRate);
                            
                            // Mark as shown
                            localStorage.setItem('resultPopupShownForRound', currentRound.round_number.toString());
                            
                            // Clear client bet info after showing
                            setTimeout(() => {
                                localStorage.removeItem('clientBetInfo');
                            }, 1000);
                        }
                    }
                } catch (error) {
                    // Silent fail
                }
            }, 2000);
        }
        
        // Start polling when page loads
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', startBetResultPolling);
        } else {
            startBetResultPolling();
        }
        
        // Stop polling when page unloads
        window.addEventListener('beforeunload', () => {
            if (betResultCheckInterval) {
                clearInterval(betResultCheckInterval);
            }
        });
    </script>
</body>
</html>

