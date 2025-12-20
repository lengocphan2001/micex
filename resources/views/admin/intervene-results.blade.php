@extends('adminlte::page')

@section('title', 'Can thiệp kết quả - Micex Admin')

@section('content_header')
    <h1>Can thiệp kết quả</h1>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade in">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade in">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            {{ session('error') }}
        </div>
    @endif

    <!-- Realtime Bet Amounts -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i> Tổng mức cược theo loại đá (Realtime)
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row" id="realtimeBetAmounts">
                        @php
                            $gemTypes = [
                                'thachanh' => ['name' => 'Thạch Anh', 'icon' => 'thachanh.png'],
                                'thachanhtim' => ['name' => 'Thạch Anh Tím', 'icon' => 'thachanhtim.png'],
                                'ngusac' => ['name' => 'Ngũ Sắc', 'icon' => 'ngusac.png'],
                                'daquy' => ['name' => 'Đá Quý', 'icon' => 'daquy.png'],
                                'cuoc' => ['name' => 'Cuốc', 'icon' => 'cuoc.png'],
                                'kimcuong' => ['name' => 'Kim Cương', 'icon' => 'kimcuong.png'],
                            ];
                        @endphp
                        @foreach($gemTypes as $type => $gem)
                            <div class="col-md-4 col-sm-6 mb-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-secondary">
                                        <img src="{{ asset('images/icons/' . $gem['icon']) }}" alt="{{ $gem['name'] }}" style="width: 32px; height: 32px;">
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">{{ $gem['name'] }}</span>
                                        <span class="info-box-number" data-gem-type="{{ $type }}">0.00</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Chỉnh sửa tỉ lệ ăn -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-percentage"></i> Chỉnh sửa tỉ lệ ăn
                    </h3>
                </div>
                <div class="card-body">
                    <form id="updatePayoutRatesForm" action="{{ route('admin.intervene-results.update-rates') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="thachanh">
                                <img src="{{ asset('images/icons/thachanh.png') }}" alt="Thạch Anh" class="d-inline-block" style="width: 24px; height: 24px;">
                                Thạch Anh
                            </label>
                            <input type="number" step="0.01" min="1" class="form-control @error('thachanh') is-invalid @enderror" 
                                   id="thachanh" name="thachanh" 
                                   value="{{ old('thachanh', $payoutRates['thachanh']) }}" required>
                            @error('thachanh')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="thachanhtim">
                                <img src="{{ asset('images/icons/thachanhtim.png') }}" alt="Thạch Anh Tím" class="d-inline-block" style="width: 24px; height: 24px;">
                                Thạch Anh Tím
                            </label>
                            <input type="number" step="0.01" min="1" class="form-control @error('thachanhtim') is-invalid @enderror" 
                                   id="thachanhtim" name="thachanhtim" 
                                   value="{{ old('thachanhtim', $payoutRates['thachanhtim']) }}" required>
                            @error('thachanhtim')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="ngusac">
                                <img src="{{ asset('images/icons/ngusac.png') }}" alt="Ngũ Sắc" class="d-inline-block" style="width: 24px; height: 24px;">
                                Ngũ Sắc
                            </label>
                            <input type="number" step="0.01" min="1" class="form-control @error('ngusac') is-invalid @enderror" 
                                   id="ngusac" name="ngusac" 
                                   value="{{ old('ngusac', $payoutRates['ngusac']) }}" required>
                            @error('ngusac')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="daquy">
                                <img src="{{ asset('images/icons/daquy.png') }}" alt="Đá Quý" class="d-inline-block" style="width: 24px; height: 24px;">
                                Đá Quý
                            </label>
                            <input type="number" step="0.01" min="1" class="form-control @error('daquy') is-invalid @enderror" 
                                   id="daquy" name="daquy" 
                                   value="{{ old('daquy', $payoutRates['daquy']) }}" required>
                            @error('daquy')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="cuoc">
                                <img src="{{ asset('images/icons/cuoc.png') }}" alt="Cuốc" class="d-inline-block" style="width: 24px; height: 24px;">
                                Cuốc
                            </label>
                            <input type="number" step="0.01" min="1" class="form-control @error('cuoc') is-invalid @enderror" 
                                   id="cuoc" name="cuoc" 
                                   value="{{ old('cuoc', $payoutRates['cuoc']) }}" required>
                            @error('cuoc')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="kimcuong">
                                <img src="{{ asset('images/icons/kimcuong.png') }}" alt="Kim Cương" class="d-inline-block" style="width: 24px; height: 24px;">
                                Kim Cương
                            </label>
                            <input type="number" step="0.01" min="1" class="form-control @error('kimcuong') is-invalid @enderror" 
                                   id="kimcuong" name="kimcuong" 
                                   value="{{ old('kimcuong', $payoutRates['kimcuong']) }}" required>
                            @error('kimcuong')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary" id="saveRatesBtn">
                            <i class="fas fa-save"></i> <span id="saveRatesBtnText">Lưu tỉ lệ ăn</span>
                        </button>
                        <div id="ratesUpdateMessage" class="mt-2"></div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Đặt kết quả phiên cược -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-dice"></i> Đặt kết quả phiên cược
                    </h3>
                </div>
                <div class="card-body">
                    <div id="roundInfoContainer">
                        @if($currentRound)
                            <div class="alert alert-info" id="roundInfoAlert">
                                <strong>Phiên hiện tại:</strong> <span id="roundNumber">#{{ $currentRound->round_number }}</span><br>
                                <strong>Trạng thái:</strong> <span id="roundStatus">
                                    @if($currentRound->status === 'pending')
                                        <span class="badge badge-secondary">Chờ bắt đầu</span>
                                    @elseif($currentRound->status === 'running')
                                        <span class="badge badge-success">Đang chạy</span>
                                    @elseif($currentRound->status === 'finished')
                                        <span class="badge badge-danger">Đã kết thúc</span>
                                    @endif
                                </span>
                                <span id="roundFinalResult">
                                    @if($currentRound->status === 'finished' && $currentRound->final_result)
                                        <br><strong>Kết quả:</strong> 
                                        @php
                                            $gemNames = [
                                                'thachanh' => 'Thạch Anh',
                                                'thachanhtim' => 'Thạch Anh Tím',
                                                'ngusac' => 'Ngũ Sắc',
                                                'daquy' => 'Đá Quý',
                                                'cuoc' => 'Cuốc',
                                                'kimcuong' => 'Kim Cương',
                                            ];
                                        @endphp
                                        <img src="{{ asset('images/icons/' . $currentRound->final_result . '.png') }}" alt="{{ $gemNames[$currentRound->final_result] ?? $currentRound->final_result }}" style="width: 24px; height: 24px;" class="d-inline-block">
                                        {{ $gemNames[$currentRound->final_result] ?? $currentRound->final_result }}
                                    @endif
                                </span>
                            </div>

                            <div id="adminSetResultAlert" style="display: none;">
                                <div class="alert alert-success">
                                    <strong>Kết quả đã được đặt:</strong> 
                                    <span id="adminSetResultDisplay"></span>
                                    <br><small>Phiên sẽ tiếp tục chạy và kết quả này sẽ là kết quả cuối cùng.</small>
                                </div>
                            </div>
                            
                            <form id="setResultForm" action="{{ route('admin.intervene-results.set-result') }}" method="POST" onsubmit="return handleSetResult(event);">
                                @csrf
                                <input type="hidden" name="round_id" id="roundIdInput" value="{{ $currentRound->id }}">
                                
                                <div class="form-group">
                                    <label for="final_result">Chọn kết quả (sẽ là kết quả cuối cùng - giây 60)</label>
                                    <div class="row" id="gemOptionsContainer">
                                        @php
                                            $gemOptions = [
                                                'thachanh' => ['name' => 'Thạch Anh', 'icon' => 'thachanh.png'],
                                                'thachanhtim' => ['name' => 'Thạch Anh Tím', 'icon' => 'thachanhtim.png'],
                                                'ngusac' => ['name' => 'Ngũ Sắc', 'icon' => 'ngusac.png'],
                                                'daquy' => ['name' => 'Đá Quý', 'icon' => 'daquy.png'],
                                                'cuoc' => ['name' => 'Cuốc', 'icon' => 'cuoc.png'],
                                                'kimcuong' => ['name' => 'Kim Cương', 'icon' => 'kimcuong.png'],
                                            ];
                                        @endphp
                                        @foreach($gemOptions as $value => $gem)
                                            <div class="col-6 mb-2">
                                                <label class="d-flex align-items-center p-2 border rounded cursor-pointer gem-option-label" style="cursor: pointer;" data-gem-value="{{ $value }}">
                                                    <input type="radio" name="final_result" value="{{ $value }}" 
                                                           class="mr-2 gem-option-radio" 
                                                           {{ old('final_result', $currentRound->admin_set_result) === $value ? 'checked' : '' }} required>
                                                    <img src="{{ asset('images/icons/' . $gem['icon']) }}" alt="{{ $gem['name'] }}" 
                                                         style="width: 24px; height: 24px;" class="mr-2">
                                                    <span>{{ $gem['name'] }}</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('final_result')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-warning" id="setResultBtn">
                                    <i class="fas fa-exclamation-triangle"></i> <span id="setResultBtnText">{{ $currentRound->admin_set_result ? 'Cập nhật kết quả' : 'Đặt kết quả' }}</span> (Can thiệp)
                                </button>
                            </form>
                            
                            <div id="roundNotRunningAlert" class="alert alert-warning" style="display: none;">
                                Chỉ có thể đặt kết quả khi phiên đang chạy.
                            </div>
                        @else
                            <div class="alert alert-warning">
                                Không tìm thấy phiên cược hiện tại.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    console.log('Admin intervene-results script loaded');
    
    let realtimeInterval = null;
    let betAmountsInterval = null;
    
    // Gem type names and icons
    const GEM_TYPES = {
        'thachanh': { name: 'Thạch Anh', icon: '{{ asset("images/icons/thachanh.png") }}' },
        'thachanhtim': { name: 'Thạch Anh Tím', icon: '{{ asset("images/icons/thachanhtim.png") }}' },
        'ngusac': { name: 'Ngũ Sắc', icon: '{{ asset("images/icons/ngusac.png") }}' },
        'daquy': { name: 'Đá Quý', icon: '{{ asset("images/icons/daquy.png") }}' },
        'cuoc': { name: 'Cuốc', icon: '{{ asset("images/icons/cuoc.png") }}' },
        'kimcuong': { name: 'Kim Cương', icon: '{{ asset("images/icons/kimcuong.png") }}' },
    };
    
    let lastRoundId = null;
    let lastRoundNumber = null;
    let currentRoundStatus = null; // Track current round status
    let isPollingActive = false; // Track if polling is active
    
    // Load bet amounts only (every 2 seconds) - only when round is running
    function loadBetAmounts() {
        // Only fetch if round is running
        if (currentRoundStatus !== 'running') {
            console.log('Round not running, skipping bet amounts fetch');
            return;
        }
        
        console.log('Loading bet amounts...');
        fetch('{{ route("admin.intervene-results.bet-amounts") }}', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            cache: 'no-cache'
        })
            .then(response => {
                console.log('Bet amounts response status:', response.status);
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Bet amounts data received:', data);
                // Update bet amounts only
                if (data.bet_amounts) {
                    Object.keys(data.bet_amounts).forEach(gemType => {
                        const amountEl = document.querySelector(`[data-gem-type="${gemType}"]`);
                        if (amountEl) {
                            const amount = parseFloat(data.bet_amounts[gemType] || 0);
                            const oldValue = amountEl.textContent;
                            amountEl.textContent = amount.toFixed(2);
                            console.log(`Updated bet amount for ${gemType}: ${oldValue} -> ${amount.toFixed(2)}`);
                        } else {
                            console.warn(`Element not found for gem type: ${gemType}`);
                        }
                    });
                } else {
                    console.warn('No bet_amounts in response:', data);
                }
            })
            .catch(error => {
                console.error('Error loading bet amounts:', error);
            });
    }
    
    // Control polling based on round status
    function controlPolling(status) {
        const wasRunning = currentRoundStatus === 'running';
        const isRunning = status === 'running';
        
        // Update current status first
        currentRoundStatus = status;
        
        // If status is running and polling is not active, start polling
        if (isRunning && !isPollingActive) {
            console.log('Round is running, starting polling...');
            isPollingActive = true;
            
            // Start bet amounts polling
            if (betAmountsInterval) {
                clearInterval(betAmountsInterval);
            }
            loadBetAmounts(); // Load immediately
            betAmountsInterval = setInterval(loadBetAmounts, 2000);
            
        } else if (!isRunning && isPollingActive) {
            // If status is not running and polling is active, stop polling
            console.log('Round ended or in break, stopping polling...');
            isPollingActive = false;
            
            // Stop bet amounts polling
            if (betAmountsInterval) {
                clearInterval(betAmountsInterval);
                betAmountsInterval = null;
            }
            
            // Reset bet amounts display to 0 when round ends
            document.querySelectorAll('[data-gem-type]').forEach(el => {
                el.textContent = '0.00';
            });
        }
    }
    
    // Load realtime data for "Đặt kết quả phiên cược" section
    function loadRealtimeRoundData() {
        // Only fetch if round is running or we need to check status
        // Always fetch to check status, but only update UI if running
        fetch('{{ route("admin.intervene-results.realtime") }}', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            cache: 'no-cache'
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Realtime round data received:', data);
                
                // Check if new round started (break time ended)
                if (lastRoundId !== null && lastRoundId !== data.round.id) {
                    // New round started, reload page to refresh form
                    console.log('New round started, reloading page...');
                    window.location.reload();
                    return;
                }
                
                lastRoundId = data.round.id;
                lastRoundNumber = data.round.round_number;
                
                // Control polling based on round status (this will start/stop polling as needed)
                controlPolling(data.round.status);
                
                // Update round number
                const roundNumberEl = document.getElementById('roundNumber');
                if (roundNumberEl) {
                    roundNumberEl.textContent = '#' + data.round.round_number;
                }
                
                // Update round status
                const roundStatusEl = document.getElementById('roundStatus');
                if (roundStatusEl) {
                    let statusHtml = '';
                    if (data.round.status === 'pending') {
                        statusHtml = '<span class="badge badge-secondary">Chờ bắt đầu</span>';
                    } else if (data.round.status === 'running') {
                        statusHtml = '<span class="badge badge-success">Đang chạy</span>';
                    } else if (data.round.status === 'finished') {
                        statusHtml = '<span class="badge badge-danger">Đã kết thúc</span>';
                    }
                    roundStatusEl.innerHTML = statusHtml;
                }
                
                // Update final result
                const roundFinalResultEl = document.getElementById('roundFinalResult');
                if (roundFinalResultEl) {
                    if (data.round.status === 'finished' && data.round.final_result) {
                        const gem = GEM_TYPES[data.round.final_result];
                        if (gem) {
                            roundFinalResultEl.innerHTML = '<br><strong>Kết quả:</strong> <img src="' + gem.icon + '" alt="' + gem.name + '" style="width: 24px; height: 24px;" class="d-inline-block"> ' + gem.name;
                        } else {
                            roundFinalResultEl.innerHTML = '<br><strong>Kết quả:</strong> ' + data.round.final_result;
                        }
                    } else {
                        roundFinalResultEl.innerHTML = '';
                    }
                }
                
                // Update admin set result display
                const adminSetResultAlert = document.getElementById('adminSetResultAlert');
                const adminSetResultDisplay = document.getElementById('adminSetResultDisplay');
                if (data.round.admin_set_result) {
                    const gem = GEM_TYPES[data.round.admin_set_result];
                    if (gem) {
                        if (adminSetResultDisplay) {
                            adminSetResultDisplay.innerHTML = '<img src="' + gem.icon + '" alt="' + gem.name + '" style="width: 24px; height: 24px;" class="d-inline-block"> ' + gem.name;
                        }
                        if (adminSetResultAlert) {
                            adminSetResultAlert.style.display = 'block';
                        }
                    } else {
                        if (adminSetResultDisplay) {
                            adminSetResultDisplay.textContent = data.round.admin_set_result;
                        }
                        if (adminSetResultAlert) {
                            adminSetResultAlert.style.display = 'block';
                        }
                    }
                } else {
                    if (adminSetResultAlert) {
                        adminSetResultAlert.style.display = 'none';
                    }
                }
                
                // Update radio buttons to reflect admin_set_result
                if (data.round.admin_set_result) {
                    const radioButtons = document.querySelectorAll('.gem-option-radio');
                    const labels = document.querySelectorAll('.gem-option-label');
                    radioButtons.forEach(radio => {
                        if (radio.value === data.round.admin_set_result) {
                            radio.checked = true;
                        }
                    });
                    labels.forEach(label => {
                        label.classList.remove('border-success', 'bg-light');
                        if (label.dataset.gemValue === data.round.admin_set_result) {
                            label.classList.add('border-success', 'bg-light');
                        }
                    });
                }
                
                // Update button text
                const setResultBtnText = document.getElementById('setResultBtnText');
                if (setResultBtnText) {
                    setResultBtnText.textContent = data.round.admin_set_result ? 'Cập nhật kết quả' : 'Đặt kết quả';
                }
                
                // Show/hide form based on round status
                const setResultForm = document.getElementById('setResultForm');
                const roundNotRunningAlert = document.getElementById('roundNotRunningAlert');
                const roundIdInput = document.getElementById('roundIdInput');
                
                if (data.round.status === 'running') {
                    if (setResultForm) setResultForm.style.display = 'block';
                    if (roundNotRunningAlert) roundNotRunningAlert.style.display = 'none';
                    if (roundIdInput) roundIdInput.value = data.round.id;
                } else {
                    if (setResultForm) setResultForm.style.display = 'none';
                    if (roundNotRunningAlert) roundNotRunningAlert.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error loading realtime round data:', error);
            });
    }
    
    // Initial check of round status to determine if polling should start
    function checkInitialRoundStatus() {
        fetch('{{ route("admin.intervene-results.realtime") }}', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            cache: 'no-cache'
        })
            .then(response => response.json())
            .then(data => {
                if (data.round) {
                    // Set status and control polling
                    controlPolling(data.round.status);
                    
                    // Also load bet amounts immediately if round is running
                    if (data.round.status === 'running') {
                        loadBetAmounts();
                    }
                }
            })
            .catch(error => {
                console.error('Error checking initial round status:', error);
            });
    }
    
    // Handle set result form submission
    function handleSetResult(event) {
        if (!confirm('Bạn có chắc chắn muốn đặt kết quả cho phiên này? Phiên sẽ tiếp tục chạy và kết quả này sẽ là kết quả cuối cùng (giây 60).')) {
            event.preventDefault();
            return false;
        }
        
        // Submit form normally (will reload page)
        return true;
    }
    
    // Handle update payout rates form submission (AJAX)
    function setupUpdatePayoutRatesForm() {
        const updateRatesForm = document.getElementById('updatePayoutRatesForm');
        if (updateRatesForm) {
            updateRatesForm.addEventListener('submit', function(event) {
                event.preventDefault();
                
                const formData = new FormData(updateRatesForm);
                const saveBtn = document.getElementById('saveRatesBtn');
                const saveBtnText = document.getElementById('saveRatesBtnText');
                const messageDiv = document.getElementById('ratesUpdateMessage');
                
                // Disable button and show loading
                saveBtn.disabled = true;
                saveBtnText.textContent = 'Đang lưu...';
                if (messageDiv) {
                    messageDiv.innerHTML = '';
                }
                
                fetch('{{ route("admin.intervene-results.update-rates") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Re-enable button
                    saveBtn.disabled = false;
                    saveBtnText.textContent = 'Lưu tỉ lệ ăn';
                    
                    if (data.success) {
                        // Show success message
                        if (messageDiv) {
                            messageDiv.innerHTML = '<div class="alert alert-success alert-dismissible fade in"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>' + data.message + '</div>';
                        }
                        
                        // Auto-hide success message after 3 seconds
                        setTimeout(function() {
                            if (messageDiv) {
                                messageDiv.innerHTML = '';
                            }
                        }, 3000);
                    } else {
                        // Show error message
                        if (messageDiv) {
                            messageDiv.innerHTML = '<div class="alert alert-danger alert-dismissible fade in"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>' + (data.message || 'Có lỗi xảy ra khi cập nhật tỉ lệ ăn.') + '</div>';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error updating payout rates:', error);
                    saveBtn.disabled = false;
                    saveBtnText.textContent = 'Lưu tỉ lệ ăn';
                    
                    if (messageDiv) {
                        messageDiv.innerHTML = '<div class="alert alert-danger alert-dismissible fade in"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>Có lỗi xảy ra khi cập nhật tỉ lệ ăn.</div>';
                    }
                });
            });
        }
    }
    
    // Start realtime updates
    document.addEventListener('DOMContentLoaded', function() {
        // Setup update payout rates form
        setupUpdatePayoutRatesForm();
        
        // Initialize lastRoundId from current round
        @if($currentRound)
            lastRoundId = {{ $currentRound->id }};
            lastRoundNumber = {{ $currentRound->round_number }};
            currentRoundStatus = '{{ $currentRound->status }}';
        @endif
        
        // Check initial round status and start/stop polling accordingly
        checkInitialRoundStatus();
        
        // Load realtime round data for "Đặt kết quả phiên cược" section (always check status)
        // This will control polling based on round status
        loadRealtimeRoundData();
        realtimeInterval = setInterval(loadRealtimeRoundData, 1000);
    });
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (realtimeInterval) {
            clearInterval(realtimeInterval);
        }
        if (betAmountsInterval) {
            clearInterval(betAmountsInterval);
        }
    });
</script>
@stop
