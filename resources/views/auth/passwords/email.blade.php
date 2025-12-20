<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - Micex</title>
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
                <!-- Back Button -->
                <a href="{{ route('login') }}" class="text-white mb-4 inline-block">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>

                <!-- Title -->
                <h1 class="text-white text-3xl font-bold mb-8">Quên mật khẩu</h1>

                @if (session('status'))
                    <div class="mb-4 p-4 bg-blue-500/20 border border-blue-500 rounded-lg text-blue-300 text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- Reset Form -->
                <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                    @csrf

                    <p class="text-gray-400 text-sm mb-6">
                        Nhập số điện thoại của bạn để nhận hướng dẫn đặt lại mật khẩu.
                    </p>

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
                            placeholder="Nhập số điện thoại (+84)"
                            class="w-full bg-gray-800 text-white rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('phone_number') border border-red-500 @enderror"
                            required
                            autofocus
                        >
                        @error('phone_number')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 rounded-lg transition-colors"
                    >
                        Gửi yêu cầu
                    </button>
                </form>

                <!-- Back to Login Link -->
                <div class="mt-6 text-center">
                    <a href="{{ route('login') }}" class="text-blue-500 text-sm hover:underline">
                        ← Quay lại đăng nhập
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

