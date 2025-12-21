@extends('adminlte::page')

@section('title', 'Cài đặt hệ thống - Micex Admin')

@section('content_header')
    <h1>Cài đặt hệ thống</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Tỉ lệ quy đổi VND / Đá quý</h3>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="vnd_to_gem_rate">Tỉ lệ quy đổi (VND / Đá quý)</label>
                    <input 
                        type="number" 
                        id="vnd_to_gem_rate" 
                        name="vnd_to_gem_rate" 
                        class="form-control @error('vnd_to_gem_rate') is-invalid @enderror" 
                        value="{{ old('vnd_to_gem_rate', $vndToGemRate) }}"
                        min="1"
                        step="1"
                        required
                    >
                    <small class="form-text text-muted">
                        Ví dụ: Nếu nhập 1000, nghĩa là 1000 VND = 1 đá quý
                    </small>
                    @error('vnd_to_gem_rate')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Lưu cài đặt</button>
            </form>
        </div>
    </div>

    <!-- Commission Rates Management -->
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Tỉ lệ hoa hồng Network (F1, F2, F3...)</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.settings.commission-rates.update') }}" method="POST" id="commissionRatesForm">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Hệ (Level)</th>
                                <th>Tỉ lệ (%)</th>
                                <th>Thứ tự</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="commissionRatesTableBody">
                            @foreach($commissionRates as $rate)
                            <tr data-rate-id="{{ $rate->id }}">
                                <td>
                                    <input type="text" name="rates[{{ $rate->id }}][level]" value="{{ $rate->level }}" class="form-control" readonly>
                                </td>
                                <td>
                                    <input type="number" name="rates[{{ $rate->id }}][rate]" value="{{ $rate->rate }}" class="form-control" min="0" max="100" step="0.01" required>
                                </td>
                                <td>
                                    <input type="number" name="rates[{{ $rate->id }}][order]" value="{{ $rate->order }}" class="form-control" min="0" required>
                                </td>
                                <td>
                                    <div class="form-check">
                                        <input type="checkbox" name="rates[{{ $rate->id }}][is_active]" value="1" class="form-check-input" {{ $rate->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label">Kích hoạt</label>
                                    </div>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteRate({{ $rate->id }})">Xóa</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn btn-primary">Lưu tỉ lệ hoa hồng</button>
            </form>

            <!-- Add New Rate Form -->
            <div class="mt-4">
                <h4>Thêm hệ mới</h4>
                <form action="{{ route('admin.settings.commission-rates.add') }}" method="POST" class="form-inline">
                    @csrf
                    <div class="form-group mr-2">
                        <label for="new_level" class="mr-2">Hệ:</label>
                        <input type="text" name="level" id="new_level" class="form-control" placeholder="F1, F2, F3..." required maxlength="10">
                    </div>
                    <div class="form-group mr-2">
                        <label for="new_rate" class="mr-2">Tỉ lệ (%):</label>
                        <input type="number" name="rate" id="new_rate" class="form-control" min="0" max="100" step="0.01" required>
                    </div>
                    <div class="form-group mr-2">
                        <label for="new_order" class="mr-2">Thứ tự:</label>
                        <input type="number" name="order" id="new_order" class="form-control" min="0" value="0">
                    </div>
                    <button type="submit" class="btn btn-success">Thêm</button>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    function deleteRate(id) {
        if (confirm('Bạn có chắc muốn xóa tỉ lệ hoa hồng này?')) {
            fetch(`{{ url('admin/settings/commission-rates') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Có lỗi xảy ra: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                alert('Có lỗi xảy ra khi xóa');
            });
        }
    }
</script>
@stop

