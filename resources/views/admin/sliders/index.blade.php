@extends('adminlte::page')

@section('title', 'Quản lý Slider - Micex Admin')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Quản lý Slider</h1>
        <a href="{{ route('admin.sliders.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm Slider mới
        </a>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Tiêu đề</th>
                        <th>Mô tả</th>
                        <th>Button Title</th>
                        <th>Thứ tự</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sliders as $slider)
                        <tr>
                            <td>{{ $slider->id }}</td>
                            <td>
                                @if($slider->image)
                                    <img src="{{ asset('storage/' . $slider->image) }}" alt="{{ $slider->title }}" style="max-width: 100px; max-height: 60px; object-fit: cover;">
                                @else
                                    <span class="text-muted">Không có ảnh</span>
                                @endif
                            </td>
                            <td>{{ $slider->title }}</td>
                            <td>{{ Str::limit($slider->description, 50) }}</td>
                            <td>{{ $slider->button_title ?? '-' }}</td>
                            <td>{{ $slider->order }}</td>
                            <td>
                                @if($slider->is_active)
                                    <span class="badge badge-success">Hoạt động</span>
                                @else
                                    <span class="badge badge-secondary">Tạm dừng</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('admin.sliders.edit', $slider->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Sửa
                                    </a>
                                    <form action="{{ route('admin.sliders.destroy', $slider->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa slider này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Chưa có slider nào. <a href="{{ route('admin.sliders.create') }}">Tạo slider mới</a></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop

