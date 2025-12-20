@extends('layouts.mobile')

@section('title', 'Hệ thống - Micex')

@section('header')
<header class="w-full px-4 py-4 flex items-center justify-between bg-gray-900 border-b border-gray-800">
    <button onclick="history.back()" class="text-white">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
    </button>
    <h1 class="text-white text-base font-semibold">Hệ thống</h1>
    <div class="w-6"></div>
</header>
@endsection

@section('content')
<div class="px-4 py-4 space-y-6">
    <!-- Top Section: User ID and Rank -->
    <div class="flex flex-col">
        <div class="flex items-start justify-start gap-2">
            <img src="{{ asset('images/icons/people.png') }}" alt="People" class="w-5 h-5">
            <span class="text-orange-400 font-semibold text-base">{{ $user->display_name ?? 'C3002023' }}</span>
        </div>
        <div class="flex flex-col items-center">
            <div class="relative">
                <img src="{{ asset('images/icons/goal.png') }}" alt="Rank Badge" class="w-20 h-20 object-contain" style="filter: drop-shadow(0 0 15px rgba(255, 215, 0, 0.6)) drop-shadow(0 0 30px rgba(255, 215, 0, 0.3)) drop-shadow(0 0 45px rgba(255, 215, 0, 0.2));">
            </div>
            <div class="mt-2 bg-gray-800 rounded-lg px-3 py-1.5 flex items-center gap-1.5">
                <span class="text-white text-sm font-medium">Cấp 1</span>
                <button class="w-4 h-4 rounded-full bg-yellow-500 flex items-center justify-center cursor-pointer relative group" title="Thông tin về cấp độ">
                    <span class="text-white text-xs">?</span>
                    <!-- Tooltip -->
                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap z-10 border border-gray-700">
                        Thông tin về cấp độ
                        <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                            <div class="border-4 border-transparent border-t-gray-900"></div>
                        </div>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Middle Section: Subordinate System Diagram -->
    <div class="bg-gray-800 rounded-xl p-4">
        @php
            // Lọc các hệ có downline (count > 0)
            $activeLevels = [];
            $levels = ['F1', 'F2', 'F3', 'F4', 'F5', 'F6'];
            foreach ($levels as $level) {
                if (isset($downlineStats[$level]) && $downlineStats[$level]['count'] > 0) {
                    $activeLevels[] = $level;
                }
            }
        @endphp

        @if(empty($activeLevels))
            <!-- No downline message -->
            <div class="text-center py-12">
                <div class="text-gray-400 text-sm mb-2">Chưa có cấp dưới</div>
                <div class="text-gray-500 text-xs">Giới thiệu bạn bè để xây dựng network của bạn</div>
            </div>
        @else
            <div class="relative" style="min-height: {{ max(300, count($activeLevels) * 100) }}px;">
                <!-- SVG for connection lines - Simplified tree structure -->
                <svg class="absolute inset-0 w-full h-full pointer-events-none" style="z-index: 0; overflow: visible;">
                    @php
                        $centerX = '50%';
                        $titleY = 60;
                        $connectorY = 100;
                        $levelSpacing = 100;
                    @endphp
                    
                    <!-- Vertical line from title down to connector -->
                    <line x1="{{ $centerX }}" y1="{{ $titleY }}" x2="{{ $centerX }}" y2="{{ $connectorY }}" stroke="#D4AF37" stroke-width="2" />
                    
                    @foreach($activeLevels as $index => $level)
                        @php
                            $isLeft = $index % 2 == 0;
                            $rowIndex = floor($index / 2);
                            $nodeY = $connectorY + ($rowIndex * $levelSpacing) + 40;
                            $nodeX = $isLeft ? '25%' : '75%';
                        @endphp
                        
                        @if($index == 0)
                            <!-- First level: connect from center -->
                            <line x1="{{ $centerX }}" y1="{{ $connectorY }}" x2="{{ $nodeX }}" y2="{{ $nodeY }}" stroke="#D4AF37" stroke-width="2" />
                        @else
                            @php
                                $prevIsLeft = ($index - 1) % 2 == 0;
                                $prevRowIndex = floor(($index - 1) / 2);
                                $prevNodeY = $connectorY + ($prevRowIndex * $levelSpacing) + 40;
                                $prevNodeX = $prevIsLeft ? '25%' : '75%';
                            @endphp
                            
                            @if($rowIndex == $prevRowIndex)
                                <!-- Same row: horizontal line -->
                                <line x1="{{ $prevNodeX }}" y1="{{ $prevNodeY }}" x2="{{ $nodeX }}" y2="{{ $nodeY }}" stroke="#D4AF37" stroke-width="2" />
                            @else
                                <!-- New row: vertical then horizontal -->
                                <line x1="{{ $prevNodeX }}" y1="{{ $prevNodeY }}" x2="{{ $prevNodeX }}" y2="{{ $nodeY - 20 }}" stroke="#D4AF37" stroke-width="2" />
                                <line x1="25%" y1="{{ $nodeY - 20 }}" x2="75%" y2="{{ $nodeY - 20 }}" stroke="#D4AF37" stroke-width="2" />
                                <line x1="{{ $nodeX }}" y1="{{ $nodeY - 20 }}" x2="{{ $nodeX }}" y2="{{ $nodeY }}" stroke="#D4AF37" stroke-width="2" />
                            @endif
                        @endif
                    @endforeach
                </svg>

                <!-- Title Node -->
                <div class="relative z-10 flex justify-center mb-8">
                    <div class="bg-gray-700 rounded-lg px-6 py-3 border border-gray-600">
                        <span class="text-white font-medium text-sm">Hệ thống cấp dưới</span>
                    </div>
                </div>

                <!-- Dynamic Levels - Only show active levels -->
                @foreach($activeLevels as $index => $level)
                    @php
                        $isLeft = $index % 2 == 0;
                        $rowIndex = floor($index / 2);
                    @endphp
                    
                    @if($isLeft)
                        <!-- Start new row -->
                        <div class="relative z-10 flex justify-center gap-12 {{ $rowIndex > 0 ? 'mt-8' : 'mb-8' }}">
                    @endif
                    
                    <div class="flex flex-col items-center">
                        <div class="bg-gray-700 rounded-lg px-5 py-3 border border-gray-600 min-w-[80px] hover:bg-gray-600 transition-colors cursor-pointer">
                            <div class="text-white font-medium text-sm text-center">{{ $level }}</div>
                            <div class="text-white text-xs text-center mt-1 font-semibold">{{ $downlineStats[$level]['count'] ?? 0 }}</div>
                        </div>
                    </div>
                    
                    @if(!$isLeft || $index == count($activeLevels) - 1)
                        <!-- End row -->
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    <!-- Bottom Section: Transaction Volume -->
    <div class="space-y-3">
        <h3 class="text-white font-semibold text-base">Khối lượng giao dịch <span class="text-gray-400">?</span></h3>
        
        <div class="space-y-2">
            @php
                // Chỉ hiển thị các hệ có downline (count > 0) và có khối lượng giao dịch
                $levelsToShow = ['F1', 'F2', 'F3', 'F4', 'F5', 'F6'];
            @endphp
            
            @foreach($levelsToShow as $level)
                @if(isset($downlineStats[$level]) && $downlineStats[$level]['count'] > 0 && isset($transactionVolumes[$level]) && $transactionVolumes[$level] > 0)
                <div class="bg-gray-800 rounded-lg px-4 py-3">
                    <div class="flex justify-between items-center">
                        <span class="text-white font-medium">{{ $level }}</span>
                        <span class="text-white font-semibold">{{ number_format($transactionVolumes[$level], 2, '.', ',') }}$</span>
                    </div>
                </div>
                @endif
            @endforeach
        </div>

        <!-- Total - chỉ hiển thị nếu có ít nhất 1 hệ có giao dịch -->
        @if(isset($transactionVolumes['total']) && $transactionVolumes['total'] > 0)
        <div class="bg-gray-800 rounded-lg px-4 py-3 border border-blue-500/50">
            <div class="flex justify-between items-center">
                <span class="text-blue-400 font-semibold">Tổng hệ thống</span>
                <span class="text-white font-bold text-lg">{{ number_format($transactionVolumes['total'], 2, '.', ',') }}$</span>
            </div>
        </div>
        @else
        <div class="bg-gray-800 rounded-lg px-4 py-3">
            <div class="text-center text-gray-400 text-sm">
                Chưa có khối lượng giao dịch
            </div>
        </div>
        @endif
    </div>

    <!-- Commission Section -->
    <div class="space-y-3">
        <h3 class="text-white font-semibold text-base">Hoa hồng Network</h3>
        
        <!-- Commission Summary -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl p-4 space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-white/90 text-sm">Hoa hồng có thể rút:</span>
                <span class="text-white font-bold text-xl">{{ number_format($availableCommission ?? 0, 2, '.', ',') }}$</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-white/90 text-sm">Tổng hoa hồng:</span>
                <span class="text-white font-semibold">{{ number_format($totalCommission ?? 0, 2, '.', ',') }}$</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-white/90 text-sm">Đã rút:</span>
                <span class="text-white font-semibold">{{ number_format($withdrawnCommission ?? 0, 2, '.', ',') }}$</span>
            </div>
            
            @if(($availableCommission ?? 0) > 0)
            <button 
                id="withdrawCommissionBtn" 
                onclick="withdrawCommission()" 
                class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-3 rounded-lg transition-colors"
            >
                Rút hoa hồng
            </button>
            @else
            <button 
                disabled 
                class="w-full bg-gray-600 text-gray-400 font-semibold py-3 rounded-lg cursor-not-allowed"
            >
                Không có hoa hồng để rút
            </button>
            @endif
        </div>

        <!-- Commission by Level -->
        @if(!empty($commissionByLevel))
        <div class="bg-gray-800 rounded-xl p-4 space-y-2">
            <h4 class="text-white font-semibold text-sm mb-3">Hoa hồng theo hệ:</h4>
            @foreach($commissionByLevel as $level => $amount)
            <div class="flex justify-between items-center py-2 border-b border-gray-700 last:border-0">
                <span class="text-white font-medium">{{ $level }}:</span>
                <span class="text-green-400 font-semibold">{{ number_format($amount, 2, '.', ',') }}$</span>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Recent Commissions -->
        @if(isset($recentCommissions) && $recentCommissions->count() > 0)
        <div class="bg-gray-800 rounded-xl p-4 space-y-2">
            <h4 class="text-white font-semibold text-sm mb-3">Lịch sử hoa hồng gần đây:</h4>
            <div class="space-y-2 max-h-60 overflow-y-auto">
                @foreach($recentCommissions as $commission)
                <div class="bg-gray-700 rounded-lg px-3 py-2">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="text-white text-xs font-medium">{{ $commission->level }} - {{ $commission->fromUser->display_name ?? 'N/A' }}</div>
                            <div class="text-gray-400 text-xs mt-1">
                                Bet: {{ number_format($commission->bet_amount, 2, '.', ',') }}$ 
                                ({{ $commission->commission_rate }}%)
                            </div>
                            <div class="text-gray-400 text-xs">
                                {{ $commission->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-green-400 font-semibold text-sm">
                                +{{ number_format($commission->commission_amount, 2, '.', ',') }}$
                            </div>
                            <div class="text-xs mt-1">
                                @if($commission->status === 'available')
                                    <span class="text-yellow-400">Có thể rút</span>
                                @elseif($commission->status === 'withdrawn')
                                    <span class="text-gray-400">Đã rút</span>
                                @else
                                    <span class="text-gray-500">Chờ xử lý</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    async function withdrawCommission() {
        const btn = document.getElementById('withdrawCommissionBtn');
        if (!btn) return;
        
        if (!confirm('Bạn có chắc muốn rút toàn bộ hoa hồng?')) {
            return;
        }
        
        btn.disabled = true;
        btn.textContent = 'Đang xử lý...';
        
        try {
            const response = await fetch('{{ route("subordinate-system.withdraw-commission") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
                alert(data.message || 'Rút hoa hồng thành công!');
                location.reload(); // Reload để cập nhật số liệu
            } else {
                alert(data.error || 'Có lỗi xảy ra khi rút hoa hồng');
                btn.disabled = false;
                btn.textContent = 'Rút hoa hồng';
            }
        } catch (error) {
            console.error('Error withdrawing commission:', error);
            alert('Có lỗi xảy ra. Vui lòng thử lại.');
            btn.disabled = false;
            btn.textContent = 'Rút hoa hồng';
        }
    }
</script>
@endpush


