@extends('layouts.mobile')

@section('title', 'Nạp tiền - Micex')

@section('header')
<header class="w-full px-4 py-4 flex items-center justify-between bg-gray-900 border-b border-gray-800">
    <button onclick="history.back()" class="text-white">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
    </button>
    <h1 class="text-white text-base font-semibold">Nạp tiền</h1>
    <div class="w-6"></div>
</header>
@endsection

@section('content')
<div class="px-4 py-4 space-y-5">
    <!-- Promotion Badge -->
    @if($activePromotion)
    <div class="rounded-xl border-2 border-yellow-400/60 bg-gradient-to-r from-yellow-500/20 to-orange-500/20 p-4 space-y-2">
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-6 h-6 bg-yellow-400 rounded-full">
                <i class="fas fa-gift text-yellow-900 text-xs"></i>
            </span>
            <p class="text-yellow-300 font-bold text-base">🎉 KHUYẾN MÃI ĐANG DIỄN RA!</p>
        </div>
        <p class="text-yellow-200 text-sm">
            Nhận thêm <span class="font-bold text-yellow-300">{{ number_format($activePromotion->deposit_percentage, 2) }}%</span> khi nạp tiền!
        </p>
        @if($activePromotion->end_date)
        <p class="text-yellow-300/80 text-xs">
            Thời gian: {{ \Carbon\Carbon::parse($activePromotion->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($activePromotion->end_date)->format('d/m/Y') }}
        </p>
        @endif
    </div>
    @endif

    <!-- Intro -->
    <div class="space-y-1">
        <p class="text-sm text-white font-semibold">
            Nhận Crypto on-chain <span class="text-blue-400">Micex</span>
        </p>
        <p class="text-[12px] text-gray-300 leading-snug">
            Mua cược bằng cách chuyển khoản ngân hàng thông qua các đối tác nạp tiền đã được xác thực và bảo lãnh an toàn bởi Micex
        </p>
    </div>

    <!-- Amount input -->
    <div class="space-y-4">
        <div class="space-y-2">
            <p class="text-xs text-gray-300">Số lượng nạp</p>
            <div class="w-full rounded-lg border border-blue-500/60 bg-[#0f1118] flex items-center">
                <input id="depositAmount" type="text" inputmode="decimal" class="flex-1 bg-transparent text-white px-3 py-3 outline-none placeholder-gray-500" style="font-size: 16px;" placeholder="100,000 đến 500,000,000">
                <span class="px-3 text-blue-400 text-sm font-semibold">VND</span>
            </div>
        </div>

        <div class="space-y-2">
            <p class="text-xs text-gray-300">Số lượng đá quý</p>
            <div class="w-full rounded-lg border border-blue-500/60 bg-[#0f1118] flex items-center justify-between px-3 py-3">
                <span id="gemAmount" class="text-white text-sm">0</span>
                <img src="{{ asset('images/icons/coin_asset.png') }}" alt="Gem" class="w-4 object-contain ">
            </div>
        </div>
    </div>

    <!-- QR + bank info -->
    <div class="rounded-xl border border-blue-500/60 bg-[#0f1118] p-4 space-y-3">
        <p class="text-xs text-white font-semibold">Mã QR</p>
        <div class="flex flex-col sm:flex-row sm:items-start gap-4">
            <div class="w-28 h-28 bg-white flex items-center justify-center rounded-lg overflow-hidden border border-gray-700 flex-shrink-0">
                <img src="{{ asset('images/image_2025-12-25_15-03-09.png') }}" alt="QR code" class="w-full h-full object-contain">
            </div>
            <div class="flex flex-col gap-1 items-start justify-start">
                <div class="flex items-center gap-2">
                    <span class="text-gray-600 text-xs" style="font-weight: 500;">Ngân hàng :</span>
                    <p class="text-white text-sm" style="font-weight: 500;"> Techcombank</p>    
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-gray-600 text-xs" style="font-weight: 500;">Số tài khoản :</span>
                    <p class="text-white text-sm" style="font-weight: 500;"> 19072055160017</p>    
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-gray-600 text-xs" style="font-weight: 500;">Họ tên :</span>
                    <p class="text-white text-sm" style="font-weight: 500;"> TRAN VAN UY</p>    
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-gray-600 text-xs" style="font-weight: 500;">Nội dung :</span> 
                    <span id="transferCode" class="text-white text-sm" style="font-weight: 500;">{{ $user->transfer_code ?? '0x7283190aaaa2' }}</span>
                    <button id="copyTransferCode" class="text-blue-400 hover:text-blue-300 transition-colors" title="Copy">
                        <i class="fas fa-copy text-sm"></i>
                    </button>
                </div>
            </div>
        </div>
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

    <!-- Deposit Form -->
    <form id="depositForm" action="{{ route('deposit.submit') }}" method="POST" class="w-full">
        @csrf
        <input type="hidden" id="depositAmountInput" name="amount" value="">
        <input type="hidden" id="transferCodeInput" name="transfer_code" value="{{ $user->transfer_code ?? '' }}">
        <button type="submit" id="depositCountdown" class="w-full bg-[#2d59ff] hover:bg-[#2448d1] text-white font-semibold py-3 rounded-full text-base shadow">
            Nạp
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const amountInput = document.getElementById('depositAmount');
    const gemDisplay = document.getElementById('gemAmount');

    // Get VND to Gem rate from server
    const vndToGemRate = {{ $vndToGemRate ?? 1000 }};

    function parseAmount(value) {
        if (!value) return 0;
        const normalized = value.replace(/[^0-9]/g, ''); // Remove all non-numeric characters
        const num = parseFloat(normalized);
        return Number.isFinite(num) ? num : 0;
    }

    function formatVnd(value) {
        if (!value) return '';
        const num = parseAmount(value);
        return num.toLocaleString('en-US');
    }

    function formatNumber(num) {
        return num.toLocaleString('en-US', { maximumFractionDigits: 2 });
    }

    function updateGem() {
        const amount = parseAmount(amountInput?.value || '');
        const gems = amount / vndToGemRate;
        gemDisplay.textContent = formatNumber(gems);
    }

    // Auto format VND when typing
    if (amountInput) {
        amountInput.addEventListener('input', function(e) {
            const cursorPosition = e.target.selectionStart;
            const originalValue = e.target.value;
            const numericValue = parseAmount(originalValue);
            
            // Format with commas
            const formatted = formatVnd(numericValue.toString());
            
            // Update value
            e.target.value = formatted;
            
            // Restore cursor position (adjust for added commas)
            const diff = formatted.length - originalValue.length;
            const newPosition = cursorPosition + diff;
            e.target.setSelectionRange(newPosition, newPosition);
            
            // Update gem amount
            updateGem();
        });
    }

    if (amountInput && gemDisplay) {
        amountInput.addEventListener('input', updateGem);
        updateGem();
    }

    // Countdown 30 minutes - chỉ bắt đầu khi user click button
    const countdownBtn = document.getElementById('depositCountdown');
    let remainingSeconds = 30 * 60;
    let countdownInterval = null;
    let isCountdownActive = false;

    function renderCountdown() {
        const m = Math.floor(remainingSeconds / 60).toString().padStart(2, '0');
        const s = (remainingSeconds % 60).toString().padStart(2, '0');
        if (countdownBtn) countdownBtn.textContent = `${m}:${s}`;
    }

    function tickCountdown() {
        if (remainingSeconds > 0) {
            remainingSeconds -= 1;
            renderCountdown();
        } else if (countdownBtn) {
            countdownBtn.textContent = '00:00';
            countdownBtn.classList.add('opacity-70', 'cursor-not-allowed');
            if (countdownInterval) {
                clearInterval(countdownInterval);
                countdownInterval = null;
            }
        }
    }

    function startCountdown(e) {
        if (isCountdownActive) {
            if (e) e.preventDefault();
            return false;
        }
        
        const amount = parseAmount(amountInput?.value || '');
        if (!amount || amount < 100000 || amount > 500000000) {
            if (e) e.preventDefault();
            if (typeof showToast === 'function') {
                showToast('Vui lòng nhập số lượng nạp từ 100,000 đến 500,000,000 VND', 'error');
            } else {
                alert('Vui lòng nhập số lượng nạp từ 100,000 đến 500,000,000 VND');
            }
            return false;
        }

        // Prevent default form submission
        if (e) e.preventDefault();

        const transferCode = document.getElementById('transferCode')?.textContent?.trim() || '';

        // Set form values BEFORE creating FormData (gem_amount will be calculated on server)
        const depositAmountInput = document.getElementById('depositAmountInput');
        const transferCodeInput = document.getElementById('transferCodeInput');
        
        // Ensure values are strings for form inputs
        if (depositAmountInput) depositAmountInput.value = String(amount || '');
        if (transferCodeInput) transferCodeInput.value = String(transferCode || '');

        // Mark as active to prevent double submission
        isCountdownActive = true;

        // Hiển thị phần "Đang được xử lý"
        const processingStatus = document.getElementById('processingStatus');
        if (processingStatus) {
            processingStatus.classList.remove('hidden');
        }

        // Start countdown immediately
        remainingSeconds = 30 * 60;
        renderCountdown();
        if (countdownBtn) {
            countdownBtn.disabled = true;
            countdownBtn.classList.add('opacity-70', 'cursor-not-allowed');
        }
        
        if (countdownInterval) {
            clearInterval(countdownInterval);
        }
        countdownInterval = setInterval(tickCountdown, 1000);

        // Submit form via AJAX to avoid page reload
        const form = document.getElementById('depositForm');
        const formData = new FormData(form);
        
        // Debug: Log FormData
        
        // Get CSRF token from meta tag or form
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
            || form.querySelector('input[name="_token"]')?.value;
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
            },
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) {
                // Handle validation errors (422)
                if (response.status === 422 && data.errors) {
                    const firstError = Object.values(data.errors)[0];
                    const errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                    throw { message: errorMessage, errors: data.errors };
                }
                // Handle other errors
                throw { message: data.message || data.error || 'Có lỗi xảy ra khi gửi yêu cầu.' };
            }
            return data;
        })
        .then(data => {
            // Show success toast
            if (typeof showToast === 'function') {
                showToast(data.message || 'Yêu cầu nạp tiền đã được gửi thành công. .', 'success');
            }
            
            // Start long polling if deposit_request_id is returned
            if (data.deposit_request_id) {
                startLongPolling(data.deposit_request_id);
            }
        })
        .catch(error => {
            let errorMessage = 'Có lỗi xảy ra khi gửi yêu cầu. Vui lòng thử lại.';
            
            if (error.message) {
                errorMessage = error.message;
            } else if (error.errors) {
                // Handle validation errors
                const firstError = Object.values(error.errors)[0];
                errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
            }
            
            if (typeof showToast === 'function') {
                showToast(errorMessage, 'error');
            }
            // Reset form state on error
            isCountdownActive = false;
            if (countdownInterval) {
                clearInterval(countdownInterval);
                countdownInterval = null;
            }
            if (countdownBtn) {
                countdownBtn.disabled = false;
                countdownBtn.classList.remove('opacity-70', 'cursor-not-allowed');
                countdownBtn.textContent = 'Nạp';
            }
            const processingStatus = document.getElementById('processingStatus');
            if (processingStatus) {
                processingStatus.classList.add('hidden');
            }
        });

        return false;
    }

    // Simple polling function to check deposit status (changed from long polling)
    let pollingTimeoutId = null;
    let isPolling = false;
    const POLLING_INTERVAL = 3000; // Poll every 3 seconds
    const MAX_POLLING_DURATION = 30 * 60 * 1000; // Stop after 30 minutes
    let pollingStartTime = null;
    
    function startLongPolling(depositRequestId) {
        // Stop any existing polling
        stopPolling();
        
        isPolling = true;
        pollingStartTime = Date.now();
        
        function poll() {
            // Check if we should stop polling
            if (!isPolling) return;
            
            // Check if countdown has expired
            if (remainingSeconds <= 0) {
                stopPolling();
                return;
            }
            
            // Check if max duration reached
            if (Date.now() - pollingStartTime > MAX_POLLING_DURATION) {
                stopPolling();
                if (typeof showToast === 'function') {
                    showToast('Yêu cầu nạp tiền đã hết thời gian chờ. Vui lòng kiểm tra lại sau.', 'warning');
                }
                return;
            }
            
            fetch(`/deposit/check-status/${depositRequestId}`, {
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
                // If status changed from pending, handle it
                if (data.status === 'approved' || data.status === 'rejected') {
                    stopPolling();
                    
                    // Stop countdown
                    if (countdownInterval) {
                        clearInterval(countdownInterval);
                        countdownInterval = null;
                    }
                    
                    // Update UI
                    if (countdownBtn) {
                        countdownBtn.disabled = false;
                        countdownBtn.classList.remove('opacity-70', 'cursor-not-allowed');
                        countdownBtn.textContent = 'Nạp';
                    }
                    
                    // Hide processing status
                    const processingStatus = document.getElementById('processingStatus');
                    if (processingStatus) {
                        processingStatus.classList.add('hidden');
                    }
                    
                    // Show toast
                    if (typeof showToast === 'function') {
                        showToast(data.message || (data.status === 'approved' ? 'Yêu cầu nạp tiền đã được duyệt thành công!' : 'Yêu cầu nạp tiền đã bị từ chối.'), data.status === 'approved' ? 'success' : 'error');
                    }
                    
                    // Reset countdown active flag
                    isCountdownActive = false;
                    
                    // Optionally reload page to show updated balance after 2 seconds
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else if (data.status === 'pending') {
                    // Still pending, continue polling after interval
                    if (isPolling) {
                        pollingTimeoutId = setTimeout(poll, POLLING_INTERVAL);
                    }
                }
            })
            .catch(error => {
                // Continue polling on error, but with longer delay
                if (isPolling) {
                    pollingTimeoutId = setTimeout(poll, POLLING_INTERVAL * 2);
                }
            });
        }
        
        // Start first poll immediately
        poll();
    }
    
    function stopPolling() {
        isPolling = false;
        if (pollingTimeoutId) {
            clearTimeout(pollingTimeoutId);
            pollingTimeoutId = null;
        }
    }
    
    // Stop polling when page is unloaded
    window.addEventListener('beforeunload', function() {
        stopPolling();
    });

    // Check for pending deposit on page load and resume countdown
    @if(isset($pendingDeposit) && $pendingDeposit)
        (function() {
            const pendingDepositCreatedAt = {{ $pendingDeposit->created_at->timestamp }} * 1000; // Convert to milliseconds
            const now = Date.now();
            const elapsedSeconds = Math.floor((now - pendingDepositCreatedAt) / 1000);
            const totalSeconds = 30 * 60; // 30 minutes
            remainingSeconds = Math.max(0, totalSeconds - elapsedSeconds);
            
            if (remainingSeconds > 0) {
                // Continue countdown if still within 30 minutes
                isCountdownActive = true;
                if (countdownBtn) {
                    countdownBtn.disabled = true;
                    countdownBtn.classList.add('opacity-70', 'cursor-not-allowed');
                }
                
                // Show processing status
                const processingStatus = document.getElementById('processingStatus');
                if (processingStatus) {
                    processingStatus.classList.remove('hidden');
                }
                
                // Start countdown immediately
                renderCountdown();
                if (countdownInterval) {
                    clearInterval(countdownInterval);
                }
                countdownInterval = setInterval(tickCountdown, 1000);
                
                // Start polling for status
                startLongPolling({{ $pendingDeposit->id }});
            } else {
                // Countdown expired, reset button
                if (countdownBtn) {
                    countdownBtn.textContent = 'Nạp';
                    countdownBtn.disabled = false;
                    countdownBtn.classList.remove('opacity-70', 'cursor-not-allowed');
                }
            }
        })();
    @endif

    // Handle form submit
    const depositForm = document.getElementById('depositForm');
    if (depositForm) {
        depositForm.addEventListener('submit', startCountdown);
    }

    // Copy transfer code to clipboard
    const copyTransferCodeBtn = document.getElementById('copyTransferCode');
    const transferCodeElement = document.getElementById('transferCode');
    
    if (copyTransferCodeBtn && transferCodeElement) {
        copyTransferCodeBtn.addEventListener('click', async function() {
            const transferCode = transferCodeElement.textContent.trim();
            
            try {
                await navigator.clipboard.writeText(transferCode);
                
                // Show success toast
                if (typeof showToast === 'function') {
                    showToast('Đã sao chép nội dung chuyển tiền!', 'success');
                }
                
                // Visual feedback
                const icon = copyTransferCodeBtn.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-copy');
                    icon.classList.add('fa-check');
                    copyTransferCodeBtn.classList.remove('text-blue-400', 'hover:text-blue-300');
                    copyTransferCodeBtn.classList.add('text-green-400');
                    
                    setTimeout(() => {
                        icon.classList.remove('fa-check');
                        icon.classList.add('fa-copy');
                        copyTransferCodeBtn.classList.remove('text-green-400');
                        copyTransferCodeBtn.classList.add('text-blue-400', 'hover:text-blue-300');
                    }, 2000);
                }
            } catch (err) {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = transferCode;
                textArea.style.position = 'fixed';
                textArea.style.opacity = '0';
                document.body.appendChild(textArea);
                textArea.select();
                
                try {
                    document.execCommand('copy');
                    if (typeof showToast === 'function') {
                        showToast('Đã sao chép nội dung chuyển tiền!', 'success');
                    }
                } catch (fallbackErr) {
                    if (typeof showToast === 'function') {
                        showToast('Không thể sao chép. Vui lòng thử lại.', 'error');
                    } else {
                        alert('Không thể sao chép. Vui lòng thử lại.');
                    }
                }
                
                document.body.removeChild(textArea);
            }
        });
    }
</script>
@endpush

