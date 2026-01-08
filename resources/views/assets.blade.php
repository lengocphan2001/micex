@extends('layouts.mobile')

@section('title', 'Tài sản - Micex')

@section('header')
<header class="w-full px-4 py-4 flex items-center justify-center bg-gray-900 border-b border-gray-800">
    <h1 class="text-white text-base font-semibold">Tài sản của tôi</h1>
</header>
@endsection

@section('content')
<div class="px-4 py-4 space-y-4">
    <!-- Main wallet card -->
    <div class="rounded-t-2xl rounded-b-[30px] border border-blue-400/60 shadow-lg overflow-hidden" style="background: linear-gradient(180deg, #2d3dbf 0%, #15204f 100%);">
        <div class="p-4 sm:p-5">
            <div class="flex items-start justify-between">
                <div class="space-y-2">
                    <p class="text-white font-bold text-base leading-tight">Nạp/Rút Crypto nhanh chóng với <span class="text-[#FFBA25]">Micex</span></p>
                    <p class="text-[12px] text-blue-100 leading-tight">Bắt đầu giao dịch tiền mã hoá bằng<br>cách nạp tiền từ ngân hàng</p>
                    <div class="mt-2 inline-flex items-center gap-2 bg-white text-gray-900 text-sm font-semibold rounded-full px-5 py-2 shadow">
                        <span>Nạp/Rút ngay bây giờ</span>
                        <span class="inline-flex items-center justify-center w-6 h-6 bg-green-400 rounded-full">
                            <i class="fas fa-chevron-right text-gray-900 text-xs"></i>
                        </span>
                    </div>
                </div>
                <div class="w-28 h-28 sm:w-32 sm:h-32">
                    <img src="{{ asset('images/coin.png') }}" alt="Coin" class="w-full h-full object-contain">
                </div>
            </div>

            <div class="mt-4 space-y-3 text-white">
                <div>
                    <p class="text-sm text-blue-100">Tổng tài sản</p>
                    <p class="text-2xl font-bold flex items-center gap-1" id="totalBalanceDisplay">
                        {{ number_format(auth()->user() ? (auth()->user()->balance ?? 0) + (auth()->user()->reward_balance ?? 0) : 0, 2, '.', ',') }}
                        <img src="{{ asset('images/icons/coin_asset.png') }}" alt="Coin asset" class="w-5 h-5 object-contain">
                    </p>
                </div>
                
                <!-- Two Wallets Display -->
                <div class="space-y-2 pt-2">
                    <div class="flex items-center justify-between bg-white/10 rounded-lg px-3 py-2">
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-blue-100">Ví nạp:</span>
                            <span class="text-white font-semibold" id="depositBalanceDisplay">{{ number_format(auth()->user()->balance ?? 0, 2, '.', ',') }}</span>
                            <img src="{{ asset('images/icons/coin_asset.png') }}" alt="Coin asset" class="w-4 h-4 object-contain">
                        </div>
                    </div>
                    <div class="flex items-center justify-between bg-white/10 rounded-lg px-3 py-2">
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-blue-100">Ví thưởng:</span>
                            <span class="text-white font-semibold" id="rewardBalanceDisplay">{{ number_format(auth()->user()->reward_balance ?? 0, 2, '.', ',') }}</span>
                            <img src="{{ asset('images/icons/coin_asset.png') }}" alt="Coin asset" class="w-4 h-4 object-contain">
                        </div>
                        @if((auth()->user()->reward_balance ?? 0) >= 5)
                        <button type="button" id="transferRewardBtn" class="text-xs bg-green-500 hover:bg-green-600 text-white font-semibold px-3 py-1 rounded-full transition-colors">
                            Chuyển
                        </button>
                        @endif
                    </div>
                </div>
                
                <div class="text-sm text-blue-100 flex items-center gap-1">
                    Vòng cược chưa hoàn thành : <span class="font-semibold text-white" data-remaining-betting>{{ number_format(auth()->user() ? (auth()->user()->betting_requirement ?? 0) : 0, 2, '.', ',') }}</span> <span class="text-yellow-300">
                        <img src="{{ asset('images/icons/coin_asset.png') }}" alt="Coin asset" class="w-5 h-5 object-contain">
                    </span>
                </div>
                <div class="flex items-center gap-3 pt-1">
                    <a href="{{ route('deposit') }}" class="flex-1 bg-[#2d59ff] hover:bg-[#2448d1] text-white font-semibold py-2.5 rounded-full text-base shadow text-center">Nạp</a>
                    <a href="{{ route('withdraw') }}" class="flex-1 bg-[#2d59ff] hover:bg-[#2448d1] text-white font-semibold py-2.5 rounded-full text-base shadow text-center">Rút</a>
                </div>
            </div>
        </div>

        <div class="mt-4 w-full bg-[#0F1317] border border-[#3958F5] shadow-[0px_4px_4px_#3958F5] rounded-[30px] px-4 pb-4 sm:px-5 sm:pb-5">
            <div class="text-white text-base py-3 text-center">
                Phần thưởng Giftcode dành cho bạn !
            </div>
            <div class="mt-2 bg-[#1b1b1b] border border-gray-700 rounded-2xl p-3">
                <form id="giftcodeForm" action="{{ route('giftcode.redeem') }}" method="POST">
                    @csrf
                    <div class="flex items-center gap-2">
                        <input type="text" id="giftcodeInput" name="code" placeholder="Nhập Giftcode" 
                               class="flex-1 bg-transparent text-white text-base placeholder-gray-500 outline-none">
                        <button type="button" id="clearGiftcode" class="text-gray-400 hover:text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
            <div class="mt-3 flex justify-center">
                <button type="submit" form="giftcodeForm" id="giftcodeSubmit" class="w-fit bg-[#2d59ff] hover:bg-[#2448d1] text-white font-semibold py-2.5 px-4 rounded-full text-base shadow">Xác nhận</button>
            </div>
        </div>
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
                <label for="transferAmount" class="text-sm text-gray-300 mb-2 block">Số tiền muốn chuyển (tối thiểu 5):</label>
                <input type="number" id="transferAmount" step="0.01" min="5" 
                       class="w-full bg-gray-700 text-white rounded-lg px-3 py-2 outline-none border border-gray-600 focus:border-blue-500"
                       placeholder="Nhập số tiền">
            </div>
            <div id="transferError" class="hidden bg-red-500/20 border border-red-500 text-red-200 text-sm rounded-lg px-3 py-2"></div>
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
    <div class="relative z-10 w-full max-w-sm mx-4 rounded-3xl overflow-visible" style="background: linear-gradient(114.45deg, #3958F5 3.99%, #111838 19.52%, #111838 78.39%, #3958F5 107.73%);">
        <!-- Close Button -->
        <button onclick="closeGiftcodeModal(event)" class="absolute top-4 right-4 z-[50] w-8 h-8 flex items-center justify-center bg-white/20 hover:bg-white/30 rounded-full transition-colors pointer-events-auto cursor-pointer">
            <svg class="w-5 h-5 text-white pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        
        <!-- Image - Nổi lên trên -->
        <div class="flex justify-center -mt-14 relative z-30">
            <img src="{{ asset('images/icons/giftcodemodalnew.png') }}" alt="Gift" class="w-fit h-fit object-fit">
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
                const csrfToken = this.querySelector('input[name="_token"]')?.value 
                    || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

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
                            const remainingBettingEl = document.querySelector('[data-remaining-betting]');
                            if (remainingBettingEl) {
                                const formattedValue = parseFloat(data.betting_requirement).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                remainingBettingEl.textContent = formattedValue;
                            } else {
                                // Try to find by text content
                                const textToFind = 'Vòng cược chưa hoàn thành';
                                const allDivs = document.querySelectorAll('div');
                                for (const div of allDivs) {
                                    if (div.textContent && div.textContent.includes(textToFind)) {
                                        const span = div.querySelector('span.font-semibold.text-white');
                                        if (span) {
                                            const formattedValue = parseFloat(data.betting_requirement).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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
                headers: { 'Accept': 'application/json' }
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
                        const parts = totalEl.innerHTML.split('<');
                        totalEl.innerHTML = total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + (parts[1] || '');
                    }
                    if (depositEl) {
                        depositEl.textContent = parseFloat(data.balance || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    }
                    if (rewardEl) {
                        rewardEl.textContent = parseFloat(data.reward_balance || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    }
                    
                    // Show/hide transfer button
                    if (transferBtn) {
                        if (parseFloat(data.reward_balance || 0) >= 5) {
                            transferBtn.style.display = 'block';
                        } else {
                            transferBtn.style.display = 'none';
                        }
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
        const rewardBalance = parseFloat(document.getElementById('rewardBalanceDisplay')?.textContent.replace(/,/g, '') || 0);
        
        if (modal && rewardBalanceEl) {
            rewardBalanceEl.textContent = rewardBalance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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
        const confirmTransferBtn = document.getElementById('confirmTransferBtn');
        
        if (transferBtn) {
            transferBtn.addEventListener('click', showTransferModal);
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
                
                const rewardBalance = parseFloat(document.getElementById('rewardBalanceDisplay')?.textContent.replace(/,/g, '') || 0);
                if (amount > rewardBalance) {
                    errorEl.textContent = 'Số tiền vượt quá số dư ví thưởng.';
                    errorEl.classList.remove('hidden');
                    return;
                }
                
                // Disable button
                confirmTransferBtn.disabled = true;
                confirmTransferBtn.textContent = 'Đang xử lý...';
                
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const response = await fetch('{{ route('wallet.transfer-reward-to-deposit') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || '',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ amount: amount })
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
            const formattedAmount = parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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

