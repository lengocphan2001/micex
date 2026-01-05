@extends('layouts.mobile')

@section('title', 'Của tôi - Micex')

@section('header')
<header class="w-full px-4 py-4 flex items-center justify-center bg-gray-900 border-b border-gray-800">
    <h1 class="text-white text-base font-semibold">Của tôi</h1>
</header>
@endsection

@section('content')
<div class="px-4 py-4 pb-24 space-y-4">
    <!-- New user reward banner -->
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-full bg-blue-500/30 border border-blue-500/60 flex items-center justify-center text-white font-bold">
            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
        </div>
        <div class="flex-1">
            <p class="text-white font-semibold text-base">{{ auth()->user()->display_name ?? 'User' }}</p>
            <p class="text-gray-400 text-sm">{{ auth()->user()->email ?? 'user@example.com' }}</p>
        </div>
        <a href="{{ route('me.edit') }}" class="inline-flex items-center justify-center w-6 h-6 bg-blue-500 rounded-full">
            <i class="fas fa-chevron-right text-white text-xs"></i>
        </a>
    </div>

    

    <div class="flex items-center gap-3 rounded-xl border border-blue-500/60 bg-[#2d59ff] px-3 py-3 shadow">
        <img src="{{ asset('images/gift_withdraw.png') }}" alt="Gift" class="w-9 h-9 flex-shrink-0">
        <div class="text-white text-sm leading-snug">
            <p class="font-semibold">Phần thưởng cho người dùng mới</p>
            <p class="text-white/90">Phần thưởng lên đến 1000 USDT!</p>
        </div>
    </div>

    <hr class="w-full border-gray-700 h-1">

    <!-- Chương trình giới thiệu -->
    <div class="">
        <div class="flex items-center justify-between mb-2">
            <div class="flex-1">
                <h3 class="text-[#3958F5] font-semibold text-base mb-1">Chương trình giới thiệu</h3>
                <p class="text-gray-300 text-xs">Giới thiệu bạn bè để hưởng hoa hồng lên tới <span class="text-[#3958F5]">50%</span></p>
            </div>
            <div class="relative w-16 h-16 flex-shrink-0">
                <img src="{{ asset('images/f1.png') }}" alt="Referral" class="w-full h-full object-contain">
            </div>
        </div>
        
    </div>
    <hr class="w-full border-gray-700 h-1">

    <div class="space-y-3">
        <a href="{{ route('transaction-history') }}" class="block w-full h-12 rounded-[10px] border-[0.5px] border-[#3958F5] px-4 py-3 text-base text-white transition-all hover:bg-[#3958F5]/20 hover:border-[#3958F5]/80" style="background: linear-gradient(90deg, rgba(201, 157, 62, 0) 15.06%, rgba(243, 172, 18, 0) 100%);">
            <span>Lịch sử trò chơi</span>
        </a>
        <a href="{{ route('deposit-withdraw-history') }}" class="block w-full h-12 rounded-[10px] border-[0.5px] border-[#3958F5] px-4 py-3 text-base text-white transition-all hover:bg-[#3958F5]/20 hover:border-[#3958F5]/80" style="background: linear-gradient(90deg, rgba(201, 157, 62, 0) 15.06%, rgba(243, 172, 18, 0) 100%);">
            <span>Lịch sử nạp & rút</span>
        </a>
        <a href="{{ route('subordinate-system') }}" class="block w-full h-12 rounded-[10px] border-[0.5px] border-[#3958F5] px-4 py-3 text-base text-white transition-all hover:bg-[#3958F5]/20 hover:border-[#3958F5]/80" style="background: linear-gradient(90deg, rgba(201, 157, 62, 0) 15.06%, rgba(243, 172, 18, 0) 100%);">
            <span>Đội nhóm của tôi</span>
        </a>
    </div>

    <form action="{{ route('logout') }}" method="POST" class="mt-4 mb-20">
        @csrf
        <button type="submit" class="w-full bg-[#2d59ff] hover:bg-[#2448d1] text-white font-semibold py-3 rounded-full text-sm shadow">
            Đăng xuất
        </button>
    </form>
</div>
@endsection

