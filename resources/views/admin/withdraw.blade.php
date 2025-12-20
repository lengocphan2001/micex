@extends('adminlte::page')

@section('title', 'Quản lý rút tiền - Micex Admin')

@section('content_header')
    <h1>Quản lý rút tiền</h1>
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

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Danh sách yêu cầu rút tiền</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Số đá quý</th>
                            <th>Số tiền (VND)</th>
                            <th>Thông tin ngân hàng</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th>Người duyệt</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($withdrawRequests as $request)
                            <tr>
                                <td>{{ $request->id }}</td>
                                <td>
                                    {{ $request->user->display_name ?? $request->user->name ?? $request->user->email }}
                                    <br>
                                    <small class="text-muted">{{ $request->user->email }}</small>
                                </td>
                                <td>{{ number_format($request->gem_amount, 2, ',', '.') }}</td>
                                <td>{{ number_format($request->amount, 0, ',', '.') }} VND</td>
                                <td>
                                    <div><strong>{{ $request->bank_name }}</strong></div>
                                    <div><small>{{ $request->bank_full_name }}</small></div>
                                    <div><code>{{ $request->bank_account }}</code></div>
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
                                        <form action="{{ route('admin.withdraw.approve', $request->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <div class="input-group input-group-sm mb-2">
                                                <input type="text" name="notes" class="form-control" placeholder="Ghi chú (tùy chọn)">
                                            </div>
                                            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Bạn có chắc chắn muốn duyệt yêu cầu này? Số dư của user sẽ bị trừ tự động.')">
                                                <i class="fa fa-check"></i> Duyệt
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.withdraw.reject', $request->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <div class="input-group input-group-sm mb-2">
                                                <input type="text" name="notes" class="form-control" placeholder="Lý do từ chối">
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
                                <td colspan="9" class="text-center">Chưa có yêu cầu rút tiền nào</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($withdrawRequests->hasPages())
            <div class="mt-3">
                {{ $withdrawRequests->links() }}
            </div>
            @endif
        </div>
    </div>
@stop
