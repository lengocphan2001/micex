@extends('layouts.mobile')

@section('title', 'Hệ thống - Micex')

@section('header')
<header class="w-full px-4 py-4 flex items-center justify-center bg-gray-900 border-b border-gray-800">
    <h1 class="text-white text-base font-semibold">Hệ thống</h1>
</header>
@endsection

@section('content')
<div class="px-4 space-y-2">
    <!-- User Profile Section with Display Name -->
    <div class="flex items-center gap-2">
        <div class="w-10 h-10 rounded flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <span class="text-orange-400 font-semibold text-base">{{ $user->display_name ?? $user->referral_code ?? 'C3002023' }}</span>
    </div>

    <!-- Promotion Banner with Level -->
    <div class="relative rounded-lg px-4 overflow-hidden" style="background: linear-gradient(245.48deg, #F6C938 -22.29%, #2A2C30 50.86%);">
        <div class="relative z-10 flex items-start">
            <div class="flex flex-col gap-2" style="padding-top: 10px; padding-bottom: 10px;">
                <div class="relative mb-2" style="height: 48px;">
                    <!-- Circular Badge -->
                    <div class="w-12 h-12 rounded-full flex items-center justify-center relative overflow-hidden">
                        <img src="{{ asset('images/icons/componentnetwork.png') }}" alt="" class="absolute inset-0 w-full h-full object-cover">
                        <span class="text-orange-900 font-bold text-3xl relative z-10 flex items-center justify-center mt-1 mr-0.5" style="color: #7c2d12; width: 100%; height: 100%; line-height: 1; display: flex; align-items: center; justify-content: center;">{{ $networkLevel }}</span>
                    </div>
                    <!-- Rectangular Section -->
                    <div class="absolute left-8.5 top-2.5 py-1 z-1 px-8 rounded-r-full border-2 border-yellow-400 bg-transparent flex items-center" style="border-left: none;">
                        <span class="text-yellow-400 font-semibold text-sm whitespace-nowrap">Cấp {{ $networkLevel }}</span>
                    </div>
                </div>
                <p class="text-[#FFFFFF80] text-[10px] font-medium">Micex invite member</p>
                <div class="flex flex-col">
                    <p class="text-white text-sm font-medium">Mời bạn bè nhận quà tới</p>
                    <p class="text-[#FF9D00] font-semibold text-lg" style="line-height: normal;">2,000 USDT</p>
                </div>
                
                <p class="text-white text-[10px] opacity-90 text-nowrap">Thưởng tiền mặt cho mỗi lượt giới thiệu bạn bè mới !</p>
            </div>
            <div class="flex-shrink-0 flex items-center justify-center self-center" style="">
                <img src="{{ asset('images/networklevel.png') }}" alt="Promotion" class="object-contain" style="width: 140px; height: 140px;">
            </div>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="mt-6 rounded-lg p-4" style="border: 0.3px solid #46484D">
        <div class="grid grid-cols-2 gap-6">
            <div class="text-center">
                <p class="text-[#D9D9D980] text-xs mb-1">Thành viên mới trong ngày</p>
                <p class="text-green-400 font-semibold text-lg">{{ number_format($newMembersToday ?? 0, 0, '.', ',') }}</p>
            </div>
            <div class="text-center">
                <p class="text-[#D9D9D980] text-xs mb-1">Tổng giao dịch hệ thống</p>
                <p class="text-blue-400 font-semibold text-lg">{{ number_format($totalSystemTransactions ?? 0, 2, '.', ',') }}$</p>
            </div>
            <div class="text-center">
                <p class="text-[#D9D9D980] text-xs mb-1">Tổng hoa hồng</p>
                <p class="text-red-400 font-semibold text-lg">{{ number_format($totalCommission ?? 0, 2, '.', ',') }}$</p>
            </div>
            <div class="text-center">
                <p class="text-[#D9D9D980] text-xs mb-1">Tổng số thành viên</p>
                <p class="text-purple-400 font-semibold text-lg">{{ number_format($totalMembers ?? 0, 0, '.', ',') }}</p>
            </div>
        </div>
    </div>

    <!-- Monthly Transaction Volume Table -->
    <div class="mb-4">
        <div class=" py-3 flex items-center gap-2">
            <h3 class="text-white text-sm font-medium">KLGD Cấp Dưới</h3>
            <button class="rounded-full flex items-center justify-center cursor-pointer relative group" style="width: 20px; height: 20px; border: 1px solid #3958F5; background: transparent;" title="Khối lượng giao dịch cấp dưới">
                <span class="text-xs font-semibold" style="color: #3958F5;">?</span>
            </button>
        </div>
        <div class="overflow-hidden" style="border-radius: 10px; border: 0.3px solid #46484D;">
            <div class="overflow-x-auto h-full">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="text-white text-xs font-medium px-4 py-3 text-center">Tháng</th>
                            <th class="text-white text-xs font-medium px-4 py-3 text-center">Cấp dưới</th>
                            <th class="text-white text-xs font-medium px-4 py-3 text-center">Tổng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monthlyTransactionVolumes ?? [] as $item)
                            <tr class="border-b border-gray-700 last:border-0">
                                <td class="text-white text-xs px-4 py-3 text-center">{{ $item['month'] }}</td>
                                <td class="text-white text-xs px-4 py-3 text-center">{{ $item['level'] }}</td>
                                <td class="text-white text-xs px-4 py-3 text-center font-semibold">{{ number_format($item['volume'], 2, '.', ',') }}$</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-gray-400 text-xs px-4 py-8 text-center">Chưa có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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


    <h3 class="mt-4 text-white text-sm font-medium">Liên kết mời bạn bè !</h3>
    <!-- Referral Links Section -->
    <div class="space-y-4 rounded-lg p-4" style="border: 0.3px solid rgba(255, 255, 255, 0.5);">
        
        
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
                withdrawStatus.textContent = 'Lỗi: Không tìm thấy CSRF token';
                withdrawStatus.classList.remove('text-gray-400', 'text-green-400');
                withdrawStatus.classList.add('text-red-400');
                withdrawCommissionBtn.disabled = false;
                return;
            }
            
            fetch('{{ route("subordinate-system.withdraw-commission") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            })
            .then(async response => {
                // Kiểm tra nếu response không phải JSON (có thể là 419 error page)
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    if (response.status === 419) {
                        throw new Error('CSRF token mismatch. Vui lòng refresh trang và thử lại.');
                    }
                    throw new Error('Server trả về response không hợp lệ');
                }
                return response.json();
            })
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
                withdrawStatus.textContent = 'Có lỗi xảy ra khi rút hoa hồng';
                withdrawStatus.classList.remove('text-gray-400', 'text-green-400');
                withdrawStatus.classList.add('text-red-400');
                withdrawCommissionBtn.disabled = false;
            });
        });
    }
</script>
@endpush
