@extends('layouts.mobile')

@section('title', 'Lịch sử nạp & rút - Micex')

@section('header')
<header class="w-full px-4 py-4 flex items-center justify-between bg-gray-900 border-b border-gray-800">
    <button onclick="history.back()" class="text-white">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
    </button>
    <h1 class="text-white text-base font-semibold">Lịch sử nạp & rút</h1>
    <div class="w-6"></div>
</header>
@endsection

@section('content')
<div class="px-4 py-4 space-y-3">
    <!-- Table Header -->
    <div class="grid grid-cols-4 gap-2 text-white text-xs font-semibold pb-2 border-b border-gray-600">
        <div>Số lượng</div>
        <div>Thời gian</div>
        <div>Loại</div>
        <div class="text-center">Tình trạng</div>
    </div>

    <!-- Table Rows -->
    <div class="space-y-2">
        @forelse($allRequests as $request)
        <div 
            class="grid grid-cols-4 gap-2 py-3 px-2 border-b border-gray-700/30 rounded hover:bg-gray-800/30 transition-colors cursor-pointer transaction-row"
            data-request-id="{{ $request['id'] }}"
            data-request-type="{{ $request['type'] }}"
            data-amount="{{ $request['amount'] }}"
            data-gem-amount="{{ $request['gem_amount'] ?? 0 }}"
            data-transfer-code="{{ $request['transfer_code'] ?? '' }}"
            data-status="{{ $request['status'] }}"
            data-created-at="{{ \Carbon\Carbon::parse($request['created_at'])->format('Y-m-d H:i:s') }}"
            data-approved-at="{{ $request['approved_at'] ? \Carbon\Carbon::parse($request['approved_at'])->format('Y-m-d H:i:s') : '' }}"
            data-notes="{{ $request['notes'] ?? '' }}"
            data-type="{{ $request['type'] }}"
            data-bank-name="{{ $request['bank_name'] ?? '' }}"
            data-bank-account="{{ $request['bank_account'] ?? '' }}"
            data-bank-full-name="{{ $request['bank_full_name'] ?? '' }}"
        >
            <!-- Số lượng -->
            <div>
                <p class="text-white font-medium text-sm">
                    @if($request['type'] === 'deposit')
                        +{{ number_format($request['amount'], 0, ',', '.') }} VND
                    @else
                        -{{ number_format($request['amount'], 0, ',', '.') }} VND
                    @endif
                </p>
            </div>
            
            <!-- Thời gian -->
            <div>
                <p class="text-white text-xs">{{ \Carbon\Carbon::parse($request['created_at'])->format('H:i d/m/Y') }}</p>
            </div>
            
            <!-- Loại -->
            <div>
                <p class="text-white text-xs">
                    @if($request['type'] === 'deposit')
                        Nạp
                    @else
                        Rút
                    @endif
                </p>
            </div>
            
            <!-- Tình trạng -->
            <div class="flex items-center justify-center">
                @if($request['status'] === 'pending')
                    <span class="text-orange-400 text-xs font-medium">Chưa xử lý</span>
                @elseif($request['status'] === 'approved')
                    <span class="text-green-400 text-xs font-medium">Thành công</span>
                @elseif($request['status'] === 'rejected')
                    <span class="text-red-400 text-xs font-medium">Thất bại</span>
                @endif
            </div>
        </div>
        @empty
        <div class="text-center py-8">
            <p class="text-gray-400 text-sm">Chưa có lịch sử nạp/rút nào.</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Detail Modal -->
