@extends('adminlte::page')

@section('title', 'Chỉnh sửa Slider - Micex Admin')

@section('content_header')
    <h1>Chỉnh sửa Slider</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.sliders.update', $slider->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="title">Tiêu đề <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $slider->title) }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Mô tả</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $slider->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="button_title">Button Title</label>
                    <input type="text" class="form-control @error('button_title') is-invalid @enderror" id="button_title" name="button_title" value="{{ old('button_title', $slider->button_title) }}">
                    @error('button_title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="image">Ảnh</label>
                    @if($slider->image)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $slider->image) }}" alt="{{ $slider->title }}" style="max-width: 300px; max-height: 200px; object-fit: cover;" class="img-thumbnail">
                        </div>
                    @endif
                    <input type="file" class="form-control-file @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                    @error('image')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Để trống nếu không muốn thay đổi ảnh. Định dạng: JPEG, PNG, JPG, GIF. Tối đa 10MB</small>
                </div>

                <div class="form-group">
                    <label for="order">Thứ tự hiển thị</label>
                    <input type="number" class="form-control @error('order') is-invalid @enderror" id="order" name="order" value="{{ old('order', $slider->order) }}" min="0">
                    @error('order')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Số nhỏ hơn sẽ hiển thị trước</small>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $slider->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Kích hoạt
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Cập nhật
                    </button>
                    <a href="{{ route('admin.sliders.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
@stop

