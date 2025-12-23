@extends('adminlte::page')

@section('title', 'Khuyến mãi & Giftcode - Micex Admin')

@section('content_header')
    <h1>Quản lý Khuyến mãi & Giftcode</h1>
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

    <!-- Active Promotion Display -->
    @if($activePromotion)
    <div class="card card-warning">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-gift"></i> Sự kiện khuyến mãi đang diễn ra
            </h3>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <strong>Khuyến mãi nạp: {{ number_format($activePromotion->deposit_percentage, 2) }}%</strong><br>
                <small>Thời gian: {{ \Carbon\Carbon::parse($activePromotion->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($activePromotion->end_date)->format('d/m/Y') }}</small>
            </div>
            <form action="{{ route('admin.promotion.update', $activePromotion->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="edit_deposit_percentage">Phần % khuyến mãi nạp</label>
                    <input type="number" step="0.01" min="0" max="100" class="form-control @error('deposit_percentage') is-invalid @enderror" 
                           id="edit_deposit_percentage" name="deposit_percentage" 
                           value="{{ old('deposit_percentage', $activePromotion->deposit_percentage) }}" required>
                    @error('deposit_percentage')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Ngày bắt đầu và kết thúc</label>
                    <div class="row">
                        <div class="col-6">
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                   id="edit_start_date" name="start_date" 
                                   value="{{ old('start_date', $activePromotion->start_date->format('Y-m-d')) }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-6">
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                   id="edit_end_date" name="end_date" 
                                   value="{{ old('end_date', $activePromotion->end_date->format('Y-m-d')) }}" required>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save"></i> Cập nhật khuyến mãi
                </button>
            </form>
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Section 1: Tạo sự kiện khuyến mãi mới -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tạo sự kiện khuyến mãi mới</h3>
                </div>
                <div class="card-body">
                    @if($activePromotion)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Hiện tại đang có sự kiện khuyến mãi đang diễn ra. Tạo sự kiện mới sẽ tự động tắt sự kiện hiện tại.
                    </div>
                    @endif
                    <form action="{{ route('admin.promotion.create') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="deposit_percentage">Phần % khuyến mãi nạp</label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control @error('deposit_percentage') is-invalid @enderror" 
                                   id="deposit_percentage" name="deposit_percentage" 
                                   value="{{ old('deposit_percentage') }}" placeholder="Phần % khuyến mãi nạp" required>
                            @error('deposit_percentage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="start_date">Ngày bắt đầu và kết thúc</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                           id="start_date" name="start_date" 
                                           value="{{ old('start_date') }}" required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-6">
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                           id="end_date" name="end_date" 
                                           value="{{ old('end_date') }}" required>
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Tạo mới</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Section 2: Tạo Giftcode -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tạo Giftcode</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.giftcode.create') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="quantity">Số lượng giftcode</label>
                            <input type="number" min="1" max="10000" class="form-control @error('quantity') is-invalid @enderror" 
                                   id="quantity" name="quantity" 
                                   value="{{ old('quantity', 1) }}" placeholder="Số lượng giftcode" required>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="value">Giá trị</label>
                            <input type="number" step="0.01" min="0" class="form-control @error('value') is-invalid @enderror" 
                                   id="value" name="value" 
                                   value="{{ old('value') }}" placeholder="Giá trị" required>
                            @error('value')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="expires_at">Thời hạn</label>
                            <input type="date" class="form-control @error('expires_at') is-invalid @enderror" 
                                   id="expires_at" name="expires_at" 
                                   value="{{ old('expires_at') }}" placeholder="Thời hạn">
                            @error('expires_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Xác nhận</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: Giftcode đang hoạt động -->
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Giftcode đang hoạt động</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Mã giftcode</th>
                            <th>Số lượng</th>
                            <th>Time</th>
                            <th>Đã dùng</th>
                            <th>Số tiền</th>
                            <th>Tuỳ chọn</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($giftcodes as $giftcode)
                            <tr>
                                <td>{{ $giftcode->code }}</td>
                                <td>{{ $giftcode->quantity }}</td>
                                <td>
                                    <div>{{ $giftcode->created_at->format('d/m/Y') }}</div>
                                    @if($giftcode->expires_at)
                                        <div>{{ $giftcode->expires_at->format('d/m/Y') }}</div>
                                    @else
                                        <div class="text-muted">Không giới hạn</div>
                                    @endif
                                </td>
                                <td>{{ $giftcode->used_count }}</td>
                                <td>{{ number_format($giftcode->value, 2) }}$</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" onclick="viewGiftcodeHistory({{ $giftcode->id }}, '{{ $giftcode->code }}')">
                                        <i class="fas fa-eye"></i> Xem lịch sử
                                    </button>
                                    @if($giftcode->is_active)
                                        <form action="{{ route('admin.giftcode.cancel', $giftcode->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn huỷ bỏ giftcode này?')">Huỷ bỏ</button>
                                        </form>
                                    @else
                                        <span class="text-muted">Đã huỷ</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Chưa có giftcode nào</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal: Lịch sử sử dụng Giftcode -->
    <div class="modal fade" id="giftcodeHistoryModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="fas fa-history"></i> Lịch sử sử dụng Giftcode: <span id="modalGiftcodeCode"></span>
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="giftcodeHistoryContent">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                            <p>Đang tải...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    function viewGiftcodeHistory(giftcodeId, giftcodeCode) {
        // Set giftcode code in modal title
        document.getElementById('modalGiftcodeCode').textContent = giftcodeCode;
        
        // Show modal
        $('#giftcodeHistoryModal').modal('show');
        
        // Load first page
        loadGiftcodeHistory(giftcodeId, 1);
    }
    
    function loadGiftcodeHistory(giftcodeId, page = 1) {
        const contentDiv = document.getElementById('giftcodeHistoryContent');
        contentDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Đang tải...</p></div>';
        
        fetch(`/admin/promotion-giftcode/giftcode/${giftcodeId}/history?page=${page}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = '<div class="table-responsive">';
                html += '<table class="table table-bordered table-striped table-hover">';
                html += '<thead><tr>';
                html += '<th>ID</th>';
                html += '<th>Người dùng</th>';
                html += '<th>Số tiền nhận</th>';
                html += '<th>Thời gian sử dụng</th>';
                html += '</tr></thead>';
                html += '<tbody>';
                
                if (data.usages && data.usages.data && data.usages.data.length > 0) {
                    data.usages.data.forEach(usage => {
                        html += '<tr>';
                        html += `<td>${usage.id}</td>`;
                        html += `<td>`;
                        html += `${usage.user?.display_name || usage.user?.name || usage.user?.email || 'N/A'}`;
                        if (usage.user?.email) {
                            html += `<br><small class="text-muted">${usage.user.email}</small>`;
                        }
                        html += `</td>`;
                        html += `<td><span class="text-success font-weight-bold">+${parseFloat(usage.amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}$</span></td>`;
                        html += `<td>${usage.created_at_formatted}<br><small class="text-muted">${usage.created_at_human}</small></td>`;
                        html += '</tr>';
                    });
                } else {
                    html += '<tr><td colspan="4" class="text-center text-muted"><i class="fas fa-inbox"></i> Chưa có lịch sử sử dụng</td></tr>';
                }
                
                html += '</tbody></table></div>';
                
                // Pagination
                if (data.usages && data.usages.last_page > 1) {
                    html += '<div class="mt-3 d-flex justify-content-center">';
                    html += '<nav><ul class="pagination">';
                    
                    // Previous button
                    if (data.usages.current_page > 1) {
                        html += `<li class="page-item"><a class="page-link" href="#" onclick="loadGiftcodeHistory(${giftcodeId}, ${data.usages.current_page - 1}); return false;">Trước</a></li>`;
                    } else {
                        html += '<li class="page-item disabled"><span class="page-link">Trước</span></li>';
                    }
                    
                    // Page numbers
                    for (let i = 1; i <= data.usages.last_page; i++) {
                        if (i === data.usages.current_page) {
                            html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
                        } else if (i === 1 || i === data.usages.last_page || (i >= data.usages.current_page - 2 && i <= data.usages.current_page + 2)) {
                            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadGiftcodeHistory(${giftcodeId}, ${i}); return false;">${i}</a></li>`;
                        } else if (i === data.usages.current_page - 3 || i === data.usages.current_page + 3) {
                            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                    }
                    
                    // Next button
                    if (data.usages.current_page < data.usages.last_page) {
                        html += `<li class="page-item"><a class="page-link" href="#" onclick="loadGiftcodeHistory(${giftcodeId}, ${data.usages.current_page + 1}); return false;">Sau</a></li>`;
                    } else {
                        html += '<li class="page-item disabled"><span class="page-link">Sau</span></li>';
                    }
                    
                    html += '</ul></nav></div>';
                    html += `<div class="text-center text-muted mt-2">Hiển thị ${data.usages.from || 0}-${data.usages.to || 0} trong tổng số ${data.usages.total || 0} bản ghi</div>`;
                } else if (data.usages && data.usages.total > 0) {
                    html += `<div class="text-center text-muted mt-2">Tổng số: ${data.usages.total} bản ghi</div>`;
                }
                
                contentDiv.innerHTML = html;
            } else {
                contentDiv.innerHTML = '<div class="alert alert-danger">Không thể tải lịch sử sử dụng giftcode.</div>';
            }
        })
        .catch(error => {
            contentDiv.innerHTML = '<div class="alert alert-danger">Có lỗi xảy ra khi tải dữ liệu.</div>';
        });
    }
</script>
@stop
