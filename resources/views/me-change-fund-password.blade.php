@extends('layouts.mobile')

@section('title', 'Đổi mật khẩu quỹ - Micex')

@section('header')
<header class="w-full px-4 py-4 flex items-center justify-between bg-gray-900 border-b border-gray-800">
    <button onclick="history.back()" class="text-white">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
    </button>
    <h1 class="text-white text-base font-semibold">Đổi mật khẩu quỹ</h1>
    <div class="w-6"></div>
</header>
@endsection

@section('content')
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

    <form id="changeFundPasswordForm" action="{{ route('me.change-fund-password.submit') }}" method="POST" class="space-y-3">
        @csrf
        <input type="password" name="current_fund_password" id="currentFundPassword" class="w-full rounded-lg border border-blue-500/60 bg-[#0f1118] text-white px-3 py-3 outline-none placeholder-gray-500" style="font-size: 16px;" placeholder="Mật khẩu quỹ hiện tại" required>
        <input type="password" name="fund_password" id="newFundPassword" class="w-full rounded-lg border border-blue-500/60 bg-[#0f1118] text-white px-3 py-3 outline-none placeholder-gray-500" style="font-size: 16px;" placeholder="Mật khẩu quỹ mới" required>
        <input type="password" name="fund_password_confirmation" id="newFundPasswordConfirm" class="w-full rounded-lg border border-blue-500/60 bg-[#0f1118] text-white px-3 py-3 outline-none placeholder-gray-500" style="font-size: 16px;" placeholder="Nhập lại mật khẩu quỹ mới" required>
        <div class="relative">
            <input type="text" name="verification_code" id="verificationCodeInput" class="w-full rounded-lg border border-blue-500/60 bg-[#0f1118] text-white px-3 py-3 pr-24 outline-none placeholder-gray-500" style="font-size: 16px;" placeholder="Mã xác nhận" required>
            <button type="button" id="sendVerificationCodeBtn" class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-[#2d59ff] hover:bg-[#2448d1] text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
                Gửi mã
            </button>
        </div>
        <p id="verificationCodeStatus" class="text-xs text-gray-400 hidden"></p>
        <div id="formError" class="bg-red-500/20 border border-red-500 text-red-200 text-sm rounded-lg px-3 py-2 hidden"></div>

        <button type="submit" id="submitBtn" class="w-full bg-[#2d59ff] hover:bg-[#2448d1] text-white font-semibold py-3 rounded-full text-base shadow">Hoàn tất</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sendBtn = document.getElementById('sendVerificationCodeBtn');
        const statusText = document.getElementById('verificationCodeStatus');
        const verificationCodeInput = document.getElementById('verificationCodeInput');
        let countdownInterval = null;
        let remainingSeconds = 0;

        function updateCountdown() {
            if (remainingSeconds > 0) {
                remainingSeconds--;
                sendBtn.textContent = `Gửi lại (${remainingSeconds}s)`;
                sendBtn.disabled = true;
                sendBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                sendBtn.textContent = 'Gửi mã';
                sendBtn.disabled = false;
                sendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                if (countdownInterval) {
                    clearInterval(countdownInterval);
                    countdownInterval = null;
                }
            }
        }

        if (sendBtn) {
            sendBtn.addEventListener('click', async function() {
                if (sendBtn.disabled) return;

                // Disable button immediately
                sendBtn.disabled = true;
                sendBtn.classList.add('opacity-50', 'cursor-not-allowed');
                sendBtn.textContent = 'Đang gửi...';
                
                // Hide previous status
                if (statusText) {
                    statusText.classList.add('hidden');
                }

                let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                    || document.querySelector('input[name="_token"]')?.value;

                try {
                    let response = await fetch('{{ route("me.send-fund-password-verification-code") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || '',
                        },
                    });

                    // Handle 419 CSRF token mismatch
                    if (response.status === 419) {
                        // Try to refresh token and retry once
                        try {
                            const refreshResponse = await fetch('/csrf-token', {
                                method: 'GET',
                                credentials: 'same-origin',
                                headers: {
                                    'Accept': 'application/json',
                                },
                            });
                            
                            if (refreshResponse.ok) {
                                const refreshData = await refreshResponse.json();
                                if (refreshData && refreshData.token) {
                                    // Update meta tag
                                    const metaTag = document.querySelector('meta[name="csrf-token"]');
                                    if (metaTag) {
                                        metaTag.setAttribute('content', refreshData.token);
                                    }
                                    csrfToken = refreshData.token;
                                    
                                    // Retry request with new token
                                    response = await fetch('{{ route("me.send-fund-password-verification-code") }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'Accept': 'application/json',
                                            'X-CSRF-TOKEN': csrfToken,
                                        },
                                    });
                                }
                            }
                        } catch (refreshError) {
                            console.error('Failed to refresh CSRF token:', refreshError);
                        }
                    }

                    const data = await response.json();

                    if (response.ok && data.success) {
                        // Show success message
                        if (statusText) {
                            statusText.textContent = data.message || 'Mã xác nhận đã được gửi đến email của bạn.';
                            statusText.classList.remove('hidden', 'text-red-400');
                            statusText.classList.add('text-green-400');
                        }

                        // Show toast
                        if (typeof showToast === 'function') {
                            showToast(data.message || 'Mã xác nhận đã được gửi đến email của bạn.', 'success');
                        }

                        // Start countdown (60 seconds)
                        remainingSeconds = 60;
                        if (countdownInterval) {
                            clearInterval(countdownInterval);
                        }
                        countdownInterval = setInterval(updateCountdown, 1000);
                        updateCountdown();
                    } else {
                        // Show error message
                        if (statusText) {
                            statusText.textContent = data.error || data.message || 'Có lỗi xảy ra. Vui lòng thử lại.';
                            statusText.classList.remove('hidden', 'text-green-400');
                            statusText.classList.add('text-red-400');
                        }

                        // Show toast
                        if (typeof showToast === 'function') {
                            showToast(data.error || data.message || 'Có lỗi xảy ra. Vui lòng thử lại.', 'error');
                        }

                        // Re-enable button
                        sendBtn.disabled = false;
                        sendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        sendBtn.textContent = 'Gửi mã';
                    }
                } catch (error) {
                    
                    // Show error message
                    if (statusText) {
                        statusText.textContent = 'Có lỗi xảy ra. Vui lòng thử lại.';
                        statusText.classList.remove('hidden', 'text-green-400');
                        statusText.classList.add('text-red-400');
                    }

                    // Show toast
                    if (typeof showToast === 'function') {
                        showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
                    }

                    // Re-enable button
                    sendBtn.disabled = false;
                    sendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    sendBtn.textContent = 'Gửi mã';
                }
            });
        }

        // Handle form submission with CSRF token refresh
        const changeFundPasswordForm = document.getElementById('changeFundPasswordForm');
        const submitBtn = document.getElementById('submitBtn');
        const formError = document.getElementById('formError');

        if (changeFundPasswordForm && submitBtn) {
            changeFundPasswordForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                // Disable submit button
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                submitBtn.textContent = 'Đang xử lý...';

                // Hide previous errors
                if (formError) {
                    formError.classList.add('hidden');
                }

                // Get CSRF token
                let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                    || changeFundPasswordForm.querySelector('input[name="_token"]')?.value;

                // Create FormData
                const formData = new FormData(changeFundPasswordForm);

                try {
                    let response = await fetch(changeFundPasswordForm.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || '',
                        },
                    });

                    // Handle 419 CSRF token mismatch
                    if (response.status === 419) {
                        // Try to refresh token and retry once
                        try {
                            const refreshResponse = await fetch('/csrf-token', {
                                method: 'GET',
                                credentials: 'same-origin',
                                headers: {
                                    'Accept': 'application/json',
                                },
                            });
                            
                            if (refreshResponse.ok) {
                                const refreshData = await refreshResponse.json();
                                if (refreshData && refreshData.token) {
                                    // Update meta tag
                                    const metaTag = document.querySelector('meta[name="csrf-token"]');
                                    if (metaTag) {
                                        metaTag.setAttribute('content', refreshData.token);
                                    }
                                    
                                    // Update form token
                                    const formTokenInput = changeFundPasswordForm.querySelector('input[name="_token"]');
                                    if (formTokenInput) {
                                        formTokenInput.value = refreshData.token;
                                    }
                                    
                                    csrfToken = refreshData.token;
                                    
                                    // Update FormData with new token
                                    formData.set('_token', csrfToken);
                                    
                                    // Retry request with new token
                                    response = await fetch(changeFundPasswordForm.action, {
                                        method: 'POST',
                                        body: formData,
                                        headers: {
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'Accept': 'application/json',
                                            'X-CSRF-TOKEN': csrfToken,
                                        },
                                    });
                                }
                            }
                        } catch (refreshError) {
                            console.error('Failed to refresh CSRF token:', refreshError);
                        }
                    }

                    // Check if response is JSON
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        if (response.status === 419) {
                            throw new Error('CSRF token mismatch. Vui lòng refresh trang và thử lại.');
                        }
                        throw new Error('Server trả về response không hợp lệ');
                    }

                    const data = await response.json();

                    if (response.ok) {
                        // Success - redirect or show success message
                        if (typeof showToast === 'function') {
                            showToast(data.message || 'Đổi mật khẩu quỹ thành công.', 'success');
                        }

                        // Redirect after 1.5 seconds
                        setTimeout(() => {
                            window.location.href = '{{ route("me.edit") }}';
                        }, 1500);
                    } else {
                        // Handle validation errors (422)
                        if (response.status === 422 && data.errors) {
                            const firstError = Object.values(data.errors)[0];
                            const errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                            
                            if (formError) {
                                formError.textContent = errorMessage;
                                formError.classList.remove('hidden');
                            }
                            
                            if (typeof showToast === 'function') {
                                showToast(errorMessage, 'error');
                            }
                        } else {
                            // Handle other errors
                            const errorMessage = data.message || data.error || 'Có lỗi xảy ra khi đổi mật khẩu quỹ.';
                            
                            if (formError) {
                                formError.textContent = errorMessage;
                                formError.classList.remove('hidden');
                            }
                            
                            if (typeof showToast === 'function') {
                                showToast(errorMessage, 'error');
                            }
                        }

                        // Re-enable submit button
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        submitBtn.textContent = 'Hoàn tất';
                    }
                } catch (error) {
                    console.error('Error:', error);
                    
                    const errorMessage = error.message || 'Có lỗi xảy ra khi đổi mật khẩu quỹ. Vui lòng thử lại.';
                    
                    if (formError) {
                        formError.textContent = errorMessage;
                        formError.classList.remove('hidden');
                    }
                    
                    if (typeof showToast === 'function') {
                        showToast(errorMessage, 'error');
                    }

                    // Re-enable submit button
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    submitBtn.textContent = 'Hoàn tất';
                }
            });
        }
    });
</script>
@endpush

