<?php

use App\Console\Commands\ProcessRideBilling;
use App\Console\Commands\ProcessRideDistance;
use Illuminate\Support\Facades\Schedule;


Schedule::command(ProcessRideBilling::class)->everyMinute();
Schedule::command(ProcessRideDistance::class)->everyThirtySeconds();
