@extends('adminlte::page')

@section('title', 'Thống kê - Micex Admin')

@section('content_header')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <h1 class="mb-0">Thống kê</h1>
        <div class="d-flex align-items-center gap-2">
            <input type="date" class="form-control" style="width: auto;" value="{{ date('Y-m-d') }}" id="datePicker">
        </div>
    </div>
@stop

@section('content')
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

    <div class="row">
        <!-- Top Statistics Cards -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($systemProfit, 2) }} <small>USDT</small></h3>
                    <p>Lợi nhuận hệ thống</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($totalDeposit, 2) }} <small>USDT</small></h3>
                    <p>Tổng nạp</p>
                </div>
                <div class="icon">
                    <i class="fas fa-arrow-down"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($totalWithdraw, 2) }} <small>USDT</small></h3>
                    <p>Tổng rút</p>
                </div>
                <div class="icon">
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ number_format($totalOnExchange, 2) }} <small>USDT</small></h3>
                    <p>Tổng tiền trên sàn</p>
                </div>
                <div class="icon">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Reward/Commission Cards -->
    <div class="row mt-4">
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card border-primary">
                <div class="card-body">
                    <h5 class="card-title text-primary">Hoa hồng chi trả</h5>
                    <h3 class="text-primary mb-0">{{ number_format($commissionPaid, 2) }} <small>USDT</small></h3>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card border-primary">
                <div class="card-body">
                    <h5 class="card-title text-primary">Tiền thưởng KM</h5>
                    <h3 class="text-primary mb-0">{{ number_format($promotionBonus, 2) }} <small>USDT</small></h3>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card border-primary">
                <div class="card-body">
                    <h5 class="card-title text-primary">Tiền thưởng nạp đầu</h5>
                    <h3 class="text-primary mb-0">{{ number_format($firstDepositBonus, 2) }} <small>USDT</small></h3>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card border-primary">
                <div class="card-body">
                    <h5 class="card-title text-primary">Tiền thưởng thủ công</h5>
                    <h3 class="text-primary mb-0">{{ number_format($manualBonus, 2) }} <small>USDT</small></h3>
                </div>
            </div>
        </div>
    </div>

@stop

@section('css')
    <style>
        .small-box {
            border-radius: 0.25rem;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            display: block;
            margin-bottom: 20px;
            position: relative;
        }
        .small-box > .inner {
            padding: 10px;
        }
        .small-box > .small-box-footer {
            background-color: rgba(0,0,0,.1);
            color: rgba(255,255,255,.8);
            display: block;
            padding: 3px 0;
            position: relative;
            text-align: center;
            text-decoration: none;
            z-index: 10;
        }
        .small-box h3 {
            font-size: 2.2rem;
            font-weight: bold;
            margin: 0 0 10px 0;
            white-space: nowrap;
            padding: 0;
        }
        .small-box p {
            font-size: 1rem;
        }
        .small-box .icon {
            color: rgba(0,0,0,.15);
            z-index: 0;
        }
        .small-box .icon > i {
            font-size: 70px;
            position: absolute;
            right: 15px;
            top: 15px;
            transition: transform .3s linear;
        }
        .card.border-primary {
            border-width: 2px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            h1 {
                font-size: 1.5rem;
            }
            .small-box h3 {
                font-size: 1.6rem;
            }
            .small-box p {
                font-size: 0.95rem;
            }
            .small-box .icon > i {
                font-size: 50px;
            }
            #datePicker {
                width: 140px !important;
            }
            .card.border-primary h3 {
                font-size: 1.3rem;
            }
            .card.border-primary h5 {
                font-size: 0.95rem;
            }
        }
        
        @media (max-width: 576px) {
            .small-box h3 {
                font-size: 1.4rem;
            }
            .small-box h3 small {
                font-size: 0.7em;
            }
            .card.border-primary h3 {
                font-size: 1.1rem;
            }
            .card.border-primary h5 {
                font-size: 0.85rem;
            }
        }
    </style>
@stop

@section('js')
    <script>
        // Date picker functionality
        document.getElementById('datePicker').addEventListener('change', function() {
            // Handle date change - can be implemented to filter data
        });
    </script>
@stop

