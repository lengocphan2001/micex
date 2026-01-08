@extends('adminlte::page')

@section('title', 'Lịch sử giao dịch user')

@section('content_header')
    <h1>Lịch sử giao dịch user</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <!-- Tabs -->
            <div class="btn-group mb-3" role="group">
                @php
                    $games = [
                        'xanhdo' => 'Xanh đỏ 1 phút',
                        'trading' => 'Trading',
                        'khaithac' => 'Khai thác',
                    ];
                @endphp
                @foreach($games as $key => $label)
                    <a href="{{ route('admin.bet-history', array_merge(request()->query(), ['game' => $key])) }}"
                       class="btn {{ $game === $key ? 'btn-primary' : 'btn-outline-secondary' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <!-- Filters -->
            <form method="GET" action="{{ route('admin.bet-history') }}" class="form-inline mb-3">
                <input type="hidden" name="game" value="{{ $game }}">
                <div class="form-group mr-2 mb-2">
                    <input type="text" name="username" class="form-control" placeholder="Username"
                           value="{{ $username }}">
                </div>
                <div class="form-group mr-2 mb-2">
                    <input type="date" name="date" class="form-control" value="{{ $date }}">
                </div>
                <button type="submit" class="btn btn-primary mb-2">Tìm kiếm</button>
            </form>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Username</th>
                            <th>No.</th>
                            <th class="text-right">Đặt cược</th>
                            <th>Lựa chọn</th>
                            <th>Kết quả</th>
                            <th class="text-right">Lợi nhuận</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bets as $bet)
                            @php
                                $user = $bet->user;
                                $round = $bet->round;
                                $stake = ($bet->amount_from_deposit ?? 0) + ($bet->amount_from_reward ?? 0) ?: $bet->amount;
                                if ($bet->status === 'won') {
                                    $profit = ($bet->payout_amount ?? 0) - $stake;
                                } else {
                                    $profit = -$stake;
                                }
                                $isWin = $profit > 0;
                                $result = $round->final_result ?? $round->current_result ?? null;
                            @endphp
                            <tr>
                                <td>{{ $user ? ($user->name ?? $user->email ?? 'N/A') : 'N/A' }}</td>
                                <td>{{ $round->round_number ?? '-' }}</td>
                                <td class="text-right">{{ number_format($stake, 2) }}$</td>
                                <td>
                                    @if($bet->bet_type === 'number')
                                        {{ $bet->bet_value ?? $bet->gem_type }}
                                    @else
                                        {{ $bet->gem_type }}
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(!is_null($result))
                                        <span class="text-{{ $result == 'kcxanh' || $result == 'xanh' ? 'success' : ($result == 'kcdo' ? 'danger' : 'primary') }}">●</span>
                                        {{ $result }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-right {{ $profit >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $profit >= 0 ? '+' : '' }}{{ number_format($profit, 2) }}$
                                </td>
                                <td>
                                    {{ $bet->created_at ? $bet->created_at->format('H:i:s d-m-Y') : '' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $bets->links() }}
            </div>
        </div>
    </div>
@stop
