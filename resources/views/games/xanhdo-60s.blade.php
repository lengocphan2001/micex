@extends('layouts.mobile')

@section('title', 'Xanh đỏ 60s - Micex')

@push('styles')
<style>
    .xd-card { background: rgba(17, 17, 17, 0.65); border: 1px solid rgba(255,255,255,0.08); }
    .xd-chip { background: rgba(255,255,255,0.08); }
    .xd-selected { outline: 2px solid rgba(66, 98, 255, 0.9); outline-offset: 2px; }
    .xd-num { border: 1px solid rgba(255,255,255,0.08); }
</style>
@endpush

@section('header')
<header class="w-full px-4 py-4 flex items-center justify-between bg-gray-900 border-b border-gray-800">
    <a href="{{ route('explore') }}" class="text-white">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
    </a>
    <h1 class="text-white text-base font-semibold">Trò chơi</h1>
    <div class="w-6"></div>
</header>
@endsection

@section('content')
<div class="px-4 py-4 space-y-4">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full xd-chip">
                    <span class="block w-1.5 h-1.5 bg-white rounded-full"></span>
                    <span class="block w-1.5 h-1.5 bg-white rounded-full ml-1"></span>
                </span>
                <span class="text-white text-xl font-semibold">Xanh đỏ 1 Phút</span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <div class="flex items-center gap-2 rounded-full xd-chip px-3 py-2">
                <span class="text-white/80 text-sm">•••</span>
                <span class="w-px h-4 bg-white/15"></span>
                <a href="{{ route('explore') }}" class="text-white/80 text-sm font-semibold">X</a>
            </div>
        </div>
    </div>

    <div class="xd-card rounded-2xl p-4 space-y-3">
        <div class="flex items-start justify-between">
            <div class="space-y-2">
                <div class="text-white/90 font-medium" id="periodCurrent">No.-</div>
                <div class="text-white/60 font-medium" id="periodPrev">No.-</div>
            </div>
            <div class="flex items-center gap-2 text-red-400 font-mono">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full xd-chip text-white/80">⏱</span>
                <span id="countdownText">00:00:00</span>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <div class="text-white/80 text-sm">Kết quả gần nhất</div>
            <div class="flex items-center gap-2">
                <span class="text-white text-lg font-semibold" id="lastResultLabel">-</span>
                <span class="w-3 h-3 rounded-full bg-gray-500" id="lastResultDot"></span>
            </div>
        </div>

        <div class="text-white/75 text-xs leading-relaxed">
            Đoán giá trị của 0-9, 1, 3, 5, 7, 9 là màu xanh, 0, 2, 4, 6, 8 là màu đỏ và 0 hoặc 5 đồng thời là màu tím.
        </div>

        <div class="grid grid-cols-3 gap-3 pt-2">
            <button type="button" data-pick="do" class="xd-card rounded-xl p-3 text-center" id="pickDo">
                <div class="text-white font-semibold">Đỏ</div>
                <div class="text-white/60 text-xs mt-1" id="rateDo">1.95x</div>
            </button>
            <button type="button" data-pick="tim" class="xd-card rounded-xl p-3 text-center" id="pickTim">
                <div class="text-white font-semibold">Tím</div>
                <div class="text-white/60 text-xs mt-1" id="rateTim">4.87x</div>
            </button>
            <button type="button" data-pick="xanh" class="xd-card rounded-xl p-3 text-center" id="pickXanh">
                <div class="text-white font-semibold">Xanh</div>
                <div class="text-white/60 text-xs mt-1" id="rateXanh">1.95x</div>
            </button>
        </div>

        <div class="grid grid-cols-5 gap-3 pt-2" id="numberGrid">
            <!-- numbers injected by JS -->
        </div>
    </div>

    <div class="xd-card rounded-2xl p-4 space-y-3">
        <div class="text-white/70">Số tiền : <span class="text-white" id="balanceText">0.00</span> USDT</div>

        <div class="xd-card rounded-xl p-4 space-y-3">
            <div class="flex items-center gap-3">
                <input id="betAmount" type="number" min="0.01" step="0.01"
                       class="flex-1 bg-transparent text-white outline-none placeholder-white/30"
                       placeholder="Giá trị" value="1">
                <button type="button" id="btnDiv3" class="xd-chip rounded-lg px-4 py-2 text-white font-semibold">/3</button>
                <button type="button" id="btnMul3" class="xd-chip rounded-lg px-4 py-2 text-white font-semibold">x3</button>
            </div>
            <div class="text-white/70" id="calcText">0 × 0 = 0.00 USDT</div>
        </div>

        <button type="button" id="confirmBtn"
                class="w-full bg-[#3958F5] hover:bg-[#2f49c8] text-white font-semibold py-3 rounded-full">
            Xác nhận
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const XD_BASE_TIME = new Date('2025-01-01T00:00:00Z').getTime();
    const XD_ROUND_DURATION = 60;

    let xdSelectedPick = null; // 'do' | 'xanh' | 'tim'
    let xdSelectedNumber = null; // 0-9

    function xdCalculateRoundNumber() {
        const now = Date.now();
        const elapsed = Math.floor((now - XD_BASE_TIME) / 1000);
        return Math.floor(elapsed / XD_ROUND_DURATION) + 1;
    }

    function xdCalculateRoundDeadline(roundNumber) {
        const roundStartTime = XD_BASE_TIME + ((roundNumber - 1) * XD_ROUND_DURATION * 1000);
        return roundStartTime + (XD_ROUND_DURATION * 1000);
    }

    function xdPickToGemType(pick) {
        if (pick === 'xanh') return 'kcxanh';
        if (pick === 'do') return 'kcdo';
        return 'daquy'; // tim
    }

    function xdNumberToPick(n) {
        if (n === 0 || n === 5) return 'tim';
        if (n % 2 === 1) return 'xanh';
        return 'do';
    }

    function xdSetSelectedPick(pick) {
        xdSelectedPick = pick;
        document.querySelectorAll('[data-pick]').forEach(btn => btn.classList.remove('xd-selected'));
        const btn = document.querySelector(`[data-pick="${pick}"]`);
        if (btn) btn.classList.add('xd-selected');
        xdUpdateCalc();
    }

    function xdSetSelectedNumber(n) {
        xdSelectedNumber = n;
        document.querySelectorAll('[data-num]').forEach(el => el.classList.remove('xd-selected'));
        const el = document.querySelector(`[data-num="${n}"]`);
        if (el) el.classList.add('xd-selected');
        xdSetSelectedPick(xdNumberToPick(n));
        xdUpdateCalc();
    }

    function xdUpdateCalc() {
        const amount = parseFloat(document.getElementById('betAmount')?.value || '0') || 0;
        const val = (xdSelectedNumber !== null) ? xdSelectedNumber : (xdSelectedPick ? (xdSelectedPick === 'tim' ? 0 : (xdSelectedPick === 'xanh' ? 1 : 2)) : 0);
        const calcEl = document.getElementById('calcText');
        if (calcEl) {
            calcEl.textContent = `${val} × ${amount.toFixed(2)} = ${(val * amount).toFixed(2)} USDT`;
        }
    }

    async function xdLoadRates() {
        try {
            const res = await fetch('{{ route('xanhdo.gem-types') }}', { headers: { 'Accept': 'application/json' }});
            if (!res.ok) return;
            const list = await res.json();
            if (!Array.isArray(list)) return;
            const map = Object.fromEntries(list.map(x => [x.type, x.payout_rate]));
            const rateDo = document.getElementById('rateDo');
            const rateXanh = document.getElementById('rateXanh');
            const rateTim = document.getElementById('rateTim');
            if (rateDo && map.kcdo) rateDo.textContent = `${Number(map.kcdo).toFixed(2)}x`;
            if (rateXanh && map.kcxanh) rateXanh.textContent = `${Number(map.kcxanh).toFixed(2)}x`;
            if (rateTim && map.daquy) rateTim.textContent = `${Number(map.daquy).toFixed(2)}x`;
        } catch (e) {
            // ignore
        }
    }

    async function xdLoadBalanceAndLastResult() {
        // Balance
        try {
            const res = await fetch('{{ route('xanhdo.my-bet') }}', { headers: { 'Accept': 'application/json' }});
            if (res.ok) {
                const data = await res.json();
                if (data && data.balance !== undefined) {
                    const el = document.getElementById('balanceText');
                    if (el) el.textContent = Number(data.balance).toFixed(2);
                }
            }
        } catch (e) {}

        // Last result (previous round)
        const roundNumber = xdCalculateRoundNumber();
        const prev = roundNumber - 1;
        if (prev < 1) return;
        try {
            const res = await fetch(`{{ route('xanhdo.round-result') }}?round_number=${prev}`, { headers: { 'Accept': 'application/json' }});
            if (!res.ok) return;
            const data = await res.json();
            const result = data && (data.admin_set_result || data.result || data.final_result);
            const label = document.getElementById('lastResultLabel');
            const dot = document.getElementById('lastResultDot');
            if (!result || !label || !dot) return;

            // Map explore gem result -> color label
            if (result === 'kcxanh') {
                label.textContent = 'Xanh';
                dot.className = 'w-3 h-3 rounded-full bg-green-400';
            } else if (result === 'kcdo') {
                label.textContent = 'Đỏ';
                dot.className = 'w-3 h-3 rounded-full bg-red-400';
            } else {
                label.textContent = 'Tím';
                dot.className = 'w-3 h-3 rounded-full bg-purple-400';
            }
        } catch (e) {}
    }

    function xdTick() {
        const roundNumber = xdCalculateRoundNumber();
        const deadline = xdCalculateRoundDeadline(roundNumber);
        const now = Date.now();
        const remainingSeconds = Math.max(0, Math.floor((deadline - now) / 1000));

        const mm = String(Math.floor(remainingSeconds / 60)).padStart(2, '0');
        const ss = String(remainingSeconds % 60).padStart(2, '0');
        const cd = document.getElementById('countdownText');
        if (cd) cd.textContent = `00:${mm}:${ss}`;

        const cur = document.getElementById('periodCurrent');
        const prev = document.getElementById('periodPrev');
        if (cur) cur.textContent = `No.${roundNumber}`;
        if (prev) prev.textContent = `No.${Math.max(1, roundNumber - 1)}`;
    }

    async function xdSubmitBet() {
        const amount = parseFloat(document.getElementById('betAmount')?.value || '0');
        if (!amount || amount <= 0) {
            if (window.showToast) window.showToast('Vui lòng nhập giá trị hợp lệ', 'error');
            return;
        }
        if (!xdSelectedPick) {
            if (window.showToast) window.showToast('Vui lòng chọn Đỏ / Tím / Xanh hoặc chọn số', 'error');
            return;
        }

        const gemType = xdPickToGemType(xdSelectedPick);

        const confirmBtn = document.getElementById('confirmBtn');
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.classList.add('opacity-60');
            confirmBtn.textContent = 'Đang xử lý...';
        }

        try {
            const response = await window.csrfFetch('{{ route('xanhdo.bet') }}', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ gem_type: gemType, amount: amount }),
            });

            const data = await response.json();

            if (response.ok && data.success) {
                if (window.showToast) window.showToast(data.message || 'Đặt cược thành công!', 'success');
                await xdLoadBalanceAndLastResult();
            } else {
                if (window.showToast) window.showToast(data.error || data.message || 'Có lỗi xảy ra', 'error');
            }
        } catch (e) {
            if (window.showToast) window.showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
        } finally {
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.classList.remove('opacity-60');
                confirmBtn.textContent = 'Xác nhận';
            }
        }
    }

    function xdBuildNumberGrid() {
        const grid = document.getElementById('numberGrid');
        if (!grid) return;
        grid.innerHTML = '';
        for (let n = 0; n <= 9; n++) {
            const pick = xdNumberToPick(n);
            const bg =
                (pick === 'xanh') ? 'bg-green-500/90' :
                (pick === 'do') ? 'bg-red-500/90' :
                'bg-purple-600/90';
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.dataset.num = String(n);
            btn.className = `xd-num rounded-xl py-4 text-center text-white font-semibold ${bg}`;
            btn.innerHTML = `<div class="text-lg">${n}</div><div class="text-xs text-white/80 mt-1">9.75x</div>`;
            btn.addEventListener('click', () => xdSetSelectedNumber(n));
            grid.appendChild(btn);
        }
    }

    document.addEventListener('DOMContentLoaded', async () => {
        xdBuildNumberGrid();

        document.querySelectorAll('[data-pick]').forEach(btn => {
            btn.addEventListener('click', () => {
                xdSelectedNumber = null;
                document.querySelectorAll('[data-num]').forEach(el => el.classList.remove('xd-selected'));
                xdSetSelectedPick(btn.dataset.pick);
            });
        });

        document.getElementById('betAmount')?.addEventListener('input', xdUpdateCalc);
        document.getElementById('btnDiv3')?.addEventListener('click', () => {
            const el = document.getElementById('betAmount');
            if (!el) return;
            const v = parseFloat(el.value || '0') || 0;
            el.value = String(Math.max(0.01, v / 3).toFixed(2));
            xdUpdateCalc();
        });
        document.getElementById('btnMul3')?.addEventListener('click', () => {
            const el = document.getElementById('betAmount');
            if (!el) return;
            const v = parseFloat(el.value || '0') || 0;
            el.value = String(Math.max(0.01, v * 3).toFixed(2));
            xdUpdateCalc();
        });
        document.getElementById('confirmBtn')?.addEventListener('click', xdSubmitBet);

        xdSetSelectedPick('xanh');
        await xdLoadRates();
        await xdLoadBalanceAndLastResult();
        xdTick();
        setInterval(xdTick, 1000);
        setInterval(xdLoadBalanceAndLastResult, 15000);
    });
</script>
@endpush


