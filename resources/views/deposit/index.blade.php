@extends('layouts.mobile')

@section('title', 'Nạp - Micex')

@section('header')
<header class="w-full px-4 py-4 flex items-center justify-between bg-gray-900 border-b border-gray-800">
    <button onclick="history.back()" class="text-white">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
    </button>
    <h1 class="text-white text-base font-semibold">Nạp</h1>
    <div class="w-6"></div>
</header>
@endsection

@section('content')
<div class="px-4 py-4 space-y-4">
    <!-- Banner -->
    <div class="rounded-2xl overflow-hidden border border-blue-500/50 shadow-lg" style="background: radial-gradient(circle at 20% 20%, rgba(102,126,234,0.35), transparent 45%), radial-gradient(circle at 80% 10%, rgba(59,130,246,0.3), transparent 40%), #10121a;">
        <div class="p-4 flex items-center gap-4">
            <div class="flex-1 space-y-2">
                <p class="text-white text-base font-semibold leading-snug">
                    Bảo mật thông tin &amp; An toàn tài sản của bạn là ưu tiên hàng đầu của Micex
                </p>
                <div class="flex items-center gap-2 text-sm text-blue-100">
                    <span class="bg-white/10 px-2 py-1 rounded-full border border-white/10">VISA</span>
                    <span class="bg-white/10 px-2 py-1 rounded-full border border-white/10">Mastercard</span>
                    <span class="bg-white/10 px-2 py-1 rounded-full border border-white/10">JCB</span>
                </div>
            </div>
            <div class="w-16 h-16 flex-shrink-0">
                <img src="{{ asset('images/coin.png') }}" alt="Banner" class="w-full h-full object-contain">
            </div>
        </div>
    </div>

    <h2 class="text-base font-semibold text-white">Chưa có tài sản crypto</h2>

    <div class="space-y-3">
        <!-- Option: On-chain -->
        <div class="rounded-2xl border border-blue-500/40 bg-gradient-to-r from-[#121525] via-[#10121d] to-[#0c0f1a] shadow-lg hover:border-blue-400/80 transition-all duration-200">
            <a href="#" class="block px-4 py-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center bg-blue-500/10 border border-blue-500/30 rounded-full px-3 py-1 text-[12px] text-blue-100">
                            <img src="{{ asset('images/icons/currency.png') }}" alt="Currencies" class="h-6 w-auto object-contain">
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center justify-between">
                                <p class="text-white text-base font-semibold leading-tight">Nạp qua Crypto on-chain</p>
                                <span class="text-[12px] text-yellow-300 bg-yellow-500/10 border border-yellow-500/30 px-2 py-1 rounded-full">Sắp ra mắt</span>
                            </div>
                            
                            <p class="text-sm text-gray-300 leading-snug">Bán Micex và nhận về VND vào tài khoản ngân hàng của bạn...</p>
                        </div>
                    </div>
                    
                </div>
            </a>
        </div>

        <!-- Option: Bank -->
        <div class="rounded-2xl border border-blue-500/60 bg-gradient-to-r from-[#131a33] via-[#10172b] to-[#0d1424] shadow-lg hover:border-blue-400/80 transition-all duration-200">
            <a href="{{ route('deposit.bank') }}" class="block px-4 py-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center bg-blue-500/10 border border-blue-500/30 rounded-full px-3 py-1 text-[12px] text-blue-100">
                            <img src="{{ asset('images/icons/currency.png') }}" alt="Currencies" class="h-6 w-auto object-contain">
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center justify-between">
                                <p class="text-white text-base font-semibold leading-tight">Nạp qua tài khoản ngân hàng</p>
                            </div>
                            <p class="text-sm text-gray-200 leading-snug">Bán Micex và nhận về VND vào tài khoản ngân hàng của bạn...</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection

