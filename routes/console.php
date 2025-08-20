<?php

use App\Console\Commands\ProcessRideBilling;
use Illuminate\Support\Facades\Schedule;


Schedule::command(ProcessRideBilling::class)->everyMinute();
