<li>
    <div class="network-node level-{{ $node['level'] }}">
        <div class="node-header">
            {{ $node['user']->display_name ?? $node['user']->referral_code }}
            @if(count($node['children']) > 0)
                <span class="toggle-children">[-]</span>
            @endif
        </div>
        <div class="node-info">
            <div>ID: {{ $node['user']->id }}</div>
            <div>Mã: {{ $node['user']->referral_code }}</div>
            <div>SĐT: {{ $node['user']->phone_number }}</div>
            <div>Số dư: {{ number_format($node['user']->balance, 2) }} đá quý</div>
        </div>
        <div class="node-stats">
            <div>Nạp: <strong>{{ number_format($node['total_deposit'], 2) }}</strong></div>
            <div>Rút: <strong>{{ number_format($node['total_withdraw'], 2) }}</strong></div>
            <div>Cấp dưới: <strong>{{ $node['subordinates_count'] }}</strong></div>
        </div>
        <div class="mt-2">
            <a href="{{ route('admin.member.detail', $node['user']->id) }}" class="btn btn-xs" style="font-size: 10px; padding: 2px 8px; background: rgba(255,255,255,0.2); color: inherit; border: 1px solid rgba(255,255,255,0.3);">
                Xem chi tiết
            </a>
        </div>
    </div>
    
    @if(count($node['children']) > 0)
        <ul>
            @foreach($node['children'] as $child)
                @include('admin.partials.network-node', ['node' => $child])
            @endforeach
        </ul>
    @endif
</li>

