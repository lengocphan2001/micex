@extends('layouts.mobile')

@section('title', 'Liên kết ngân hàng - Micex')

@section('header')
<header class="w-full px-4 py-4 flex items-center justify-between bg-gray-900 border-b border-gray-800">
    <button onclick="history.back()" class="text-white">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
    </button>
    <h1 class="text-white text-base font-semibold">Liên kết ngân hàng</h1>
    <div class="w-6"></div>
</header>
@endsection

@section('content')
@php
    $user = auth()->user();
    $hasBank = !empty($user?->bank_name) && !empty($user?->bank_account);
    $hasFundPassword = !empty($user?->fund_password);
@endphp

<div class="px-4 py-4 space-y-4">
    @if ($errors->any())
        <div class="bg-red-500/20 border border-red-500 text-red-200 text-sm rounded-lg px-3 py-2">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('status'))
        <div class="bg-green-500/20 border border-green-500 text-green-200 text-sm rounded-lg px-3 py-2">
            {{ session('status') }}
        </div>
    @endif

    @if($hasBank)
    <div class="rounded-xl border border-blue-500/60 bg-[#0f1118] p-3 text-sm text-white space-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="font-semibold">{{ $user->bank_name }}</p>
                <p class="text-gray-300 text-xs">{{ $user->bank_full_name }}</p>
            </div>
            <p class="text-blue-300 font-mono">{{ $user->bank_account }}</p>
        </div>
        <p class="text-gray-400 text-xs">Bạn có thể thêm ngân hàng khác bên dưới.</p>
    </div>
    @endif

    <form action="{{ route('me.bank.submit') }}" method="POST" class="space-y-3">
        @csrf
        <input type="text" name="bank_name" value="{{ old('bank_name', $user->bank_name) }}" class="w-full rounded-lg border border-blue-500/60 bg-[#0f1118] text-white px-3 py-3 outline-none placeholder-gray-500" style="font-size: 16px;" placeholder="Ngân hàng" required>
        <input type="text" name="bank_account" value="{{ old('bank_account', $user->bank_account) }}" class="w-full rounded-lg border border-blue-500/60 bg-[#0f1118] text-white px-3 py-3 outline-none placeholder-gray-500" style="font-size: 16px;" placeholder="Số tài khoản" required>
        <input type="text" name="bank_full_name" value="{{ old('bank_full_name', $user->bank_full_name) }}" class="w-full rounded-lg border border-blue-500/60 bg-[#0f1118] text-white px-3 py-3 outline-none placeholder-gray-500" style="font-size: 16px;" placeholder="Họ và Tên" required>
        <input type="password" name="fund_password" id="fundPasswordInput" class="w-full rounded-lg border border-blue-500/60 bg-[#0f1118] text-white px-3 py-3 outline-none placeholder-gray-500" style="font-size: 16px;" placeholder="Mật khẩu quỹ (để xác nhận)" required>

        <button type="submit" id="submitBankLink" class="w-full bg-[#2d59ff] hover:bg-[#2448d1] text-white font-semibold py-3 rounded-full text-base shadow">Lưu ngân hàng</button>
    </form>
</div>

