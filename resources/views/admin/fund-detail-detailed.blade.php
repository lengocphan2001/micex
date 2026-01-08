<!-- Header Metrics -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none;">
            <div class="card-body">
                <div class="row text-white">
                    <div class="col-md-3 col-6 mb-3 mb-md-0">
                        <div class="d-flex flex-column">
                            <small class="text-white-50 mb-1">Số tiền trong quỹ lúc 00:00 UTC</small>
                            <h4 class="mb-0 font-weight-bold">{{ number_format($fundAtStartOfDay ?? $initialFund, 2) }}$</h4>
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
                            <small class="text-white-50 mb-1">Hôm nay lỗ</small>
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

<div class="row">
    <!-- Left Column -->
    <div class="col-lg-6">
        <!-- Dòng tiền vào -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0 font-weight-bold">Dòng tiền vào</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm table-bordered mb-0">
                    <tbody>
                        <tr>
                            <td>Tổng số tiền nạp</td>
                            <td class="text-right text-success font-weight-bold">+{{ number_format($totalDepositInflow, 2) }}$</td>
                        </tr>
                        <tr>
                            <td>Sàn lời từ cược</td>
                            <td class="text-right text-success font-weight-bold">+{{ number_format($profitFromBetsInflow, 2) }}$</td>
                        </tr>
                        <tr class="bg-light">
                            <td class="font-weight-bold">Tổng tiền vào</td>
                            <td class="text-right text-success font-weight-bold">+{{ number_format($totalInflow, 2) }}$</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tiền ra trong ngày -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0 font-weight-bold">Tiền ra trong ngày</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm table-bordered mb-0">
                    <tbody>
                        <tr>
                            <td>Tổng số tiền rút</td>
                            <td class="text-right text-danger">-{{ number_format($totalWithdrawOutflow, 2) }}$</td>
                        </tr>
                        <tr>
                            <td>Thưởng khuyến mãi</td>
                            <td class="text-right text-danger">-{{ number_format($promotionOutflow, 2) }}$</td>
                        </tr>
                        <tr>
                            <td>Hoa hồng</td>
                            <td class="text-right text-danger">-{{ number_format($commissionOutflow, 2) }}$</td>
                        </tr>
                        <tr class="bg-light">
                            <td class="font-weight-bold">Tổng tiền ra</td>
                            <td class="text-right text-danger font-weight-bold">-{{ number_format($totalOutflow, 2) }}$</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Thống kê tiền cược -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0 font-weight-bold">Thống kê tiền cược</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm table-bordered mb-0">
                    <tbody>
                        <tr>
                            <td>Tổng tiền user đã cược</td>
                            <td class="text-right">{{ number_format($totalBetsAllTime, 2) }}$</td>
                        </tr>
                        <tr>
                            <td>Tổng tiền trả thưởng</td>
                            <td class="text-right text-danger">-{{ number_format($totalPayoutAllTime, 2) }}$</td>
                        </tr>
                        <tr class="bg-light">
                            <td class="font-weight-bold">Tổng sàn lời từ cược</td>
                            <td class="text-right text-success font-weight-bold">+{{ number_format($totalProfitFromBetsAllTime, 2) }}$</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TOP user lãi nhiều nhất -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0 font-weight-bold">TOP user lãi nhiều nhất</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th class="text-right">Lãi từ tiền thường</th>
                                <th class="text-right">Lãi từ tiền nạp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProfitableUsers as $user)
                                <tr>
                                    <td>{{ $user['username'] }}</td>
                                    <td class="text-right">{{ number_format($user['profit_from_reward'], 2) }}$</td>
                                    <td class="text-right">{{ number_format($user['profit_from_deposit'], 2) }}$</td>
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

    <!-- Right Column -->
    <div class="col-lg-6">
        <!-- Cược từ tiền thưởng -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0 font-weight-bold">Cược từ tiền thưởng</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm table-bordered mb-0">
                    <tbody>
                        <tr>
                            <td>Tổng số thưởng Lì xì & Giftcode</td>
                            <td class="text-right">{{ number_format($totalRewardGiven, 2) }}$</td>
                        </tr>
                        <tr>
                            <td>Số tiền cược từ tiền thưởng</td>
                            <td class="text-right">{{ number_format($betsFromReward, 2) }}$</td>
                        </tr>
                        <tr>
                            <td>Trả thưởng cược</td>
                            <td class="text-right text-danger">-{{ number_format($payoutFromReward, 2) }}$</td>
                        </tr>
                        <tr class="bg-light">
                            <td class="font-weight-bold">LỜI / LỖ</td>
                            <td class="text-right font-weight-bold {{ $profitFromReward >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $profitFromReward >= 0 ? '+' : '' }}{{ number_format($profitFromReward, 2) }}$
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="row mt-3">
                    <div class="col-md-6 mb-3">
                        <h6 class="font-weight-bold">User thắng từ thưởng</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th class="text-right">Số tiền thắng</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $winningList = collect($usersWinningFromReward ?? [])->map(function($amount, $uid) {
                                            return [
                                                'username' => $uid,
                                                'amount' => $amount,
                                            ];
                                        });
                                        // If $usersWinningFromReward is already keyed, convert to list with usernames
                                        if (($usersWinningFromReward ?? null) && ! $winningList->count()) {
                                            $winningList = collect($usersWinningFromReward);
                                        }
                                    @endphp
                                    @forelse($usersWinningFromReward ?? [] as $uid => $amount)
                                        <tr>
                                            <td>
                                                @php $u = \App\Models\User::find($uid); @endphp
                                                {{ $u ? ($u->name ?? $u->email ?? 'N/A') : 'N/A' }}
                                            </td>
                                            <td class="text-right">{{ number_format($amount, 2) }}$</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">Không có dữ liệu</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h6 class="font-weight-bold">User rút tiền từ tiền thưởng</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th class="text-right">Số lần</th>
                                        <th class="text-right">Số tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rewardTransfers ?? [] as $item)
                                        <tr>
                                            <td>{{ $item['username'] }}</td>
                                            <td class="text-right">{{ $item['transfer_count'] ?? 0 }}</td>
                                            <td class="text-right">{{ number_format($item['total_amount'] ?? 0, 2) }}$</td>
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
        </div>

        <!-- Tổng tiền cược từ tiền nạp -->
        <div class="card mb-4" style="background-color: #fff3cd;">
            <div class="card-header" style="background-color: #ffc107;">
                <h5 class="mb-0 font-weight-bold">Tổng tiền cược từ tiền nạp</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm table-bordered mb-0">
                    <tbody>
                        <tr>
                            <td>Tổng số tiền nạp</td>
                            <td class="text-right font-weight-bold">{{ number_format($totalDeposits, 2) }}$</td>
                        </tr>
                        <tr>
                            <td>Số tiền cược tổng từ tiền nạp</td>
                            <td class="text-right font-weight-bold">{{ number_format($totalBetsFromDeposits, 2) }}$</td>
                        </tr>
                        <tr>
                            <td>Thắng</td>
                            <td class="text-right">{{ number_format($winsFromDeposits, 2) }}$</td>
                        </tr>
                        <tr>
                            <td>Thua</td>
                            <td class="text-right">{{ number_format($lossesFromDeposits, 2) }}$</td>
                        </tr>
                        <tr>
                            <td>User lời / lỗ</td>
                            <td class="text-right {{ ($winsFromDeposits - $lossesFromDeposits) >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ ($winsFromDeposits - $lossesFromDeposits) >= 0 ? '+' : '' }}{{ number_format($winsFromDeposits - $lossesFromDeposits, 2) }}$
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- User rút tiền nhiều nhất -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0 font-weight-bold">User rút tiền nhiều nhất</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th class="text-right">Số lần rút</th>
                                <th class="text-right">Số tiền rút</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($usersWithdrawals as $user)
                                <tr>
                                    <td>{{ $user['username'] }}</td>
                                    <td class="text-right">{{ $user['withdrawal_count'] }}</td>
                                    <td class="text-right">{{ number_format($user['total_amount'], 2) }}$</td>
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
</div>

<script>
function showUsersWinningFromReward() {
    alert('Tính năng đang được phát triển. Cần track chính xác bets từ ví thưởng.');
}

function showUsersWithdrawFromReward() {
    alert('Tính năng đang được phát triển. Cần track chính xác khi user chuyển từ ví thưởng sang ví nạp rồi rút.');
}
</script>
