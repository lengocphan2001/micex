<nav class="fixed bottom-0 left-0 right-0 w-full bg-[#111111] border-t border-gray-800 px-3 z-50 md:left-auto md:right-auto md:max-w-[450px]">
    <div class="flex items-center justify-between text-gray-300 text-xs gap-1">
        <!-- Home -->
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-0.5 py-2 px-2 rounded-lg text-center w-14">
            <svg class="w-6 h-6 {{ request()->routeIs('dashboard') ? 'text-blue-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="text-xs whitespace-nowrap {{ request()->routeIs('dashboard') ? 'text-blue-500' : 'text-gray-400' }}">Trang chủ</span>
        </a>

        <!-- Intro -->
        <a href="{{ route('subordinate-system') }}" class="flex flex-col items-center gap-0.5 py-2 px-2 rounded-lg w-14 text-center">
            <svg class="w-6 h-6 {{ request()->routeIs('subordinate-system') ? 'text-blue-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-xs whitespace-nowrap {{ request()->routeIs('subordinate-system') ? 'text-blue-500' : 'text-gray-400' }}">Giới thiệu</span>
        </a>

        <!-- Explore (center item - floating) -->
        <a href="{{ route('games.index') }}" class="flex flex-col items-center gap-0.5 py-2 px-2 rounded-lg text-center w-14 -mt-5">
            <img src="{{ asset('images/icons/gioithieu.png') }}" alt="Khám phá" class="w-14 h-10">
            <span class="text-xs whitespace-nowrap mt-1 {{ request()->routeIs('games.*') || request()->routeIs('explore') ? 'text-blue-500' : 'text-gray-400' }}">Khám phá</span>
        </a>

        <!-- Assets -->
        <a href="{{ route('assets') }}" class="flex flex-col items-center gap-0.5 py-2 px-2 rounded-lg w-14 text-center">
            <svg class="w-6 h-6 {{ request()->routeIs('assets') ? 'text-blue-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span class="text-xs whitespace-nowrap {{ request()->routeIs('assets') ? 'text-blue-500' : 'text-gray-400' }}">Tài sản</span>
        </a>

        <!-- Profile -->
        <a href="{{ route('me') }}" class="flex flex-col items-center gap-0.5 py-2 px-2 rounded-lg w-14 text-center">
            <svg class="w-6 h-6 {{ request()->routeIs('me') ? 'text-blue-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span class="text-xs whitespace-nowrap {{ request()->routeIs('me') ? 'text-blue-500' : 'text-gray-400' }}">Của tôi</span>
        </a>
    </div>
</nav>

