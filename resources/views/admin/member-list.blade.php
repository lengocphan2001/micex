@extends('adminlte::page')

@section('title', 'Danh sách thành viên - Micex Admin')

@section('content_header')
    <h1>Danh sách thành viên</h1>
@stop

@section('content')
    <!-- Search Section -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.member-list') }}" class="d-flex align-items-end gap-2">
                <div class="form-group mb-0 flex-grow-1">
                    <label for="search">Tìm kiếm (Username hoặc Mã giới thiệu)</label>
                    <input 
                        type="text" 
                        id="search" 
                        name="search" 
                        class="form-control" 
                        placeholder="Nhập username hoặc mã giới thiệu"
                        value="{{ request('search') }}"
                    >
                </div>
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                </div>
                @if(request('search'))
                <div class="form-group mb-0">
                    <a href="{{ route('admin.member-list') }}" class="btn btn-secondary">Xóa bộ lọc</a>
                </div>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Số điện thoại</th>
                        <th>Tên hiển thị</th>
                        <th>Mã giới thiệu</th>
                        <th>Ngày đăng ký</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->phone_number }}</td>
                            <td>{{ $user->display_name }}</td>
                            <td>{{ $user->referral_code }}</td>
                            <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.member.detail', $user->id) }}" class="btn btn-sm btn-info">Xem</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Không có thành viên nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div class="mt-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>
@stop

