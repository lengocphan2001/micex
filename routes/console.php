<?php

use Illuminate\Support\Facades\Schedule;

// NOTE: Round timer được chạy bởi RoundTimerLoop service (systemd)
// Không cần schedule ở đây vì service sẽ chạy liên tục mỗi giây
// Schedule::command('round:process')->everySecond(); // Đã chuyển sang RoundTimerLoop service
