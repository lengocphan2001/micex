<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Đăng ký - Micex</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-800 md:bg-gray-800 min-h-screen flex items-center justify-center ">
    <div class="w-full md:max-w-[450px] h-screen flex flex-col mx-auto bg-gray-900 md:shadow-2xl overflow-hidden">
        <!-- Header -->
        <header class="w-full px-4 py-4 flex items-center justify-between bg-gray-900 border-b border-gray-800">
            <div class="text-white text-xl font-bold">MICEX</div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 bg-gray-800 px-3 py-1.5 rounded-full">
                    <span class="text-white text-sm">🇻🇳</span>
                    <span class="text-white text-sm font-medium">VI</span>
                </div>
                <button class="text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                </button>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto hide-scrollbar text-base leading-relaxed" style="background-color: #0F1317;">
            <div class="px-4 py-8">
                <!-- Back Button and Title -->
                <div class="flex items-center gap-4 mb-2">
                    <button onclick="window.history.back()" class="text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <h1 class="text-white text-3xl font-bold">Đăng ký tài khoản</h1>
                </div>
                <p class="text-blue-500 text-2xl font-semibold mb-8">Micex</p>

                <!-- Error Messages -->
                @if (session('error'))
                    <div class="mb-4 p-4 bg-red-500/20 border border-red-500 rounded-lg">
                        <p class="text-red-400 text-sm">{{ session('error') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-500/20 border border-red-500 rounded-lg">
                        <ul class="list-disc list-inside text-red-400 text-sm space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Registration Form -->
                <form method="POST" action="{{ route('register') }}" class="space-y-6">
                    @csrf

                    <!-- Phone Number -->
                    <div>
                        <label for="phone_number" class="block text-white text-sm font-medium mb-2">
                            Số điện thoại
                        </label>
                        <input 
                            type="tel" 
                            id="phone_number" 
                            name="phone_number" 
                            value="{{ old('phone_number') }}"
                            placeholder="Nhập số điện thoại (bắt đầu từ 0)"
                            class="w-full bg-gray-800 text-white rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('phone_number') border border-red-500 @enderror"
                            required
                        >
                        @error('phone_number')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-white text-sm font-medium mb-2">
                            Email
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="{{ old('email') }}"
                            placeholder="Nhập email"
                            class="w-full bg-gray-800 text-white rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border border-red-500 @enderror"
                            required
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Display Name -->
                    <div>
                        <label for="display_name" class="block text-white text-sm font-medium mb-2">
                            Tên hiển thị
                        </label>
                        <input 
                            type="text" 
                            id="display_name" 
                            name="display_name" 
                            value="{{ old('display_name') }}"
                            placeholder="Username"
                            class="w-full bg-gray-800 text-white rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('display_name') border border-red-500 @enderror"
                            required
                        >
                        @error('display_name')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-white text-sm font-medium mb-2">
                            Mật khẩu
                        </label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                placeholder="Nhập mật khẩu"
                                class="w-full bg-gray-800 text-white rounded-lg px-4 py-3 pr-12 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border border-red-500 @enderror"
                                required
                            >
                            <button 
                                type="button" 
                                id="togglePassword" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white focus:outline-none"
                                onclick="togglePasswordVisibility('password', 'togglePassword')"
                            >
                                <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg id="eyeOffIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <label for="password_confirmation" class="block text-white text-sm font-medium mb-2">
                            Xác nhận mật khẩu
                        </label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="password_confirmation" 
                                name="password_confirmation" 
                                placeholder="Nhập lại mật khẩu"
                                class="w-full bg-gray-800 text-white rounded-lg px-4 py-3 pr-12 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >
                            <button 
                                type="button" 
                                id="togglePasswordConfirmation" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white focus:outline-none"
                                onclick="togglePasswordVisibility('password_confirmation', 'togglePasswordConfirmation')"
                            >
                                <svg id="eyeIconConfirmation" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg id="eyeOffIconConfirmation" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Referral Code -->
                    <div>
                        <label for="referral_code" class="block text-white text-sm font-medium mb-2">
                            Mã giới thiệu
                        </label>
                        <input 
                            type="text" 
                            id="referral_code" 
                            name="referral_code" 
                            value="{{ old('referral_code') }}"
                            placeholder="Mã giới thiệu ( Bắt Buộc)"
                            class="w-full bg-gray-800 text-white rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('referral_code') border border-red-500 @enderror"
                        >
                        @error('referral_code')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="terms" 
                            name="terms" 
                            value="1"
                            class="custom-checkbox"
                            {{ old('terms', true) ? 'checked' : '' }}
                            required
                        >
                        <label for="terms" class="ml-3 text-white text-sm leading-relaxed cursor-pointer">
                            Khi đăng ký, bạn đồng ý với 
                            <a href="#" class="text-blue-500 underline hover:text-blue-400">Điều khoản bảo mật</a> 
                            và 
                            <a href="#" class="text-blue-500 underline hover:text-blue-400">Các điều khoản sử dụng dịch vụ</a>
                        </label>
                    </div>
                    @error('terms')
                        <p class="text-sm text-red-500">{{ $message }}</p>
                    @enderror

                    <!-- Register Button -->
                    <button 
                        type="submit" 
                        class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 rounded-lg transition-colors"
                    >
                        Đăng ký
                    </button>
                </form>

                <!-- Login Link -->
                <div class="mt-6 text-center">
                    <p class="text-white text-sm">
                        Bạn đã có tài khoản? 
                        <a href="{{ route('login') }}" class="text-blue-500 font-medium hover:underline">Đăng nhập</a>
                    </p>
                </div>
            </div>
        </main>
    </div>

    <script>
        function togglePasswordVisibility(inputId, buttonId) {
            const input = document.getElementById(inputId);
            const eyeIcon = document.getElementById(inputId === 'password' ? 'eyeIcon' : 'eyeIconConfirmation');
            const eyeOffIcon = document.getElementById(inputId === 'password' ? 'eyeOffIcon' : 'eyeOffIconConfirmation');
            
            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeOffIcon.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeOffIcon.classList.add('hidden');
            }
        }

        // Auto-fill referral code from URL query parameter
        document.addEventListener('DOMContentLoaded', function() {
            const referralCodeInput = document.getElementById('referral_code');
            
            if (referralCodeInput) {
                // Check if there's already a value from old() (form validation error)
                const existingValue = referralCodeInput.value.trim();
                
                // Only auto-fill if input is empty and URL has 'r' parameter
                if (!existingValue) {
                    const urlParams = new URLSearchParams(window.location.search);
                    const referralCode = urlParams.get('r');
                    
                    if (referralCode) {
                        // Trim and uppercase the referral code
                        referralCodeInput.value = referralCode.trim().toUpperCase();
                    }
                }
            }
        });
    </script>
</body>
</html>