<!-- Popup yêu cầu tạo mật khẩu quỹ -->
@if(!$hasFundPassword)
<div id="fundPasswordModal" class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center px-4" style="display: flex;">
    <div class="bg-[#0f1118] rounded-xl border border-blue-500/60 p-6 w-full max-w-sm space-y-4">
        <div class="space-y-2">
            <h3 class="text-white text-lg font-semibold">Tạo mật khẩu quỹ</h3>
            <p class="text-gray-300 text-sm">Bạn cần tạo mật khẩu quỹ trước khi có thể liên kết ngân hàng. Mật khẩu quỹ dùng để xác nhận các giao dịch quan trọng.</p>
        </div>
        
        <form id="createFundPasswordForm" class="space-y-3">
            @csrf
            <div class="space-y-2">
                <input type="password" name="fund_password" id="newFundPassword" class="w-full rounded-lg border border-blue-500/60 bg-[#0f1118] text-white px-3 py-3 outline-none placeholder-gray-500" style="font-size: 16px;" placeholder="Mật khẩu quỹ mới" required minlength="6">
                <input type="password" name="fund_password_confirmation" id="newFundPasswordConfirm" class="w-full rounded-lg border border-blue-500/60 bg-[#0f1118] text-white px-3 py-3 outline-none placeholder-gray-500" style="font-size: 16px;" placeholder="Nhập lại mật khẩu quỹ" required minlength="6">
            </div>
            
            <div id="fundPasswordError" class="bg-red-500/20 border border-red-500 text-red-200 text-sm rounded-lg px-3 py-2 hidden"></div>
            
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-[#2d59ff] hover:bg-[#2448d1] text-white font-semibold py-3 rounded-lg text-sm transition-colors">
                    Tạo mật khẩu quỹ
                </button>
                <button type="button" onclick="window.history.back()" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white font-semibold py-3 rounded-lg text-sm transition-colors">
                    Hủy
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    // Prevent form submission if user doesn't have fund password
    @if(!$hasFundPassword)
    document.addEventListener('DOMContentLoaded', function() {
        const bankForm = document.querySelector('form[action="{{ route('me.bank.submit') }}"]');
        const submitBtn = document.getElementById('submitBankLink');
        const fundPasswordInput = document.getElementById('fundPasswordInput');
        const modal = document.getElementById('fundPasswordModal');
        const createFundPasswordForm = document.getElementById('createFundPasswordForm');
        const fundPasswordError = document.getElementById('fundPasswordError');
        
        if (bankForm && submitBtn && modal && createFundPasswordForm) {
            // Disable bank form inputs
            bankForm.querySelectorAll('input').forEach(input => {
                input.disabled = true;
            });
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            
            // Show modal immediately
            modal.style.display = 'flex';
            
            // Prevent bank form submission
            bankForm.addEventListener('submit', function(e) {
                e.preventDefault();
                modal.style.display = 'flex';
            });
            
            // Handle create fund password form submission
            createFundPasswordForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const newPassword = document.getElementById('newFundPassword').value;
                const confirmPassword = document.getElementById('newFundPasswordConfirm').value;
                
                // Hide previous errors
                fundPasswordError.classList.add('hidden');
                
                // Validate passwords match
                if (newPassword !== confirmPassword) {
                    fundPasswordError.textContent = 'Mật khẩu xác nhận không khớp.';
                    fundPasswordError.classList.remove('hidden');
                    return;
                }
                
                // Validate minimum length
                if (newPassword.length < 6) {
                    fundPasswordError.textContent = 'Mật khẩu phải có ít nhất 6 ký tự.';
                    fundPasswordError.classList.remove('hidden');
                    return;
                }
                
                // Submit via AJAX
                const formData = new FormData(createFundPasswordForm);
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                    || createFundPasswordForm.querySelector('input[name="_token"]')?.value;
                
                try {
                    const response = await fetch('{{ route("me.create-fund-password.submit") }}', {
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
                        // Show success toast
                        if (typeof showToast === 'function') {
                            showToast(data.message || 'Tạo mật khẩu quỹ thành công.', 'success');
                        }
                        
                        // Hide modal
                        modal.style.display = 'none';
                        
                        // Enable bank form
                        bankForm.querySelectorAll('input').forEach(input => {
                            input.disabled = false;
                        });
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        
                        // Reload page to refresh user data
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        // Show error
                        fundPasswordError.textContent = data.error || data.message || 'Có lỗi xảy ra khi tạo mật khẩu quỹ.';
                        fundPasswordError.classList.remove('hidden');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    fundPasswordError.textContent = 'Có lỗi xảy ra khi tạo mật khẩu quỹ. Vui lòng thử lại.';
                    fundPasswordError.classList.remove('hidden');
                }
            });
        }
    });
    @endif
</script>
@endpush

