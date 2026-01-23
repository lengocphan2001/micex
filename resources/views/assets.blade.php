@extends('layouts.mobile')

@section('title', 'Tài sản - Micex')

@section('header')
<header class="w-full px-4 py-4 flex items-center justify-center bg-gray-900 border-b border-gray-800">
    <h1 class="text-white text-base font-semibold">Tài sản của tôi</h1>
</header>
@endsection

@section('content')
<div class="px-4 py-4 space-y-4">
        <!-- Top Promotional Banner -->
        <div class="rounded-xl p-4 flex items-center justify-between"
            style="background: linear-gradient(282.43deg, #3958F5 -1.04%, #21338F 35.65%);">
            <div class="flex-1">
                <h2 class="text-white font-bold text-base mb-1">Nạp/Rút Crypto nhanh chóng <span class="text-blue-400">với
                        Micex</span></h2>
                <p class="text-gray-300 text-xs mb-3">Bắt đầu giao dịch tiền mã hoá bằng cách nạp tiền từ ngân hàng</p>
                <a href="{{ route('deposit') }}"
                    class="inline-flex items-center gap-2 bg-white text-gray-900 text-sm font-semibold rounded-full px-4 py-2">
                    <span>Nạp/Rút ngay bây giờ</span>
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
            <div class="w-28 h-28 flex-shrink-0">
                <img src="{{ asset('images/icons/assetnew.gif') }}" alt="Asset" class="w-full h-full object-contain">
            </div>
        </div>

        <!-- Total Assets Section -->
                <div class="space-y-2">
            <h3 class="text-white text-base font-semibold">Tổng tài sản</h3>
            <div class="flex items-center gap-2">
                <p class="text-white text-2xl font-bold" id="totalBalanceDisplay">
                    {{ number_format(auth()->user() ? (auth()->user()->balance ?? 0) + (auth()->user()->reward_balance ?? 0) : 0, 2, '.', ',') }}
                    USDT
                </p>
            </div>
            <p class="text-gray-400 text-sm">
                Khối lượng giao dịch cần hoàn thành : <span class="text-white font-semibold"
                    data-remaining-betting>{{ number_format(auth()->user() ? auth()->user()->betting_requirement ?? 0 : 0, 2, '.', ',') }}</span>
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-3">
            <a href="{{ route('deposit') }}"
                class="flex-1 bg-[#3958F5] hover:bg-blue-700 text-white font-semibold py-2 rounded-4xl text-center transition-colors">Nạp</a>
            <a href="{{ route('withdraw') }}"
                class="flex-1 bg-[#2C3B87] hover:bg-[#2C3B87] text-white font-semibold py-2 rounded-4xl text-center transition-colors">Rút</a>
            <button type="button" id="convertBtn"
                class="flex-1 bg-[#2C3B87] hover:bg-[#2C3B87] text-white font-semibold py-2 rounded-4xl text-center transition-colors">Chuyển
                đổi</button>
        </div>

        <!-- Wallet Balances Section -->
        <div class="space-y-3">
            <!-- Main Wallet -->
            <div class="bg-[#181A20] rounded-xl p-4 flex items-center justify-between">
                <div class="flex items-center gap-3 flex-1">
                    <div class="w-12 h-12 bg-green-500/20 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none"
                            xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <rect width="40" height="40" fill="url(#pattern0_1295_2694)" />
                            <defs>
                                <pattern id="pattern0_1295_2694" patternContentUnits="objectBoundingBox" width="1"
                                    height="1">
                                    <use xlink:href="#image0_1295_2694" transform="scale(0.00390625)" />
                                </pattern>
                                <image id="image0_1295_2694" width="256" height="256" preserveAspectRatio="none"
                                    xlink:href="data:image/png;base64,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" />
                            </defs>
                        </svg>

                    </div>
                    <div class="flex-1">
                        <p class="text-white font-semibold text-sm">Ví chính</p>
                        <p class="text-gray-400 text-xs">Số dư khả dụng</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-white font-bold text-lg" id="depositBalanceDisplay">
                        {{ number_format(auth()->user()->balance ?? 0, 2, '.', ',') }}</p>
                </div>
            </div>

            <!-- Reward Wallet -->
            <div class="bg-[#181A20] rounded-xl p-4 flex items-center justify-between">
                <div class="flex items-center gap-3 flex-1">
                    <div class="w-12 h-12 bg-green-500/20 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none"
                            xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <rect width="40" height="40" fill="url(#pattern0_1295_2694)" />
                            <defs>
                                <pattern id="pattern0_1295_2694" patternContentUnits="objectBoundingBox" width="1"
                                    height="1">
                                    <use xlink:href="#image0_1295_2694" transform="scale(0.00390625)" />
                                </pattern>
                                <image id="image0_1295_2694" width="256" height="256" preserveAspectRatio="none"
                                    xlink:href="data:image/png;base64,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" />
                            </defs>
                        </svg>

                    </div>
                    <div class="flex-1">
                        <p class="text-white font-semibold text-sm">Ví thưởng</p>
                        <p class="text-gray-400 text-xs">Tiền thưởng từ lì xì & Giftcode</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-white font-bold text-lg" id="rewardBalanceDisplay">
                        {{ number_format(auth()->user()->reward_balance ?? 0, 2, '.', ',') }}</p>
                </div>
            </div>
        </div>

        <!-- Giftcode Redemption Section -->
        <div class="bg-[#181A20] rounded-xl p-4 space-y-3">
            <h3 class="text-white text-sm font-semibold text-center">Phần thưởng Giftcode dành cho bạn !</h3>
                <form id="giftcodeForm" action="{{ route('giftcode.redeem') }}" method="POST">
                    @csrf
                <div class="rounded-lg p-3 flex items-center gap-2 border border-blue-400">
                        <input type="text" id="giftcodeInput" name="code" placeholder="Nhập Giftcode" 
                        class="flex-1 bg-transparent text-white text-sm placeholder-gray-500 outline-none">
                    <button type="button" id="clearGiftcode"
                        class="w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center text-white hover:bg-blue-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </form>
            <button type="submit" form="giftcodeForm" id="giftcodeSubmit"
                class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 rounded-lg text-sm transition-colors">Xác
                nhận</button>
    </div>
