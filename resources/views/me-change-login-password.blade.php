@extends('layouts.mobile')

@section('title', 'Đổi mật khẩu đăng nhập - Micex')

@section('header')
<header class="w-full px-4 py-4 flex items-center justify-between bg-gray-900 border-b border-gray-800">
    <button onclick="history.back()" class="text-white">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
    </button>
    <h1 class="text-white text-base font-semibold">Đổi mật khẩu đăng nhập</h1>
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

    <form action="{{ route('me.change-login-password.submit') }}" method="POST" class="space-y-3">
        @csrf
        <input type="password" name="current_password" class="w-full rounded-lg border border-blue-500/60 bg-[#0f1118] text-white text-sm px-3 py-3 outline-none placeholder-gray-500" placeholder="Mật khẩu hiện tại" required>
        <input type="password" name="password" class="w-full rounded-lg border border-blue-500/60 bg-[#0f1118] text-white text-sm px-3 py-3 outline-none placeholder-gray-500" placeholder="Mật khẩu mới" required>
        <input type="password" name="password_confirmation" class="w-full rounded-lg border border-blue-500/60 bg-[#0f1118] text-white text-sm px-3 py-3 outline-none placeholder-gray-500" placeholder="Nhập lại mật khẩu mới" required>

        <button type="submit" class="w-full bg-[#2d59ff] hover:bg-[#2448d1] text-white font-semibold py-3 rounded-full text-base shadow">Hoàn tất</button>
    </form>
</div>
@endsection

