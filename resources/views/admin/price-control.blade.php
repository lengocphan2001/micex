@extends('adminlte::page')

@section('title', 'Điều khiển giá - Micex Admin')

@section('content_header')
    <h1>Điều khiển giá Trading</h1>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade in">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
        @foreach($symbols as $symbol)
            @php
                $control = $controls[$symbol];
            @endphp
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-coins"></i> {{ $symbol }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.price-control.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="symbol" value="{{ $symbol }}">
                            
                            <div class="form-group">
                                <label>Mode:</label>
                                <select name="mode" class="form-control">
                                    <option value="normal" {{ $control->mode === 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="up" {{ $control->mode === 'up' ? 'selected' : '' }}>Up (Ép tăng - Short chết)</option>
                                    <option value="down" {{ $control->mode === 'down' ? 'selected' : '' }}>Down (Ép giảm - Long chết)</option>
                                    <option value="trap" {{ $control->mode === 'trap' ? 'selected' : '' }}>Trap (Bẫy)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Strength (1-10):</label>
                                <input type="number" name="strength" class="form-control" 
                                       value="{{ $control->strength }}" min="1" max="10" required>
                                <small class="form-text text-muted">Cường độ can thiệp vào giá</small>
                            </div>
                            
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" name="enabled" class="form-check-input" 
                                           id="enabled_{{ $symbol }}" {{ $control->enabled ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enabled_{{ $symbol }}">
                                        Enable Price Control
                                    </label>
                                </div>
                            </div>
                            
                            <hr>
                            <h5 class="mt-3 mb-2">⚙️ Ép Nến (Bias Control)</h5>
                            
                            <div class="form-group">
                                <label>Hướng ép:</label>
                                <select name="bias_dir" class="form-control">
                                    <option value="0" {{ $control->bias_dir == 0 ? 'selected' : '' }}>Tự nhiên</option>
                                    <option value="1" {{ $control->bias_dir == 1 ? 'selected' : '' }}>Ép lên (CALL thắng)</option>
                                    <option value="-1" {{ $control->bias_dir == -1 ? 'selected' : '' }}>Ép xuống (PUT thắng)</option>
                                </select>
                                <small class="form-text text-muted">Hướng ép giá trong giây cuối</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Giây cuối:</label>
                                <input type="number" name="last_seconds" class="form-control" 
                                       value="{{ $control->last_seconds ?? 10 }}" min="1" max="60" required>
                                <small class="form-text text-muted">Số giây cuối để ép giá (1-60)</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Độ lệch giá:</label>
                                <input type="number" name="bias_power" class="form-control" 
                                       value="{{ $control->bias_power ?? 10 }}" min="0" max="100" required>
                                <small class="form-text text-muted">Độ lệch giá để ép (0-100)</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Cập nhật
                            </button>
                        </form>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <strong>Lưu ý:</strong><br>
                                • <strong>Mode:</strong> Điều khiển tổng thể giá (Up/Down/Trap)<br>
                                • <strong>Ép Nến:</strong> Trong giây cuối, giá sẽ được ép về target = open + (bias_dir × bias_power)<br>
                                • <strong>Ví dụ:</strong> bias_dir=1, bias_power=10 → ép giá lên 10 điểm trong giây cuối<br>
                                • Logic này giống Binary Options engine - ép chắc chắn trong giây cuối
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@stop
