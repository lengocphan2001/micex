@extends('layouts.mobile')

@section('title', 'Rút tiền - Micex')

@section('header')
<header class="w-full px-4 py-4 flex items-center justify-between bg-gray-900 border-b border-gray-800">
    <button onclick="history.back()" class="text-white">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
    </button>
    <h1 class="text-white text-base font-semibold">Rút tiền</h1>
    <div class="w-6"></div>
</header>
@endsection

@section('content')
<div class="px-4 py-4 space-y-5">
    <div class="space-y-1">
        <p class="text-sm text-white font-semibold">
            Rút tiền Crypto on-chain <span class="text-blue-400">Micex</span>
        </p>
        <p class="text-[12px] text-gray-300 leading-snug">
            Rút tiền về tài khoản ngân hàng của bạn
        </p>
    </div>

    @php
        $user = auth()->user();
        $vndToGemRate = \App\Models\SystemSetting::getVndToGemRate();
    @endphp

    <!-- Balance Display -->
    <div class="space-y-2">
        <p class="text-xs text-gray-300">Số dư hiện tại</p>
        <div class="w-full rounded-lg border border-blue-500/60 bg-[#0f1118] flex items-center justify-between px-3 py-3">
            <span class="text-white text-base font-bold">{{ number_format($user->balance ?? 0, 2, '.', ',') }}</span>
            <img src="{{ asset('images/icons/coin_asset.png') }}" alt="Gem" class="w-4 object-contain">
        </div>
    </div>

    @if(!empty($user?->bank_name) && !empty($user?->bank_account))
        <!-- Bank Info Display -->
        <div class="space-y-3">
            <div class="space-y-2">
                <p class="text-xs text-gray-300">Thông tin ngân hàng</p>
                <div class="w-full rounded-lg border border-blue-500/60 bg-[#0f1118] text-white text-sm px-3 py-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white font-semibold">{{ $user->bank_name }}</p>
                            <p class="text-gray-300 text-xs">{{ $user->bank_full_name }}</p>
                        </div>
                        <p class="text-blue-300 text-sm font-mono">{{ $user->bank_account }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Amount input -->
        <div class="space-y-4">
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <p class="text-xs text-gray-300">Số lượng đá quý</p>
                    <button type="button" id="maxAmountBtn" class="text-xs text-blue-400 font-semibold hover:text-blue-300">Tối đa</button>
                </div>
                <input type="text" id="gemAmountInput" inputmode="decimal" class="w-full rounded-lg border border-blue-500/60 bg-[#0f1118] text-white px-3 py-3 outline-none placeholder-gray-500" style="font-size: 16px;" placeholder="Nhập số lượng đá quý">
            </div>

            <div class="space-y-2">
                <p class="text-xs text-gray-300">Số tiền VND tương ứng</p>
                <div class="w-full rounded-lg border border-blue-500/60 bg-[#0f1118] flex items-center justify-between px-3 py-3">
                    <span id="vndAmount" class="text-white text-sm">0</span>
                    <span class="text-blue-400 text-sm font-semibold">VND</span>
                </div>
            </div>
        </div>

        <!-- Fund Password -->
        <div class="space-y-2">
            <p class="text-xs text-gray-300">Mật khẩu quỹ</p>
            <input type="password" id="fundPasswordInput" class="w-full rounded-lg border border-blue-500/60 bg-[#0f1118] text-white px-3 py-3 outline-none placeholder-gray-500" style="font-size: 16px;" placeholder="Nhập mật khẩu quỹ">
        </div>

        <!-- Success/Error Messages -->
        @if (session('success'))
            <div class="mb-4 bg-green-500/15 border border-green-500 text-green-200 text-sm rounded-lg px-3 py-2">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 bg-red-500/15 border border-red-500 text-red-200 text-sm rounded-lg px-3 py-2">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 bg-red-500/15 border border-red-500 text-red-200 text-sm rounded-lg px-3 py-2">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Status -->
        <div id="processingStatus" class="text-xs text-gray-300 flex items-center gap-2 hidden">
            Đang được xử lý
            <span class="inline-flex w-4 h-4 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></span>
        </div>

        <!-- Withdraw Form -->
        <form id="withdrawForm" action="{{ route('withdraw.submit') }}" method="POST" class="w-full">
            @csrf
            <input type="hidden" id="gemAmountInputHidden" name="gem_amount" value="">
            <button type="submit" id="withdrawBtn" class="w-full bg-[#2d59ff] hover:bg-[#2448d1] text-white font-semibold py-3 rounded-full text-base shadow">
                Xác nhận rút tiền
            </button>
        </form>
    @else
        <div class="space-y-2">
            <div class="bg-yellow-500/15 border border-yellow-500 text-yellow-200 text-sm rounded-lg px-3 py-2">
                <p>Bạn chưa liên kết ngân hàng. Vui lòng liên kết ngân hàng trước khi rút tiền.</p>
            </div>
            <a href="{{ route('me.bank') }}" class="w-full inline-flex items-center justify-center bg-[#2d59ff] hover:bg-[#2448d1] text-white font-semibold py-3 rounded-full text-base shadow">
                Liên kết ngân hàng ngay
            </a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    const gemAmountInput = document.getElementById('gemAmountInput');
    const vndAmountDisplay = document.getElementById('vndAmount');
    const maxAmountBtn = document.getElementById('maxAmountBtn');
    const gemAmountInputHidden = document.getElementById('gemAmountInputHidden');
    const withdrawForm = document.getElementById('withdrawForm');
    const withdrawBtn = document.getElementById('withdrawBtn');
    const fundPasswordInput = document.getElementById('fundPasswordInput');
    const processingStatus = document.getElementById('processingStatus');

    // Get VND to Gem rate from server
    const vndToGemRate = {{ $vndToGemRate ?? 1000 }};
    const userBalance = {{ $user->balance ?? 0 }};

    function parseAmount(value) {
        if (!value) return 0;
        const normalized = value.replace(/[^0-9.]/g, '');
        const num = parseFloat(normalized);
        return Number.isFinite(num) ? num : 0;
    }

    function formatNumber(num) {
        return num.toLocaleString('vi-VN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function updateVnd() {
        const gemAmount = parseAmount(gemAmountInput?.value || '');
        const vndAmount = gemAmount * vndToGemRate;
        vndAmountDisplay.textContent = formatNumber(vndAmount);
        
        // Update hidden input
        if (gemAmountInputHidden) {
            gemAmountInputHidden.value = gemAmount;
        }
    }

    // Max amount button
    if (maxAmountBtn && gemAmountInput) {
        maxAmountBtn.addEventListener('click', function() {
            gemAmountInput.value = userBalance.toFixed(2);
            updateVnd();
        });
    }

    // Update VND when gem amount changes
    if (gemAmountInput) {
        gemAmountInput.addEventListener('input', function() {
            const value = parseAmount(this.value);
            if (value > userBalance) {
                this.value = userBalance.toFixed(2);
            }
            updateVnd();
        });
    }

    // Form submission
    if (withdrawForm) {
        withdrawForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const gemAmount = parseAmount(gemAmountInput?.value || '');
            const fundPassword = fundPasswordInput?.value || '';

            // Validation
            if (!gemAmount || gemAmount <= 0) {
                if (typeof showToast === 'function') {
                    showToast('Vui lòng nhập số lượng đá quý muốn rút.', 'error');
                } else {
                    alert('Vui lòng nhập số lượng đá quý muốn rút.');
                }
                return;
            }

            if (gemAmount > userBalance) {
                if (typeof showToast === 'function') {
                    showToast('Số dư không đủ để rút tiền.', 'error');
                } else {
                    alert('Số dư không đủ để rút tiền.');
                }
                return;
            }

            if (!fundPassword) {
                if (typeof showToast === 'function') {
                    showToast('Vui lòng nhập mật khẩu quỹ.', 'error');
                } else {
                    alert('Vui lòng nhập mật khẩu quỹ.');
                }
                return;
            }

            // Disable button and show processing
            if (withdrawBtn) {
                withdrawBtn.disabled = true;
                withdrawBtn.textContent = 'Đang xử lý...';
            }
            if (processingStatus) {
                processingStatus.classList.remove('hidden');
            }

            // Create FormData
            const formData = new FormData(this);
            formData.set('gem_amount', gemAmount);
            formData.set('fund_password', fundPassword);

            // Get CSRF token
            const csrfToken = this.querySelector('input[name="_token"]')?.value 
                || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

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

                if (response.ok && data.success) {
                    if (typeof showToast === 'function') {
                        showToast(data.message || 'Yêu cầu rút tiền đã được gửi thành công. .', 'success');
                    }

                    // Start polling for status updates
                    if (data.withdraw_request_id) {
                        startPolling(data.withdraw_request_id);
                    } else {
                        // Reload page after 2 seconds
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    }
                } else {
                    if (typeof showToast === 'function') {
                        showToast(data.message || data.error || 'Có lỗi xảy ra khi gửi yêu cầu rút tiền.', 'error');
                    } else {
                        alert(data.message || data.error || 'Có lỗi xảy ra khi gửi yêu cầu rút tiền.');
                    }

                    // Re-enable button
                    if (withdrawBtn) {
                        withdrawBtn.disabled = false;
                        withdrawBtn.textContent = 'Xác nhận rút tiền';
                    }
                    if (processingStatus) {
                        processingStatus.classList.add('hidden');
                    }
                }
            } catch (error) {
                console.error('Error submitting withdraw request:', error);
                if (typeof showToast === 'function') {
                    showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
                } else {
                    alert('Có lỗi xảy ra. Vui lòng thử lại.');
                }

                // Re-enable button
                if (withdrawBtn) {
                    withdrawBtn.disabled = false;
                    withdrawBtn.textContent = 'Xác nhận rút tiền';
                }
                if (processingStatus) {
                    processingStatus.classList.add('hidden');
                }
            }
        });
    }

    // Simple polling function to check withdraw status
    let pollingTimeoutId = null;
    let isPolling = false;
    const POLLING_INTERVAL = 3000; // Poll every 3 seconds
    const MAX_POLLING_DURATION = 30 * 60 * 1000; // Stop after 30 minutes
    let pollingStartTime = null;

    function startPolling(withdrawRequestId) {
        stopPolling();
        
        isPolling = true;
        pollingStartTime = Date.now();
        
        function poll() {
            if (!isPolling) return;
            
            if (Date.now() - pollingStartTime > MAX_POLLING_DURATION) {
                stopPolling();
                if (typeof showToast === 'function') {
                    showToast('Yêu cầu rút tiền đã hết thời gian chờ. Vui lòng kiểm tra lại sau.', 'warning');
                }
                return;
            }
            
            fetch(`/withdraw/check-status/${withdrawRequestId}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            })
            .then(async response => {
                if (!response.ok) {
                    throw new Error('Failed to check status');
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'approved' || data.status === 'rejected') {
                    stopPolling();
                    
                    if (withdrawBtn) {
                        withdrawBtn.disabled = false;
                        withdrawBtn.textContent = 'Xác nhận rút tiền';
                    }
                    
                    if (processingStatus) {
                        processingStatus.classList.add('hidden');
                    }
                    
                    if (typeof showToast === 'function') {
                        showToast(data.message || (data.status === 'approved' ? 'Yêu cầu rút tiền đã được duyệt thành công!' : 'Yêu cầu rút tiền đã bị từ chối.'), data.status === 'approved' ? 'success' : 'error');
                    }
                    
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    pollingTimeoutId = setTimeout(poll, POLLING_INTERVAL);
                }
            })
            .catch(error => {
                console.error('Polling error:', error);
                pollingTimeoutId = setTimeout(poll, POLLING_INTERVAL);
            });
        }
        
        pollingTimeoutId = setTimeout(poll, POLLING_INTERVAL);
    }

    function stopPolling() {
        if (pollingTimeoutId) {
            clearTimeout(pollingTimeoutId);
            pollingTimeoutId = null;
        }
        isPolling = false;
    }
</script>
@endpush