</div>

<!-- Transfer Reward Modal -->
<div id="transferRewardModal" class="fixed inset-0 z-[10000] flex items-center justify-center hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/70" onclick="closeTransferModal()"></div>
    
    <!-- Modal Content -->
    <div class="relative z-10 w-full max-w-sm mx-4 rounded-3xl overflow-hidden bg-gray-800 border border-gray-700">
        <!-- Header -->
        <div class="p-4 border-b border-gray-700">
            <h3 class="text-white text-lg font-semibold">Chuyển từ ví thưởng sang ví nạp</h3>
        </div>
        
        <!-- Content -->
        <div class="p-4 space-y-4">
            <div>
                <label class="text-sm text-gray-300 mb-2 block">Số dư ví thưởng:</label>
                <p class="text-white font-semibold text-lg" id="transferModalRewardBalance">0.00</p>
            </div>
            <div>
                    <label for="transferAmount" class="text-sm text-gray-300 mb-2 block">Số tiền muốn chuyển (tối thiểu
                        5):</label>
                <input type="number" id="transferAmount" step="0.01" min="5" 
                       class="w-full bg-gray-700 text-white rounded-lg px-3 py-2 outline-none border border-gray-600 focus:border-blue-500"
                       placeholder="Nhập số tiền">
            </div>
                <div id="transferError"
                    class="hidden bg-red-500/20 border border-red-500 text-red-200 text-sm rounded-lg px-3 py-2"></div>
        </div>
        
        <!-- Footer -->
        <div class="p-4 border-t border-gray-700 flex gap-3">
            <button type="button" onclick="closeTransferModal()" 
                    class="flex-1 bg-gray-700 hover:bg-gray-600 text-white font-semibold py-2.5 rounded-lg transition-colors">
                Hủy
            </button>
            <button type="button" id="confirmTransferBtn" 
                    class="flex-1 bg-[#2d59ff] hover:bg-[#2448d1] text-white font-semibold py-2.5 rounded-lg transition-colors">
                Xác nhận
            </button>
        </div>
    </div>
</div>

