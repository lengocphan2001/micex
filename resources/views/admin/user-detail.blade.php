@extends('adminlte::page')

@section('title', 'Chi tiết thành viên - Micex Admin')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Chi tiết thành viên</h1>
        <a href="{{ route('admin.member-list') }}" class="btn btn-secondary">Quay lại</a>
    </div>
@stop

@section('content')
    <!-- User Information Card -->
    <div class="card mb-4">
        <div class="card-header bg-primary">
            <h3 class="card-title">Thông tin thành viên</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">ID</th>
                            <td>{{ $user->id }}</td>
                        </tr>
                        <tr>
                            <th>Số điện thoại</th>
                            <td>{{ $user->phone_number }}</td>
                        </tr>
                        <tr>
                            <th>Tên hiển thị</th>
                            <td>{{ $user->display_name }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>Mã giới thiệu</th>
                            <td>{{ $user->referral_code }}</td>
                        </tr>
                        <tr>
                            <th>Mã chuyển khoản</th>
                            <td>{{ $user->transfer_code }}</td>
                        </tr>
                        <tr>
                            <th>Số dư</th>
                            <td><strong>{{ number_format($user->balance, 2) }} đá quý</strong></td>
                        </tr>
                        <tr>
                            <th>Người giới thiệu</th>
                            <td>
                                @if($user->referrer)
                                    <a href="{{ route('admin.member.detail', $user->referrer->id) }}">
                                        {{ $user->referrer->display_name }} ({{ $user->referrer->referral_code }})
                                    </a>
                                @else
                                    <span class="text-muted">Không có</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Ngày đăng ký</th>
                            <td>{{ $user->created_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>Thống kê nạp/rút</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th width="50%">Tổng nạp</th>
                            <td><strong class="text-success">{{ number_format($userTotalDeposit, 2) }} đá quý</strong></td>
                        </tr>
                        <tr>
                            <th>Tổng rút</th>
                            <td><strong class="text-danger">{{ number_format($userTotalWithdraw, 2) }} đá quý</strong></td>
                        </tr>
                        <tr>
                            <th>Tổng nạp cấp dưới</th>
                            <td><strong class="text-info">{{ number_format($subordinatesTotalDeposit, 2) }} đá quý</strong></td>
                        </tr>
                        <tr>
                            <th>Tổng rút cấp dưới</th>
                            <td><strong class="text-warning">{{ number_format($subordinatesTotalWithdraw, 2) }} đá quý</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Section -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning">
                    <h3 class="card-title">Đổi mật khẩu đăng nhập</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.member.update-password', $user->id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="password">Mật khẩu mới</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" required minlength="6">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation">Xác nhận mật khẩu</label>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation" required minlength="6">
                        </div>
                        <button type="submit" class="btn btn-warning">Cập nhật mật khẩu đăng nhập</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info">
                    <h3 class="card-title">Đổi mật khẩu quỹ</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.member.update-fund-password', $user->id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="fund_password">Mật khẩu quỹ mới</label>
                            <input type="password" class="form-control @error('fund_password') is-invalid @enderror" 
                                   id="fund_password" name="fund_password" required minlength="6">
                            @error('fund_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="fund_password_confirmation">Xác nhận mật khẩu quỹ</label>
                            <input type="password" class="form-control" 
                                   id="fund_password_confirmation" name="fund_password_confirmation" required minlength="6">
                        </div>
                        <button type="submit" class="btn btn-info">Cập nhật mật khẩu quỹ</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Network Link -->
    <div class="card mt-4">
        <div class="card-header bg-info">
            <h3 class="card-title">Network</h3>
        </div>
        <div class="card-body">
            <a href="{{ route('admin.member.network', $user->id) }}" class="btn btn-info btn-lg">
                <i class="fas fa-sitemap"></i> Xem cây Network
            </a>
        </div>
    </div>

    <!-- Subordinates List -->
    <div class="card mt-4">
        <div class="card-header bg-success">
            <h3 class="card-title">Danh sách cấp dưới trực tiếp ({{ $subordinates->total() }})</h3>
        </div>
        <div class="card-body">
            @if($subordinates->count() > 0)
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Số điện thoại</th>
                            <th>Tên hiển thị</th>
                            <th>Mã giới thiệu</th>
                            <th>Số dư</th>
                            <th>Số cấp dưới</th>
                            <th>Tổng nạp</th>
                            <th>Tổng rút</th>
                            <th>Ngày đăng ký</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subordinates as $subordinate)
                            @php
                                $subDeposit = \App\Models\DepositRequest::where('user_id', $subordinate->id)
                                    ->where('status', 'approved')
                                    ->sum('gem_amount') ?? 0;
                                $subWithdraw = \App\Models\WithdrawRequest::where('user_id', $subordinate->id)
                                    ->where('status', 'approved')
                                    ->sum('gem_amount') ?? 0;
                            @endphp
                            <tr>
                                <td>{{ $subordinate->id }}</td>
                                <td>{{ $subordinate->phone_number }}</td>
                                <td>{{ $subordinate->display_name }}</td>
                                <td>{{ $subordinate->referral_code }}</td>
                                <td>{{ number_format($subordinate->balance, 2) }}</td>
                                <td>{{ $subordinate->referrals_count }}</td>
                                <td class="text-success">{{ number_format($subDeposit, 2) }}</td>
                                <td class="text-danger">{{ number_format($subWithdraw, 2) }}</td>
                                <td>{{ $subordinate->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.member.detail', $subordinate->id) }}" class="btn btn-sm btn-info">Xem</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <div class="mt-3">
                    {{ $subordinates->links() }}
                </div>
            @else
                <p class="text-center text-muted">Không có cấp dưới nào</p>
            @endif
        </div>
    </div>
@stop

@section('js')
    <script>
        @if(session('success'))
            toastr.success('{{ session('success') }}');
        @endif
        
        @if(session('error'))
            toastr.error('{{ session('error') }}');
        @endif
    </script>
@stop

