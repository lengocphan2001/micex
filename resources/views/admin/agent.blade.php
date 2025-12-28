@extends('adminlte::page')

@section('title', 'Quản lý đại lý - Micex Admin')

@section('content_header')
    <h1>Quản lý đại lý</h1>
    <p class="text-muted">Trang quản lý đại lý sẽ được phát triển tại đây.</p>
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
                                    <span class="badge badge-success" style="background-color: #28a745; padding: 0.5rem 1rem; border-radius: 20px;">
                                        {{ number_format($agent->total_first_deposit ?? 0, 2) }} USDT
                                    </span>
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
