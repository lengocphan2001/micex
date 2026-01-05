@extends('layouts.mobile')

@section('title', 'Trò chơi - Micex')

@section('header')
<header class="w-full px-4 py-4 flex items-center justify-center bg-gray-900 border-b border-gray-800">
    <h1 class="text-white text-base font-semibold">Trò chơi</h1>
</header>
@endsection

@section('content')
<div class="px-4 py-6">
    <div class="grid grid-cols-3 gap-6">
        <a href="{{ route('games.khaithac') }}" class="flex flex-col items-center text-center">
            <div class="w-20 h-20 rounded-2xl overflow-hidden bg-gray-800 flex items-center justify-center">
                <img src="{{ asset('images/gameitems/khaithac.png') }}" alt="Khai thác" class="w-full h-full object-cover">
            </div>
            <div class="mt-2 text-white text-sm font-medium">Khai thác</div>
            <div class="mt-1">
                <span class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-[#4B5563] text-white text-xs font-semibold">60s</span>
            </div>
        </a>

        <a href="{{ route('games.xanhdo') }}" class="flex flex-col items-center text-center">
            <div class="w-20 h-20 rounded-2xl overflow-hidden bg-gray-800 flex items-center justify-center">
                <img src="{{ asset('images/gameitems/xanhdo.png') }}" alt="Xanh đỏ 60s" class="w-full h-full object-cover">
            </div>
            <div class="mt-2 text-white text-sm font-medium">Xanh đỏ</div>
            <div class="mt-1">
                <span class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-[#4B5563] text-white text-xs font-semibold">60s</span>
            </div>
        </a>
    </div>
</div>
@endsection


