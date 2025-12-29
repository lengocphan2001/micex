@extends('adminlte::page')

@section('title', 'Quản lý đại lý - Micex Admin')

@section('content_header')
    <h1>Quản lý đại lý</h1>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade in">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade in">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            {{ session('error') }}
        </div>
    @endif

    <!-- Search and Filter Section -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.agent') }}" class="d-flex align-items-end gap-2 flex-wrap">
                <div class="form-group mb-0 flex-grow-1" style="min-width: 200px;">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-control" 
                        placeholder="username"
                        value="{{ request('username') }}"
                    >
                </div>
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                </div>
                <div class="form-group mb-0 ml-auto d-flex gap-2">
                    <a href="{{ route('admin.agent', array_merge(request()->except('date_filter'), ['date_filter' => 'today'])) }}" 
                       class="btn {{ request('date_filter') === 'today' ? 'btn-primary' : 'btn-secondary' }}">
                        Hôm nay
                    </a>
                    <a href="{{ route('admin.agent', array_merge(request()->except('date_filter'), ['date_filter' => '7days'])) }}" 
                       class="btn {{ request('date_filter') === '7days' ? 'btn-primary' : 'btn-secondary' }}">
                        7 Ngày
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Agents Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Đại lý</th>
                            <th>Time</th>
                            <th>Mời</th>
                            <th>Tổng nạp đầu</th>
                            <th>Thưởng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($agents as $agent)
                            <tr>
                                <td>
                                    <strong>{{ $agent->referral_code ?? $agent->name ?? $agent->email }}</strong>
                                </td>
                                <td>
                                    <div>{{ $agent->created_at->format('H:i') }}</div>
                                    <div class="text-muted small">{{ $agent->created_at->format('d-m-Y') }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span>{{ $agent->referral_code ?? 'N/A' }}</span>
                                        <button 
                                            type="button" 
                                            class="btn btn-sm btn-link p-0" 
                                            onclick="copyToClipboard('{{ $agent->referral_code }}')"
                                            title="Sao chép mã giới thiệu"
                                        >
                                            <i class="fas fa-copy text-primary"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge badge-success" style="background-color: #28a745; padding: 0.5rem 1rem; border-radius: 20px;">
                                            {{ number_format($agent->total_first_deposit ?? 0, 2) }} USDT
                                        </span>
                                        <button 
                                            type="button" 
                                            class="btn btn-sm btn-info" 
                                            onclick="showF1DetailsModal({{ $agent->id }})"
                                            title="Xem chi tiết F1"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <form action="{{ route('admin.agent.reward', $agent->id) }}" method="POST" class="d-flex gap-2">
                                        @csrf
                                        <input 
                                            type="number" 
                                            name="amount" 
                                            class="form-control form-control-sm" 
                                            placeholder="Nhập số lượng" 
                                            step="0.01" 
                                            min="0.01"
                                            required
                                            style="width: 150px;"
                                        >
                                        <button type="submit" class="btn btn-sm btn-primary">Xác nhận</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Không có đại lý nào</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- F1 Details Modal -->
    <div id="f1DetailsModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chi tiết F1 - <span id="modalAgentName"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Display Name</th>
                                    <th>Số dư</th>
                                    <th>Số tiền nạp đầu</th>
                                    <th>Khối lượng giao dịch</th>
                                    <th>Trạng thái liên kết ngân hàng</th>
                                </tr>
                            </thead>
                            <tbody id="f1DetailsTableBody">
                                <tr>
                                    <td colspan="6" class="text-center">Đang tải...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    function copyToClipboard(text) {
        if (!text) {
            alert('Không có mã giới thiệu để sao chép');
            return;
        }
        
        navigator.clipboard.writeText(text).then(function() {
            // Show toast if available
            if (typeof showToast === 'function') {
                showToast('Đã sao chép mã giới thiệu: ' + text, 'success');
            } else {
                alert('Đã sao chép mã giới thiệu: ' + text);
            }
        }).catch(function(err) {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                if (typeof showToast === 'function') {
                    showToast('Đã sao chép mã giới thiệu: ' + text, 'success');
                } else {
                    alert('Đã sao chép mã giới thiệu: ' + text);
                }
            } catch (err) {
                alert('Không thể sao chép. Vui lòng thử lại.');
            }
            document.body.removeChild(textArea);
        });
    }

    function showF1DetailsModal(agentId) {
        const modal = $('#f1DetailsModal');
        const tableBody = document.getElementById('f1DetailsTableBody');
        
        // Show modal
        modal.modal('show');
        
        // Set loading state
        tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Đang tải...</td></tr>';
        
        // Fetch F1 details
        fetch(`/admin/api/agent/${agentId}/f1-details`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.f1_details) {
                // Update agent name
                document.getElementById('modalAgentName').textContent = data.agent.name;
                
                // Build table rows
                if (data.f1_details.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Không có F1 nào</td></tr>';
                } else {
                    let html = '';
                    data.f1_details.forEach(f1 => {
                        html += `
                            <tr>
                                <td>${escapeHtml(f1.username)}</td>
                                <td>${escapeHtml(f1.display_name)}</td>
                                <td>${formatNumber(f1.balance)} đá quý</td>
                                <td>${formatNumber(f1.first_deposit_amount)} đá quý</td>
                                <td>${formatNumber(f1.total_betting)} đá quý</td>
                                <td>
                                    <span class="badge ${f1.has_bank_account ? 'badge-success' : 'badge-warning'}">
                                        ${escapeHtml(f1.bank_status)}
                                    </span>
                                </td>
                            </tr>
                        `;
                    });
                    tableBody.innerHTML = html;
                }
            } else {
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Có lỗi xảy ra khi tải dữ liệu</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error loading F1 details:', error);
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Có lỗi xảy ra khi tải dữ liệu</td></tr>';
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatNumber(number) {
        return parseFloat(number).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
</script>
@stop

@section('css')
<style>
    .table td {
        vertical-align: middle;
    }
    .badge {
        font-size: 0.875rem;
    }
</style>
@stop
