@extends('layouts.mobile')

@section('title', 'Hồ sơ của tôi - Micex')

@section('header')
<header class="w-full px-4 py-4 flex items-center justify-between bg-gray-900 border-b border-gray-800">
    <button onclick="history.back()" class="text-white">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
    </button>
    <h1 class="text-white text-base font-semibold">Hồ sơ của tôi</h1>
    <div class="w-6"></div>
</header>
@endsection

@section('content')
<div class="px-4 py-4 space-y-4">
    <div class="bg-[#111218] flex items-center gap-3">
        <div class="w-12 h-12 rounded-full bg-blue-500/30 border border-blue-500/60 flex items-center justify-center text-white font-bold">
            U
        </div>
        <div class="flex-1">
            <p class="text-white text-xs">Tên hiển thị</p>
            <p class="text-blue-300 text-sm font-semibold">C3002023</p>
        </div>
    </div>

    <hr class="w-full border-gray-700 h-1">

    <div class="space-y-3">
        <a href="{{ route('me.bank') }}" class="block w-full h-12 rounded-[10px] border-[0.5px] border-[#3958F5] px-4 py-3 text-base text-white transition-all hover:bg-[#3958F5]/20 hover:border-[#3958F5]/80" style="background: linear-gradient(90deg, rgba(201, 157, 62, 0) 15.06%, rgba(243, 172, 18, 0) 100%);">
            <span>Liên kết ngân hàng</span>
        </a>
        <a href="{{ route('me.change-login-password') }}" class="block w-full h-12 rounded-[10px] border-[0.5px] border-[#3958F5] px-4 py-3 text-base text-white transition-all hover:bg-[#3958F5]/20 hover:border-[#3958F5]/80" style="background: linear-gradient(90deg, rgba(201, 157, 62, 0) 15.06%, rgba(243, 172, 18, 0) 100%);">
            <span>Đổi mật khẩu đăng nhập</span>
        </a>
        <a href="{{ route('me.change-fund-password') }}" class="block w-full h-12 rounded-[10px] border-[0.5px] border-[#3958F5] px-4 py-3 text-base text-white transition-all hover:bg-[#3958F5]/20 hover:border-[#3958F5]/80" style="background: linear-gradient(90deg, rgba(201, 157, 62, 0) 15.06%, rgba(243, 172, 18, 0) 100%);">
            <span>Đổi mật khẩu quỹ</span>
        </a>
    </div>

    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="w-full bg-[#2d59ff] hover:bg-[#2448d1] text-white font-semibold py-3 rounded-full text-sm shadow">
            Đăng xuất
        </button>
    </form>
</div>
@endsection

