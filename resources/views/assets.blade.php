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
                    <p class="text-2xl font-bold flex items-center gap-1">
                        {{ number_format(auth()->user()->balance ?? 0, 2, '.', ',') }}
                        <img src="{{ asset('images/icons/coin_asset.png') }}" alt="Coin asset" class="w-5 h-5 object-contain">
                    </p>
                </div>
                <div class="text-sm text-blue-100 flex items-center gap-1">
                    Vòng cược chưa hoàn thành : <span class="font-semibold text-white">{{ number_format(auth()->user()->getRemainingBettingRequirement() ?? 0, 2, '.', ',') }}</span> <span class="text-yellow-300">
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
@endsection

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
                        
                        if (typeof showToast === 'function') {
                            showToast(data.message, 'success');
                        }
                        
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
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
</script>
@endpush

