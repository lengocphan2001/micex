@extends('layouts.mobile')

@section('title', 'Lịch sử giao dịch - Micex')

@section('header')
<header class="w-full px-4 py-4 flex items-center justify-between bg-gray-900 border-b border-gray-800">
    <button onclick="history.back()" class="text-white">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
    </button>
    <h1 class="text-white text-base font-semibold">Lịch sử giao dịch</h1>
    <div class="w-6"></div>
</header>
@endsection

@section('content')
<div class="px-4 py-4">
    <!-- Table Header -->
    <div class="grid grid-cols-4 gap-3 mb-4 text-white text-sm font-semibold pb-3 border-b border-gray-600">
        <div class="text-center">Thời gian</div>
        <div class="text-center">Lựa chọn</div>
        <div class="text-center">Số lượng</div>
        <div class="text-center">Lợi nhuận</div>
    </div>

    <!-- Table Rows -->
    <div class="space-y-3">
        @forelse($bets as $bet)
            @php
                $gemType = $gemTypes[$bet->gem_type] ?? null;
                $gemIcon = $gemType ? asset('images/icons/' . $gemType['icon']) : asset('images/icons/thachanh.png');
                $gemName = $gemType ? $gemType['name'] : 'N/A';
                
                $profitAmount = 0;
                $profitColor = 'text-gray-400';
                
                if ($bet->status === 'won') {
                    $profitAmount = $bet->payout_amount ?? ($bet->amount * $bet->payout_rate);
                    $profitColor = 'text-green-400';
                } elseif ($bet->status === 'lost') {
                    $profitAmount = -$bet->amount;
                    $profitColor = 'text-red-400';
                } elseif ($bet->status === 'pending') {
                    $profitAmount = 0;
                    $profitColor = 'text-gray-400';
                }
                
                // Format date and time
                $createdAt = $bet->created_at;
                $time = $createdAt->format('H:i');
                $date = $createdAt->format('d-m-Y');
            @endphp
            
        <div class="grid grid-cols-4 gap-3 py-3 border-b border-gray-700/30 hover:bg-gray-800/30 transition-colors rounded px-2">
            <div class="text-white text-xs text-center leading-tight">
                    <div class="font-medium">{{ $time }}</div>
                    <div class="text-gray-400">{{ $date }}</div>
            </div>
            <div class="flex items-center justify-center">
                    <img src="{{ $gemIcon }}" alt="{{ $gemName }}" class="w-8 h-8 object-contain">
            </div>
            <div class="text-white text-xs text-center flex items-center justify-center gap-1.5">
                    <span class="font-medium">{{ number_format($bet->amount, 2, '.', ',') }}</span>
                <img src="{{ asset('images/icons/coin_asset.png') }}" alt="Coin" class="w-5 h-5 object-contain">
            </div>
                <div class="{{ $profitColor }} text-xs text-center font-bold flex items-center justify-center">
                    @if($profitAmount > 0)
                        +{{ number_format($profitAmount, 2, '.', ',') }}$
                    @elseif($profitAmount < 0)
                        {{ number_format($profitAmount, 2, '.', ',') }}$
                    @else
                        -
                    @endif
            </div>
            </div>
        @empty
            <div class="text-center py-8">
                <p class="text-gray-400 text-sm">Chưa có giao dịch nào</p>
            </div>
        @endforelse
        </div>

    <!-- Pagination -->
    @if($bets->hasPages())
        <div class="mt-6 flex justify-center">
            <div class="flex gap-2">
                @if($bets->onFirstPage())
                    <span class="px-4 py-2 bg-gray-800 text-gray-500 rounded-lg cursor-not-allowed">Trước</span>
                @else
                    <a href="{{ $bets->previousPageUrl() }}" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition-colors">Trước</a>
                @endif
                
                <span class="px-4 py-2 bg-gray-800 text-white rounded-lg">
                    Trang {{ $bets->currentPage() }} / {{ $bets->lastPage() }}
                </span>
                
                @if($bets->hasMorePages())
                    <a href="{{ $bets->nextPageUrl() }}" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition-colors">Sau</a>
                @else
                    <span class="px-4 py-2 bg-gray-800 text-gray-500 rounded-lg cursor-not-allowed">Sau</span>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection

