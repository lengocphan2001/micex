@extends('adminlte::page')

@section('title', 'Quản lý tài khoản Marketing - Micex Admin')

@section('content_header')
    <h1>Quản lý tài khoản Marketing</h1>
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

    <!-- Create Marketing Account Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">TẠO TÀI KHOẢN MARKETING</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.marketing-accounts.create') }}" method="POST" class="d-flex align-items-end gap-2">
                @csrf
                <div class="form-group mb-0 flex-grow-1">
                    <label for="email">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control @error('email') is-invalid @enderror" 
                        placeholder="Nhập gmail" 
                        value="{{ old('email') }}"
                        required
                    >
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group mb-0 flex-grow-1">
                    <label for="password">Mật khẩu</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control @error('password') is-invalid @enderror" 
                        placeholder="Mật khẩu" 
                        required
                    >
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">Xác nhận</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Marketing Accounts List -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Danh sách tài khoản marketing</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Số dư</th>
                            <th>Cộng thêm tiền</th>
                            <th>Trừ tiền</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($marketingAccounts as $account)
                            <tr data-account-id="{{ $account->id }}">
                                <td>{{ $account->name ?? explode('@', $account->email)[0] }}</td>
                                <td class="balance-cell">{{ number_format($account->balance, 2) }}$</td>
                                <td>
                                    <form action="{{ route('admin.marketing-accounts.update-balance', $account->id) }}" method="POST" class="d-flex gap-2">
                                        @csrf
                                        <input type="hidden" name="type" value="add">
                                        <input 
                                            type="number" 
                                            name="amount" 
                                            class="form-control form-control-sm" 
                                            placeholder="Số lượng" 
                                            step="0.01" 
                                            min="0.01"
                                            required
                                            style="width: 120px;"
                                        >
                                        <button type="submit" class="btn btn-sm btn-primary">Xác nhận</button>
                                    </form>
                                </td>
                                <td>
                                    <form action="{{ route('admin.marketing-accounts.update-balance', $account->id) }}" method="POST" class="d-flex gap-2">
                                        @csrf
                                        <input type="hidden" name="type" value="subtract">
                                        <input 
                                            type="number" 
                                            name="amount" 
                                            class="form-control form-control-sm" 
                                            placeholder="Số lượng" 
                                            step="0.01" 
                                            min="0.01"
                                            required
                                            style="width: 120px;"
                                        >
                                        <button type="submit" class="btn btn-sm btn-primary">Xác nhận</button>
                                    </form>
                                </td>
                                <td>
                                    <form action="{{ route('admin.marketing-accounts.delete', $account->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa tài khoản này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Huỷ tài khoản này</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Chưa có tài khoản marketing nào</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .form-group {
        margin-bottom: 0;
    }
    .table td {
        vertical-align: middle;
    }
    .balance-cell {
        font-weight: bold;
        color: #28a745;
    }
</style>
@stop

