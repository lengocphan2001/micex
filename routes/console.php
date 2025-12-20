<?php

use Illuminate\Support\Facades\Schedule;

// Schedule round timer to run every second
Schedule::command('round:process')->everySecond();
