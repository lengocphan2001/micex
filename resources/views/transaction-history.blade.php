@extends('layouts.mobile')

@section('title', 'Lịch sử trò chơi - Micex')

@section('header')
<header class="w-full px-4 py-4 flex items-center justify-between bg-gray-900 border-b border-gray-800">
    <button onclick="history.back()" class="text-white">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
    </button>
    <h1 class="text-white text-base font-semibold">Lịch sử trò chơi</h1>
    <div class="w-6"></div>
</header>
@endsection

@section('content')
<div class="px-4 py-4">
    <!-- Filters -->
    @php
        $gameFilter = $gameFilter ?? 'all';
        $gameOptions = $gameOptions ?? ['all' => 'Tất cả', 'khaithac' => 'Khai thác 60s', 'xanhdo' => 'Xanh đỏ 60s'];
    @endphp
    <div class="flex items-center gap-2 overflow-x-auto hide-scrollbar mb-4">
        @foreach($gameOptions as $key => $label)
            <a href="{{ route('transaction-history', ['game' => $key]) }}"
               class="px-4 py-2 rounded-full text-sm font-semibold whitespace-nowrap border transition-colors
                    {{ $gameFilter === $key ? 'bg-[#3958F5] text-white border-[#3958F5]' : 'bg-gray-800 text-gray-200 border-gray-700 hover:bg-gray-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <!-- History cards -->
    <div class="space-y-4">
        @forelse($bets as $bet)
            @php
                $round = $bet->round;
                $gameKey = $round ? ($round->game_key ?? 'khaithac') : 'khaithac';
                $gameName = $gameKey === 'xanhdo' ? 'Xanh đỏ 60s' : 'Khai thác 60s';

                $createdAt = $bet->created_at;
                $time = $createdAt->format('Y-m-d H:i:s');

                // Choice label
                if ($gameKey === 'xanhdo') {
                    // If bet_type is 'number', show the number; if 'color', show the color
                    if ($bet->bet_type === 'number' && $bet->bet_value !== null) {
                        $choiceLabel = $bet->bet_value; // Show the number
                    } else {
                        // Show color name
                        $choiceLabel = $bet->gem_type === 'kcxanh' ? 'Xanh' : ($bet->gem_type === 'kcdo' ? 'Đỏ' : 'Tím');
                    }
                } else {
                    $choiceLabel = ($gemTypes[$bet->gem_type]['name'] ?? $bet->gem_type);
                }

                // Result label
                $resultLabel = '-';
                if ($round) {
                    $finalResult = $round->admin_set_result ?? $round->final_result ?? null;
                    if ($finalResult !== null && $finalResult !== '') {
                        if ($gameKey === 'xanhdo') {
                            // For xanhdo, final_result is a number (0-9) stored as string
                            // Convert to string first to ensure consistent comparison
                            $finalResultStr = (string) $finalResult;
                            
                            // Check if it's a single digit (0-9)
                            if (preg_match('/^[0-9]$/', $finalResultStr)) {
                                $resultLabel = $finalResultStr;
                            } elseif (is_numeric($finalResultStr)) {
                                $resultNum = (int) $finalResultStr;
                                if ($resultNum >= 0 && $resultNum <= 9) {
                                    $resultLabel = (string) $resultNum;
                                } else {
                                    // Not a valid number, try legacy gem type
                                    $resultLabel = $finalResultStr === 'kcxanh' ? 'Xanh' : ($finalResultStr === 'kcdo' ? 'Đỏ' : ($finalResultStr === 'daquy' ? 'Tím' : '-'));
                                }
                            } else {
                                // Not numeric, try legacy gem type
                                $resultLabel = $finalResultStr === 'kcxanh' ? 'Xanh' : ($finalResultStr === 'kcdo' ? 'Đỏ' : ($finalResultStr === 'daquy' ? 'Tím' : '-'));
                            }
                        } else {
                            // For khaithac game
                            $resultLabel = $gemTypes[$finalResult]['name'] ?? $finalResult;
                        }
                    }
                }

                // Amount display (UX like screenshot: pending shows -amount because stake is deducted)
                $displayAmount = 0;
                $displayColor = 'text-gray-300';
                $statusText = '';
                if ($bet->status === 'pending') {
                    $displayAmount = - (float) $bet->amount;
                    $displayColor = 'text-yellow-400';
                    $statusText = 'Chờ kết quả';
                } elseif ($bet->status === 'lost') {
                    $displayAmount = - (float) $bet->amount;
                    $displayColor = 'text-red-400';
                } elseif ($bet->status === 'won') {
                    $displayAmount = (float) ($bet->payout_amount ?? ((float)$bet->amount * (float)$bet->payout_rate));
                    $displayColor = 'text-green-400';
                }
            @endphp

            <div class="rounded-2xl bg-[#0f1118] border border-white/10 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="{{ $displayColor }} text-lg font-bold">
                        @if($displayAmount > 0)
                            +{{ number_format($displayAmount, 2, '.', ',') }} USDT
                        @else
                            {{ number_format($displayAmount, 2, '.', ',') }} USDT
                        @endif
                    </div>
                    <div class="text-yellow-400 font-semibold whitespace-nowrap">
                        Thông tin chi tiết <span class="text-white/70">›</span>
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                    <div class="text-white/50">• Trò chơi</div>
                    <div class="text-white text-right">{{ $gameName }}</div>

                    <div class="text-white/50">• No.</div>
                    <div class="text-white text-right">{{ $round->round_number ?? '-' }}</div>

                    <div class="text-white/50">• Thời gian đặt cược</div>
                    <div class="text-white text-right">{{ $time }}</div>

                    <div class="text-white/50">• Lựa chọn cược</div>
                    <div class="text-white text-right">{{ $choiceLabel }}</div>

                    <div class="text-white/50">• Kết quả</div>
                    <div class="text-white text-right">{{ $resultLabel }}</div>

                    <div class="text-white/50">• Tổng đơn cược</div>
                    <div class="text-white text-right">{{ number_format($bet->amount, 2, '.', ',') }} USDT</div>
                </div>

                @if($statusText)
                    <div class="mt-3 {{ $displayColor }} font-semibold">{{ $statusText }}</div>
                @endif
            </div>
        @empty
            <div class="text-center py-10">
                <p class="text-gray-400 text-sm">Chưa có lịch sử trò chơi</p>
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

