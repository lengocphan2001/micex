@extends('adminlte::page')

@section('title', 'Chi tiết quỹ - Micex Admin')

@section('content_header')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <h1 class="mb-0">Thống kê chi tiết</h1>
    </div>
@stop

@section('content')
    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-4" id="fundDetailTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="summary-tab" data-toggle="tab" href="#summary" role="tab" aria-controls="summary" aria-selected="true">
                Tổng quan
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="detailed-tab" data-toggle="tab" href="#detailed" role="tab" aria-controls="detailed" aria-selected="false">
                Chi tiết
            </a>
        </li>
    </ul>

    <!-- Tabs Content -->
    <div class="tab-content" id="fundDetailTabContent">
        <!-- Tab 1: Tổng quan (existing content) -->
        <div class="tab-pane fade show active" id="summary" role="tabpanel" aria-labelledby="summary-tab">
    <!-- Header Metrics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none;">
                <div class="card-body">
                    <div class="row text-white">
                        <div class="col-md-3 col-6 mb-3 mb-md-0">
                            <div class="d-flex flex-column">
                                <small class="text-white-50 mb-1">Số vốn quỹ của hệ thống</small>
                                <h4 class="mb-0 font-weight-bold">{{ number_format($initialFund, 2) }}$</h4>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3 mb-md-0">
                            <div class="d-flex flex-column">
                                <small class="text-white-50 mb-1">Số tiền trong quỹ hiện tại</small>
                                <h4 class="mb-0 font-weight-bold">{{ number_format($currentFund, 2) }}$</h4>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="d-flex flex-column">
                                <small class="text-white-50 mb-1">Lợi nhuận</small>
                                <h4 class="mb-0 font-weight-bold {{ $profit < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $profit >= 0 ? '+' : '' }}{{ number_format($profit, 2) }}$ ~ ({{ $profitPercent >= 0 ? '+' : '' }}{{ number_format($profitPercent, 2) }}%)
                                </h4>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="d-flex flex-column">
                                <small class="text-white-50 mb-1">Trạng thái</small>
                                <h4 class="mb-0 font-weight-bold {{ $status === 'Tốt' ? 'text-success' : ($status === 'Cảnh báo' ? 'text-warning' : 'text-danger') }}">
                                    {{ $status }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Three Main Panels -->
    <div class="row mb-4">
        <!-- Panel 1: Số tiền sàn chi ra trong ngày -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0 font-weight-bold">Số tiền sàn chi ra trong ngày</h5>
                </div>
                <div class="card-body">
                    <h3 class="text-danger mb-3">-{{ number_format($totalExpensesToday, 2) }}</h3>
                    <table class="table table-sm table-bordered mb-0">
                        <tbody>
                            <tr>
                                <td>Tổng khách hàng mở lì xì</td>
                                <td class="text-right text-danger">-{{ number_format($luckyMoneyTotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Tổng khách hàng nhận giftcode</td>
                                <td class="text-right text-danger">-{{ number_format($giftcodeTotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Tổng số tiền khuyến mãi</td>
                                <td class="text-right text-danger">-{{ number_format($promotionTotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Hoa hồng chỉ trả cho đại lý</td>
                                <td class="text-right text-danger">-{{ number_format($commissionPaidToday, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Panel 2: Tiền thưởng -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0 font-weight-bold">Tiền thưởng</h5>
                </div>
                <div class="card-body">
                    <h3 class="mb-3">{{ number_format($customersWon, 2) }}</h3>
                    <table class="table table-sm table-bordered mb-0">
                        <tbody>
                            <tr>
                                <td>Khách hàng thua</td>
                                <td class="text-right">{{ number_format($customersLost, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Khách hàng thắng</td>
                                <td class="text-right">{{ number_format($customersWon, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Kết quả</td>
                                <td class="text-right {{ $rewardResult >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $rewardResult >= 0 ? '+' : '' }}{{ number_format($rewardResult, 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Panel 3: Hoạt động cược -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0 font-weight-bold">Hoạt động cược</h5>
                </div>
                <div class="card-body">
                    <h3 class="mb-3">{{ number_format($totalBetsToday, 2) }}</h3>
                    <table class="table table-sm table-bordered mb-0">
                        <tbody>
                            <tr>
                                <td>Tổng tiền khách cược</td>
                                <td class="text-right">{{ number_format($totalBetsToday, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Sàn trả thưởng</td>
                                <td class="text-right text-danger">-{{ number_format($totalPayoutToday, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Sàn lời từ cược</td>
                                <td class="text-right {{ $profitFromBets >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $profitFromBets >= 0 ? '+' : '' }}{{ number_format($profitFromBets, 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Two Lower Panels -->
    <div class="row mb-4">
        <!-- Panel 1: User rút tiền cần chú ý -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0 font-weight-bold">User rút tiền cần chú ý</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th class="text-right">Số tiền rút</th>
                                    <th class="text-right">Số lần rút</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($usersWithdrawals as $user)
                                    <tr>
                                        <td>{{ $user['username'] }}</td>
                                        <td class="text-right">{{ number_format($user['total_amount'], 2) }}$</td>
                                        <td class="text-right">{{ $user['withdrawal_count'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Không có dữ liệu</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel 2: Tổng cược từ tiền nạp -->
        <div class="col-lg-6 mb-4">
            <div class="card" style="background-color: #fff3cd;">
                <div class="card-header" style="background-color: #ffc107;">
                    <h5 class="mb-0 font-weight-bold">Tổng cược từ tiền nạp</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-bordered mb-0">
                        <tbody>
                            <tr>
                                <td>Tổng số nạp</td>
                                <td class="text-right font-weight-bold">{{ number_format($totalDeposits, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Số tiền cược từ tiền nạp</td>
                                <td class="text-right font-weight-bold">{{ number_format($totalBetsFromDeposits, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Khách hàng thắng</td>
                                <td class="text-right">{{ number_format($winsFromDeposits, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Khách hàng thua</td>
                                <td class="text-right">{{ number_format($lossesFromDeposits, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Hệ thống lời / Lỗ</td>
                                <td class="text-right font-weight-bold {{ $systemProfitFromDeposits >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $systemProfitFromDeposits >= 0 ? '+' : '' }}{{ number_format($systemProfitFromDeposits, 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Cards: Tổng nạp và Tổng rút -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card" style="background-color: #d4edda; border-color: #c3e6cb;">
                <div class="card-body text-center">
                    <h5 class="card-title mb-3">Tổng nạp</h5>
                    <h2 class="mb-0 font-weight-bold text-success">{{ number_format($totalDepositAll, 2) }} USDT</h2>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card" style="background-color: #f8d7da; border-color: #f5c6cb;">
                <div class="card-body text-center">
                    <h5 class="card-title mb-3">Tổng rút</h5>
                    <h2 class="mb-0 font-weight-bold text-danger">-{{ number_format($totalWithdrawAll, 2) }} USDT</h2>
                </div>
            </div>
        </div>
    </div>
        </div>

        <!-- Tab 2: Chi tiết (new detailed view) -->
        <div class="tab-pane fade" id="detailed" role="tabpanel" aria-labelledby="detailed-tab">
            @include('admin.fund-detail-detailed', [
                'initialFund' => $initialFund,
                'fundAtStartOfDay' => $fundAtStartOfDay ?? $initialFund,
                'currentFund' => $currentFund,
                'profit' => $profit,
                'profitPercent' => $profitPercent,
                'status' => $status,
                'totalDepositInflow' => $totalDepositInflow ?? 0,
                'profitFromBetsInflow' => $profitFromBetsInflow ?? 0,
                'totalInflow' => $totalInflow ?? 0,
                'totalWithdrawOutflow' => $totalWithdrawOutflow ?? 0,
                'promotionOutflow' => $promotionOutflow ?? 0,
                'commissionOutflow' => $commissionOutflow ?? 0,
                'totalOutflow' => $totalOutflow ?? 0,
                'totalBetsAllTime' => $totalBetsAllTime ?? 0,
                'totalPayoutAllTime' => $totalPayoutAllTime ?? 0,
                'totalProfitFromBetsAllTime' => $totalProfitFromBetsAllTime ?? 0,
                'topProfitableUsers' => $topProfitableUsers ?? collect(),
                'totalDeposits' => $totalDeposits ?? 0,
                'totalBetsFromDeposits' => $totalBetsFromDeposits ?? 0,
                'winsFromDeposits' => $winsFromDeposits ?? 0,
                'lossesFromDeposits' => $lossesFromDeposits ?? 0,
                'usersWithdrawals' => $usersWithdrawals ?? collect(),
                'totalRewardGiven' => $totalRewardGiven ?? 0,
                'betsFromReward' => $betsFromReward ?? 0,
                'payoutFromReward' => $payoutFromReward ?? 0,
                'profitFromReward' => $profitFromReward ?? 0,
            ])
        </div>
    </div>
@stop
