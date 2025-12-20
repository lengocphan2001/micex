@extends('adminlte::page')

@section('title', 'Quản lý nạp tiền - Micex Admin')

@section('content_header')
    <h1>Quản lý nạp tiền</h1>
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

    @if($activePromotion)
    <div class="alert alert-warning alert-dismissible fade in" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); border-color: #ff9800;">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <h4><i class="icon fa fa-gift"></i> <strong>KHUYẾN MÃI ĐANG DIỄN RA!</strong></h4>
        <p style="margin: 5px 0;">
            <strong>Khuyến mãi nạp: {{ number_format($activePromotion->deposit_percentage, 2) }}%</strong>
            @if($activePromotion->start_date && $activePromotion->end_date)
                <br>
                <small>Thời gian: {{ \Carbon\Carbon::parse($activePromotion->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($activePromotion->end_date)->format('d/m/Y') }}</small>
            @endif
        </p>
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Danh sách yêu cầu nạp tiền</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Số tiền (VND)</th>
                            <th>Số đá quý</th>
                            <th>Mã chuyển tiền</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th>Người duyệt</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($depositRequests as $request)
                            <tr>
                                <td>{{ $request->id }}</td>
                                <td>
                                    {{ $request->user->display_name ?? $request->user->name ?? $request->user->email }}
                                    <br>
                                    <small class="text-muted">{{ $request->user->email }}</small>
                                </td>
                                <td>{{ number_format($request->amount, 0, ',', '.') }} VND</td>
                                <td>{{ number_format($request->gem_amount ?? 0, 2, ',', '.') }}</td>
                                <td>
                                    <code>{{ $request->transfer_code }}</code>
                                </td>
                                <td>
                                    @if ($request->status === 'pending')
                                        <span class="badge badge-warning">Chờ duyệt</span>
                                    @elseif ($request->status === 'approved')
                                        <span class="badge badge-success">Đã duyệt</span>
                                    @else
                                        <span class="badge badge-danger">Đã từ chối</span>
                                    @endif
                                </td>
                                <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if ($request->approver)
                                        {{ $request->approver->name ?? $request->approver->email }}
                                        <br>
                                        <small class="text-muted">{{ $request->approved_at->format('d/m/Y H:i') }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($request->status === 'pending')
                                        <form action="{{ route('admin.deposit.approve', $request->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <div class="input-group input-group-sm mb-2">
                                                <input type="text" name="notes" class="form-control" placeholder="Ghi chú (tùy chọn)">
                                            </div>
                                            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Bạn có chắc chắn muốn duyệt yêu cầu này? Số dư của user sẽ được cập nhật tự động.')">
                                                <i class="fa fa-check"></i> Duyệt
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.deposit.reject', $request->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <div class="input-group input-group-sm mb-2">
                                                <input type="text" name="notes" class="form-control" placeholder="Lý do từ chối (tùy chọn)">
                                            </div>
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn từ chối yêu cầu này?')">
                                                <i class="fa fa-times"></i> Từ chối
                                            </button>
                                        </form>
                                    @else
                                        @if ($request->notes)
                                            <small class="text-muted">{{ $request->notes }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Không có yêu cầu nạp tiền nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $depositRequests->links() }}
            </div>
        </div>
    </div>
@stop