<div id="detailModal" class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center px-4 hidden">
    <div class="bg-[#0f1118] rounded-xl border border-blue-500/60 p-6 w-full max-w-sm space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between">
            <h3 class="text-white text-lg font-semibold">Chi tiết giao dịch</h3>
            <button onclick="closeDetailModal()" class="text-gray-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <div id="modalContent" class="space-y-3 text-sm">
            <!-- Content will be populated by JavaScript -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function showDetailModal(requestId) {
        
        // Extract type and id from requestId (format: "deposit_123" or "withdraw_456")
        const parts = requestId.split('_');
        const requestType = parts[0];
        const id = parts.slice(1).join('_');
        
        
        // Try to find row by both attributes
        let row = document.querySelector(`[data-request-id="${id}"][data-request-type="${requestType}"]`);
        
        // If not found, try simpler selector
        if (!row) {
            row = document.querySelector(`[data-request-id="${id}"]`);
        }
        
        if (!row) {
            alert('Không tìm thấy thông tin giao dịch. Vui lòng thử lại.');
            return;
        }
        
        const modal = document.getElementById('detailModal');
        const content = document.getElementById('modalContent');
        
        if (!modal || !content) {
            alert('Không thể hiển thị chi tiết. Vui lòng thử lại.');
            return;
        }
        
        // Get data from attributes
        const amount = row.getAttribute('data-amount');
        const gemAmount = row.getAttribute('data-gem-amount');
        const transferCode = row.getAttribute('data-transfer-code');
        const status = row.getAttribute('data-status');
        const createdAt = row.getAttribute('data-created-at');
        const approvedAt = row.getAttribute('data-approved-at');
        const notes = row.getAttribute('data-notes');
        const type = row.getAttribute('data-type') || requestType;
        const bankName = row.getAttribute('data-bank-name');
        const bankAccount = row.getAttribute('data-bank-account');
        const bankFullName = row.getAttribute('data-bank-full-name');
        
        // Format dates
        const createdDate = createdAt ? formatDateTime(createdAt) : '---';
        const approvedDate = approvedAt ? formatDateTime(approvedAt) : '---';
        
        // Status badge
        let statusBadge = '';
        if (status === 'pending') {
            statusBadge = '<span class="text-orange-400 font-medium">Chưa xử lý</span>';
        } else if (status === 'approved') {
            statusBadge = '<span class="text-green-400 font-medium">Thành công</span>';
        } else if (status === 'rejected') {
            statusBadge = '<span class="text-red-400 font-medium">Thất bại</span>';
        }
        
        // Determine transaction type (deposit or withdraw)
        const isDeposit = type === 'deposit';
        
        if (isDeposit) {
            // Deposit transaction - only show: amount, gem amount, date, status
            content.innerHTML = `
                <div class="space-y-2 border-b border-gray-700 pb-3">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Số lượng nạp:</span>
                        <span class="text-white font-semibold">${formatNumber(amount)} VND</span>
                    </div>
                    ${gemAmount && parseFloat(gemAmount) > 0 ? `
                    <div class="flex justify-between">
                        <span class="text-gray-400">Số đá quý:</span>
                        <span class="text-white font-semibold">${formatNumber(gemAmount)} đá quý</span>
                    </div>
                    ` : ''}
                </div>
                
                <div class="space-y-2 border-b border-gray-700 pb-3">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Ngày tạo:</span>
                        <span class="text-white text-xs">${createdDate}</span>
                    </div>
                    ${approvedAt ? `
                    <div class="flex justify-between">
                        <span class="text-gray-400">Ngày xử lý:</span>
                        <span class="text-white text-xs">${approvedDate}</span>
                    </div>
                    ` : ''}
                </div>
                
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Tình trạng:</span>
                        ${statusBadge}
                    </div>
                    ${notes ? `
                    <div class="mt-2 pt-2 border-t border-gray-700">
                        <span class="text-gray-400 text-xs">Ghi chú:</span>
                        <p class="text-white text-xs mt-1">${escapeHtml(notes)}</p>
                    </div>
                    ` : ''}
                </div>
            `;
        } else {
            // Withdraw transaction - show: amount, gem amount, bank info, date, status
            content.innerHTML = `
                <div class="space-y-2 border-b border-gray-700 pb-3">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Số lượng rút:</span>
                        <span class="text-white font-semibold">${formatNumber(amount)} VND</span>
                    </div>
                    ${gemAmount && parseFloat(gemAmount) > 0 ? `
                    <div class="flex justify-between">
                        <span class="text-gray-400">Số đá quý:</span>
                        <span class="text-white font-semibold">${formatNumber(gemAmount)} đá quý</span>
                    </div>
                    ` : ''}
                </div>
                
                <div class="space-y-2 border-b border-gray-700 pb-3">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Ngân hàng:</span>
                        <span class="text-white">${bankName || '---'}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Số tài khoản:</span>
                        <span class="text-white">${bankAccount || '---'}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Họ tên:</span>
                        <span class="text-white">${bankFullName || '---'}</span>
                    </div>
                </div>
                
                <div class="space-y-2 border-b border-gray-700 pb-3">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Ngày tạo:</span>
                        <span class="text-white text-xs">${createdDate}</span>
                    </div>
                    ${approvedAt ? `
                    <div class="flex justify-between">
                        <span class="text-gray-400">Ngày xử lý:</span>
                        <span class="text-white text-xs">${approvedDate}</span>
                    </div>
                    ` : ''}
                </div>
                
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Tình trạng:</span>
                        ${statusBadge}
                    </div>
                    ${notes ? `
                    <div class="mt-2 pt-2 border-t border-gray-700">
                        <span class="text-gray-400 text-xs">Ghi chú:</span>
                        <p class="text-white text-xs mt-1">${escapeHtml(notes)}</p>
                    </div>
                    ` : ''}
                </div>
            `;
        }
        
        modal.classList.remove('hidden');
    }
    
    function formatDateTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN', { 
            day: '2-digit', 
            month: '2-digit', 
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    function closeDetailModal() {
        const modal = document.getElementById('detailModal');
        modal.classList.add('hidden');
    }
    
    function formatNumber(num) {
        if (!num) return '0';
        return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Add click event listeners to all transaction rows
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('.transaction-row');
        rows.forEach(row => {
            row.addEventListener('click', function() {
                const requestId = this.getAttribute('data-request-id');
                const requestType = this.getAttribute('data-request-type');
                showDetailModal(`${requestType}_${requestId}`);
            });
        });
        
        // Close modal when clicking outside
        const modal = document.getElementById('detailModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeDetailModal();
                }
            });
        }
    });
</script>
@endpush

