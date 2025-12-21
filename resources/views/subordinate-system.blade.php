@extends('layouts.mobile')

@section('title', 'Hệ thống - Micex')

@section('header')
<header class="w-full px-4 py-4 flex items-center justify-center bg-gray-900 border-b border-gray-800">
    <h1 class="text-white text-base font-semibold">Hệ thống</h1>
</header>
@endsection

@section('content')
<div class="px-4 py-4 space-y-6">
    <!-- User Profile/Rank Section -->
    <div class="flex flex-col items-center">
        <div class="flex items-start gap-2 mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span class="text-orange-400 font-semibold text-base">{{ $user->display_name ?? $user->referral_code ?? 'C3002023' }}</span>
        </div>
        
        <div class="relative mb-4">
            <img src="{{ asset('images/icons/goal.png') }}" alt="Rank Badge" class="w-20 h-20 object-contain" style="filter: drop-shadow(0 0 15px rgba(255, 215, 0, 0.6)) drop-shadow(0 0 30px rgba(255, 215, 0, 0.3)) drop-shadow(0 0 45px rgba(255, 215, 0, 0.2));">
        </div>
        
        <div class="bg-gray-800 rounded-full px-4 py-2 flex items-center gap-2 mb-2">
            <span class="text-white text-sm font-medium">Cấp 1</span>
            <button class="w-4 h-4 rounded-full bg-yellow-500 flex items-center justify-center cursor-pointer relative group" title="Thông tin về cấp độ">
                <span class="text-white text-xs">?</span>
                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap z-10 border border-gray-700">
                    Thông tin về cấp độ
                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                        <div class="border-4 border-transparent border-t-gray-900"></div>
                    </div>
                </div>
            </button>
        </div>
        
        <p class="text-white text-sm">Hệ thống của bạn</p>
    </div>

    <!-- Referral Links Section -->
    <div class="space-y-4">
        <!-- Registration Link -->
        <div>
            <label class="text-white text-sm mb-2 block">Link đăng ký</label>
            <div class="flex items-center gap-2 overflow-hidden" style="height: 46px; border-radius: 5px; border: 0.5px solid #3958F5; background: #111111; padding: 0 0px;">
                <input type="text" id="referralLinkInput" value="{{ url('/register?r=' . ($user->referral_code ?? '')) }}" readonly class="ml-2 flex-1 min-w-0 bg-transparent text-white text-sm outline-none" style="font-size: 16px;">
                <button id="copyReferralLink" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-xs transition-colors cursor-pointer flex items-center justify-center gap-2 whitespace-nowrap flex-shrink-0">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <span>Sao chép</span>
                </button>
            </div>
        </div>
        
        <!-- Referral Code -->
        <div>
            <label class="text-white text-sm mb-2 block">Mã giới thiệu</label>
            <div class="flex items-center gap-2 overflow-hidden" style="height: 46px; border-radius: 5px; border: 0.5px solid #3958F5; background: #111111; padding: 0 0px;">
                <input type="text" id="referralCodeInput" value="{{ $user->referral_code ?? '' }}" readonly class="ml-2 flex-1 min-w-0 bg-transparent text-white text-sm outline-none" style="font-size: 16px;">
                <button id="copyReferralCode" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-xs transition-colors cursor-pointer flex items-center justify-center gap-2 whitespace-nowrap flex-shrink-0">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <span>Sao chép</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Summary Statistics Section -->
    <div class="grid grid-cols-2 gap-4">
        <!-- Left Card -->
        <div class="bg-[#111111] rounded-lg p-4 space-y-3">
            <div>
                <p class="text-white text-xs mb-1 text-center">Tổng giao dịch hệ thống</p>
                <p class="text-blue-400 font-semibold text-lg text-center">{{ number_format($transactionVolumes['total'] ?? 0, 2, '.', ',') }}$</p>
            </div>

            <hr class="w-full border-gray-700 h-1">
            <div>
                <p class="text-white text-xs mb-1 text-center">Tổng hoa hồng</p>
                <p class="text-orange-400 font-semibold text-lg text-center">{{ number_format($totalCommission ?? 0, 2, '.', ',') }}$</p>
            </div>
        </div>
        
        <!-- Right Card -->
        <div class="bg-[#111111] rounded-lg p-4 space-y-3">
            <div>
                <p class="text-white text-xs mb-1 text-center">Người giới thiệu</p>
                <p class="text-white font-semibold text-lg text-center">{{ $referrer->display_name ?? $referrer->referral_code ?? '-' }}</p>
            </div>
            <hr class="w-full border-gray-700 h-1">
            <div>
                <p class="text-white text-xs mb-1 text-center">Tổng nhà giao dịch</p>
                <p class="text-blue-400 font-semibold text-lg text-center">{{ number_format($totalTraders ?? 0, 0, '.', ',') }}</p>
            </div>
        </div>
    </div>

    <!-- Withdraw Commission Section -->
    <div class="bg-[#111111] rounded-lg p-4 space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-white text-xs mb-1">Hoa hồng có thể rút</p>
                <p class="text-green-400 font-semibold text-lg">{{ number_format($availableCommission ?? 0, 2, '.', ',') }}$</p>
            </div>
            <button id="withdrawCommissionBtn" class="bg-blue-500 hover:bg-blue-600 disabled:bg-gray-600 disabled:cursor-not-allowed text-white px-6 py-2 rounded-lg transition-colors cursor-pointer whitespace-nowrap">
                Rút hoa hồng
            </button>
        </div>
        <p id="withdrawStatus" class="text-gray-400 text-xs"></p>
    </div>

    <!-- Referral List/Table -->
    <div class="bg-[#111111] rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-900 border-b border-gray-700">
                        <th class="text-white text-xs font-medium px-4 py-3 text-left">Cấp độ</th>
                        <th class="text-white text-xs font-medium px-4 py-3 text-right">KLGD</th>
                        <th class="text-white text-xs font-medium px-4 py-3 text-right">HH Nhận</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($referralList ?? [] as $item)
                    <tr class="border-b border-gray-700 last:border-0">
                        <td class="text-white text-xs px-4 py-3">{{ $item['level'] }}</td>
                        <td class="text-white text-xs px-4 py-3 text-right">{{ number_format($item['transaction_volume'] ?? 0, 2, '.', ',') }}$</td>
                        <td class="text-green-400 text-xs px-4 py-3 text-right font-semibold">{{ number_format($item['commission'] ?? 0, 2, '.', ',') }}$</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-gray-400 text-xs px-4 py-8 text-center">Chưa có người giới thiệu</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Copy Referral Link
    const copyReferralLink = document.getElementById('copyReferralLink');
    const referralLinkInput = document.getElementById('referralLinkInput');
    
    if (copyReferralLink && referralLinkInput) {
        copyReferralLink.addEventListener('click', function() {
            navigator.clipboard.writeText(referralLinkInput.value).then(function() {
                copyReferralLink.classList.remove('bg-blue-500', 'hover:bg-blue-600');
                copyReferralLink.classList.add('bg-green-500');
                copyReferralLink.querySelector('span').textContent = 'Đã sao chép!';
                
                setTimeout(function() {
                    copyReferralLink.classList.remove('bg-green-500');
                    copyReferralLink.classList.add('bg-blue-500', 'hover:bg-blue-600');
                    copyReferralLink.querySelector('span').textContent = 'Sao chép';
                }, 2000);
            }).catch(function(err) {
                console.error('Failed to copy:', err);
            });
        });
    }
    
    // Copy Referral Code
    const copyReferralCode = document.getElementById('copyReferralCode');
    const referralCodeInput = document.getElementById('referralCodeInput');
    
    if (copyReferralCode && referralCodeInput) {
        copyReferralCode.addEventListener('click', function() {
            navigator.clipboard.writeText(referralCodeInput.value).then(function() {
                copyReferralCode.classList.remove('bg-blue-500', 'hover:bg-blue-600');
                copyReferralCode.classList.add('bg-green-500');
                copyReferralCode.querySelector('span').textContent = 'Đã sao chép!';
                
                setTimeout(function() {
                    copyReferralCode.classList.remove('bg-green-500');
                    copyReferralCode.classList.add('bg-blue-500', 'hover:bg-blue-600');
                    copyReferralCode.querySelector('span').textContent = 'Sao chép';
                }, 2000);
            }).catch(function(err) {
                console.error('Failed to copy:', err);
            });
        });
    }

    // Withdraw Commission
    const withdrawCommissionBtn = document.getElementById('withdrawCommissionBtn');
    const withdrawStatus = document.getElementById('withdrawStatus');
    let lastWithdrawTime = null;
    let countdownInterval = null;

    // Check withdraw status on page load
    checkWithdrawStatus();

    function checkWithdrawStatus() {
        fetch('{{ route("subordinate-system.check-withdraw-status") }}')
            .then(response => response.json())
            .then(data => {
                if (data.last_withdraw_time) {
                    lastWithdrawTime = new Date(data.last_withdraw_time);
                    updateWithdrawButton();
                } else {
                    // No previous withdrawal, allow withdrawal
                    withdrawCommissionBtn.disabled = false;
                    withdrawStatus.textContent = '';
                }
            })
            .catch(error => {
                console.error('Error checking withdraw status:', error);
            });
    }

    function updateWithdrawButton() {
        if (!lastWithdrawTime) {
            withdrawCommissionBtn.disabled = false;
            withdrawStatus.textContent = '';
            return;
        }

        const now = new Date();
        const timeDiff = now - lastWithdrawTime;
        const oneHour = 60 * 60 * 1000; // 1 hour in milliseconds
        const remainingTime = oneHour - timeDiff;

        if (remainingTime > 0) {
            // Still in cooldown
            withdrawCommissionBtn.disabled = true;
            updateCountdown(remainingTime);
        } else {
            // Can withdraw
            withdrawCommissionBtn.disabled = false;
            withdrawStatus.textContent = '';
            if (countdownInterval) {
                clearInterval(countdownInterval);
                countdownInterval = null;
            }
        }
    }

    function updateCountdown(remainingMs) {
        const minutes = Math.floor(remainingMs / 60000);
        const seconds = Math.floor((remainingMs % 60000) / 1000);
        withdrawStatus.textContent = `Có thể rút lại sau: ${minutes}:${seconds.toString().padStart(2, '0')}`;

        if (countdownInterval) {
            clearInterval(countdownInterval);
        }

        countdownInterval = setInterval(() => {
            const now = new Date();
            const timeDiff = now - lastWithdrawTime;
            const oneHour = 60 * 60 * 1000;
            const remainingTime = oneHour - timeDiff;

            if (remainingTime > 0) {
                updateCountdown(remainingTime);
            } else {
                clearInterval(countdownInterval);
                countdownInterval = null;
                withdrawCommissionBtn.disabled = false;
                withdrawStatus.textContent = '';
            }
        }, 1000);
    }

    if (withdrawCommissionBtn) {
        withdrawCommissionBtn.addEventListener('click', function() {
            if (withdrawCommissionBtn.disabled) {
                return;
            }

            withdrawCommissionBtn.disabled = true;
            withdrawStatus.textContent = 'Đang xử lý...';

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch('{{ route("subordinate-system.withdraw-commission") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    withdrawStatus.textContent = 'Rút hoa hồng thành công!';
                    withdrawStatus.classList.remove('text-gray-400');
                    withdrawStatus.classList.add('text-green-400');
                    
                    // Update last withdraw time
                    lastWithdrawTime = new Date();
                    updateWithdrawButton();
                    
                    // Reload page after 2 seconds to update balance
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    withdrawStatus.textContent = data.error || 'Có lỗi xảy ra';
                    withdrawStatus.classList.remove('text-gray-400', 'text-green-400');
                    withdrawStatus.classList.add('text-red-400');
                    withdrawCommissionBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error withdrawing commission:', error);
                withdrawStatus.textContent = 'Có lỗi xảy ra khi rút hoa hồng';
                withdrawStatus.classList.remove('text-gray-400', 'text-green-400');
                withdrawStatus.classList.add('text-red-400');
                withdrawCommissionBtn.disabled = false;
            });
        });
    }
</script>
@endpush