<!-- Giftcode Success Modal -->
<div id="giftcodeSuccessModal" class="fixed inset-0 z-[10000] flex items-center justify-center hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/70" onclick="closeGiftcodeModal(event)"></div>
    
    <!-- Modal Content -->
        <div class="relative z-10 w-full max-w-sm mx-4 rounded-3xl overflow-visible"
            style="background: linear-gradient(114.45deg, #3958F5 3.99%, #111838 19.52%, #111838 78.39%, #3958F5 107.73%);">
        <!-- Close Button -->
            <button onclick="closeGiftcodeModal(event)"
                class="absolute top-4 right-4 z-[50] w-8 h-8 flex items-center justify-center bg-white/20 hover:bg-white/30 rounded-full transition-colors pointer-events-auto cursor-pointer">
                <svg class="w-5 h-5 text-white pointer-events-none" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        
        <!-- Image - Nổi lên trên -->
        <div class="flex justify-center -mt-14 relative z-30">
                <img src="{{ asset('images/icons/giftcodemodalnew.png') }}" alt="Gift"
                    class="w-fit h-fit object-fit">
        </div>
        
        <!-- Text Content -->
        <div class="px-6 pt-4 pb-8 text-center">
            <h2 class="text-white text-2xl font-bold mb-3">Chúc mừng bạn !</h2>
            <p id="giftcodeAmount" class="text-green-400 text-3xl font-bold mb-3">0 USDT</p>
            <p class="text-[#FFFFFF80] text-[13px] leading-relaxed">Nhận thưởng thành công từ mã quà tặng của Micex</p>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Giftcode Success Modal Animation */
    #giftcodeSuccessModal {
        opacity: 0;
        transition: opacity 0.3s ease-out;
    }
    
    #giftcodeSuccessModal.show {
        opacity: 1;
    }
    
    #giftcodeSuccessModal .relative {
        transform: scale(0.9);
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    
    #giftcodeSuccessModal.show .relative {
        transform: scale(1);
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const giftcodeForm = document.getElementById('giftcodeForm');
        const giftcodeInput = document.getElementById('giftcodeInput');
        const giftcodeSubmit = document.getElementById('giftcodeSubmit');
        const clearGiftcode = document.getElementById('clearGiftcode');

        if (clearGiftcode) {
            clearGiftcode.addEventListener('click', function() {
                giftcodeInput.value = '';
            });
        }

        if (giftcodeForm) {
            giftcodeForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const code = giftcodeInput.value.trim().toUpperCase();
                if (!code) {
                    if (typeof showToast === 'function') {
                        showToast('Vui lòng nhập mã giftcode.', 'error');
                    }
                    return;
                }

                giftcodeSubmit.disabled = true;
                giftcodeSubmit.textContent = 'Đang xử lý...';

                // Set code value to uppercase
                giftcodeInput.value = code;

                // Get CSRF token from form
                    const csrfToken = this.querySelector('input[name="_token"]')?.value ||
                        document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                const formData = new FormData(this);

                try {
                    const response = await fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || '',
                        },
                    });

                    const data = await response.json();

                    if (response.ok && data.message) {
                        giftcodeInput.value = '';
                        
                        // Show success modal
                        if (data.value !== undefined) {
                            showGiftcodeModal(data.value);
                        } else if (typeof showToast === 'function') {
                            showToast(data.message, 'success');
                        }
                        
                        // Update balances if provided
                        if (data.balance !== undefined || data.reward_balance !== undefined) {
                            loadWalletBalances();
                        }
                        
                        // Update betting requirement if provided
                        if (data.betting_requirement !== undefined) {
                            // Find the element showing betting requirement by data attribute
                                const remainingBettingEl = document.querySelector(
                                    '[data-remaining-betting]');
                            if (remainingBettingEl) {
                                    const formattedValue = parseFloat(data.betting_requirement)
                                        .toLocaleString('en-US', {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        });
                                remainingBettingEl.textContent = formattedValue;
                            } else {
                                // Try to find by text content
                                const textToFind = 'Vòng cược chưa hoàn thành';
                                const allDivs = document.querySelectorAll('div');
                                for (const div of allDivs) {
                                    if (div.textContent && div.textContent.includes(textToFind)) {
                                            const span = div.querySelector(
                                                'span.font-semibold.text-white');
                                        if (span) {
                                                const formattedValue = parseFloat(data
                                                    .betting_requirement).toLocaleString('en-US', {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2
                                                });
                                            span.textContent = formattedValue;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                        
                        // Reload page after modal is closed (or after delay if no modal)
                        // The modal will handle reload when closed
                    } else {
                        if (typeof showToast === 'function') {
                            showToast(data.message || 'Có lỗi xảy ra.', 'error');
                        }
                    }
                } catch (error) {
                    if (typeof showToast === 'function') {
                        showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
                    }
                } finally {
                    giftcodeSubmit.disabled = false;
                    giftcodeSubmit.textContent = 'Xác nhận';
                }
            });
        }
    });

    // Load wallet balances
    async function loadWalletBalances() {
        try {
            const response = await fetch('{{ route('wallet.balances') }}', {
                    headers: {
                        'Accept': 'application/json'
                    }
            });
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    // Update displays
                    const totalEl = document.getElementById('totalBalanceDisplay');
                    const depositEl = document.getElementById('depositBalanceDisplay');
                    const rewardEl = document.getElementById('rewardBalanceDisplay');
                    const transferBtn = document.getElementById('transferRewardBtn');
                    
                    if (totalEl) {
                        const total = parseFloat(data.total_balance || 0);
                            totalEl.textContent = total.toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }) + ' USDT';
                    }
                    if (depositEl) {
                            depositEl.textContent = parseFloat(data.balance || 0).toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                    }
                    if (rewardEl) {
                            rewardEl.textContent = parseFloat(data.reward_balance || 0).toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                    }
                }
            }
        } catch (e) {
            console.error('Error loading balances:', e);
        }
    }

    // Transfer Reward Modal Functions
    function showTransferModal() {
        const modal = document.getElementById('transferRewardModal');
        const rewardBalanceEl = document.getElementById('transferModalRewardBalance');
            const rewardBalance = parseFloat(document.getElementById('rewardBalanceDisplay')?.textContent.replace(/,/g,
                '') || 0);
        
        if (modal && rewardBalanceEl) {
                rewardBalanceEl.textContent = rewardBalance.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            document.getElementById('transferAmount').value = '';
            document.getElementById('transferError').classList.add('hidden');
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
        }
    }

    function closeTransferModal() {
        const modal = document.getElementById('transferRewardModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
            document.getElementById('transferAmount').value = '';
            document.getElementById('transferError').classList.add('hidden');
        }
    }

    // Transfer button click handler
    document.addEventListener('DOMContentLoaded', function() {
        const transferBtn = document.getElementById('transferRewardBtn');
            const convertBtn = document.getElementById('convertBtn');
        const confirmTransferBtn = document.getElementById('confirmTransferBtn');
        
        if (transferBtn) {
            transferBtn.addEventListener('click', showTransferModal);
        }

            if (convertBtn) {
                convertBtn.addEventListener('click', function() {
                    // Open transfer modal for converting reward to deposit
                    showTransferModal();
                });
            }
        
        if (confirmTransferBtn) {
            confirmTransferBtn.addEventListener('click', async function() {
                const amountInput = document.getElementById('transferAmount');
                const errorEl = document.getElementById('transferError');
                const amount = parseFloat(amountInput.value);
                
                // Validation
                if (!amount || isNaN(amount) || amount < 5) {
                    errorEl.textContent = 'Số tiền tối thiểu là 5 đá quý.';
                    errorEl.classList.remove('hidden');
                    return;
                }
                
                    const rewardBalance = parseFloat(document.getElementById('rewardBalanceDisplay')
                        ?.textContent.replace(/,/g, '') || 0);
                if (amount > rewardBalance) {
                    errorEl.textContent = 'Số tiền vượt quá số dư ví thưởng.';
                    errorEl.classList.remove('hidden');
                    return;
                }
                
                // Disable button
                confirmTransferBtn.disabled = true;
                confirmTransferBtn.textContent = 'Đang xử lý...';
                
                try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content');
                        const response = await fetch(
                            '{{ route('wallet.transfer-reward-to-deposit') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || '',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                                body: JSON.stringify({
                                    amount: amount
                                })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        if (typeof showToast === 'function') {
                            showToast(data.message || 'Chuyển tiền thành công!', 'success');
                        }
                        closeTransferModal();
                        loadWalletBalances();
                    } else {
                        errorEl.textContent = data.message || 'Có lỗi xảy ra.';
                        errorEl.classList.remove('hidden');
                    }
                } catch (e) {
                    errorEl.textContent = 'Có lỗi xảy ra. Vui lòng thử lại.';
                    errorEl.classList.remove('hidden');
                } finally {
                    confirmTransferBtn.disabled = false;
                    confirmTransferBtn.textContent = 'Xác nhận';
                }
            });
        }
        
        // Load balances on page load
        loadWalletBalances();
    });

    // Giftcode Success Modal Functions
    function showGiftcodeModal(amount) {
        const modal = document.getElementById('giftcodeSuccessModal');
        const amountEl = document.getElementById('giftcodeAmount');
        
        if (modal && amountEl) {
            // Format amount
                const formattedAmount = parseFloat(amount).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            amountEl.textContent = formattedAmount + ' USDT';
            
            // Show modal
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
            
            // Add animation
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    modal.classList.add('show');
                });
            });
        }
    }

    function closeGiftcodeModal(event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        const modal = document.getElementById('giftcodeSuccessModal');
        if (modal) {
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
                modal.classList.add('hidden');
                // Reload page after modal closes
                window.location.reload();
            }, 300);
        }
    }
</script>
@endpush
