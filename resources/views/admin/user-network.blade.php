@extends('adminlte::page')

@section('title', 'Network - Micex Admin')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Network của {{ $user->display_name }} ({{ $user->referral_code }})</h1>
        <div>
            <a href="{{ route('admin.member.detail', $user->id) }}" class="btn btn-secondary">Quay lại</a>
        </div>
    </div>
@stop

@section('content')
    <!-- Network Statistics -->
    <div class="card mb-4">
        <div class="card-header bg-info">
            <h3 class="card-title">Thống kê Network</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Phân bổ theo cấp độ</h5>
                    <table class="table table-bordered">
                        @foreach($networkStats['level_counts'] as $level => $count)
                            <tr>
                                <th width="50%">{{ $level }}</th>
                                <td><strong>{{ number_format($count) }} người</strong></td>
                            </tr>
                        @endforeach
                        <tr class="bg-light">
                            <th>Tổng cấp dưới</th>
                            <td><strong class="text-primary">{{ number_format($networkStats['total_subordinates']) }} người</strong></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>Tổng nạp/rút Network</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th width="50%">Tổng nạp</th>
                            <td><strong class="text-success">{{ number_format($networkStats['total_deposit'], 2) }} đá quý</strong></td>
                        </tr>
                        <tr>
                            <th>Tổng rút</th>
                            <td><strong class="text-danger">{{ number_format($networkStats['total_withdraw'], 2) }} đá quý</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Network Tree -->
    <div class="card">
        <div class="card-header bg-success">
            <h3 class="card-title">Cây Network</h3>
        </div>
        <div class="card-body">
            <div class="network-tree-container" style="overflow-x: auto;">
                @if($networkTree)
                    <ul class="network-tree">
                        @include('admin.partials.network-node', ['node' => $networkTree])
                    </ul>
                @else
                    <p class="text-center text-muted">Không có cấp dưới nào</p>
                @endif
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .network-tree {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .network-tree ul {
            list-style: none;
            padding-left: 30px;
            margin: 0;
            position: relative;
        }
        
        .network-tree ul::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #ddd;
        }
        
        .network-tree li {
            position: relative;
            margin: 10px 0;
        }
        
        .network-tree li::before {
            content: '';
            position: absolute;
            left: -15px;
            top: 20px;
            width: 15px;
            height: 2px;
            background: #ddd;
        }
        
        .network-node {
            display: inline-block;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px 15px;
            min-width: 250px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .network-node:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        
        .network-node.level-0 {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
        }
        
        .network-node.level-1 {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-color: #f5576c;
        }
        
        .network-node.level-2 {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border-color: #4facfe;
        }
        
        .network-node.level-3 {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
            border-color: #43e97b;
        }
        
        .network-node.level-4 {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
            border-color: #fa709a;
        }
        
        .network-node.level-5 {
            background: #e9ecef;
            color: #333;
            border-color: #ced4da;
        }
        
        .node-header {
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .node-info {
            font-size: 12px;
            margin: 3px 0;
        }
        
        .node-stats {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid rgba(255,255,255,0.3);
            font-size: 11px;
        }
        
        .network-node.level-5 .node-stats {
            border-top-color: #dee2e6;
        }
        
        .toggle-children {
            cursor: pointer;
            color: rgba(255,255,255,0.8);
            font-size: 12px;
            margin-left: 5px;
        }
        
        .network-node.level-5 .toggle-children {
            color: #666;
        }
        
        .network-node.level-5 a.btn {
            background: #6c757d !important;
            color: white !important;
            border-color: #6c757d !important;
        }
        
        .children-hidden {
            display: none;
        }
        
        @media (max-width: 768px) {
            .network-node {
                min-width: 200px;
                padding: 8px 12px;
            }
            
            .node-header {
                font-size: 12px;
            }
            
            .node-info {
                font-size: 11px;
            }
        }
    </style>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle children visibility
            document.querySelectorAll('.toggle-children').forEach(function(toggle) {
                toggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const node = this.closest('li');
                    const children = node.querySelector('> ul');
                    if (children) {
                        if (children.classList.contains('children-hidden')) {
                            children.classList.remove('children-hidden');
                            this.textContent = '[-]';
                        } else {
                            children.classList.add('children-hidden');
                            this.textContent = '[+]';
                        }
                    }
                });
            });
        });
    </script>
@stop

