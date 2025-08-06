<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ride;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ProcessRideBilling extends Command implements ShouldQueue
{
    protected $signature = 'billing:process-rides';
    protected $description = 'Dispatch billing jobs for all active rides';

    public function handle()
    {

        $now = now();
        $rides = Ride::where('status', 'active')->get();

        if ($rides->isEmpty()) {
            Log::info("No active rides at " . $now);
            return;
        }

        $now = now();
        $rides = Ride::where('status', 'active')->get();

        foreach ($rides as $ride) {
            $user = $ride->user;

            $startTime = $ride->started_at;
            $lastBilledAt = $ride->last_billed_at ?? $startTime;
            $diffSinceLastBill = \Carbon\Carbon::parse($lastBilledAt)->diffInSeconds($now);
            $totalRideSeconds = \Carbon\Carbon::parse($startTime)->diffInSeconds($now);

            // Initial covered time by option (10 or 20 minutes)
            $initialMinutes = $ride->option === '20min' ? 20 : 10;
            $initialSeconds = $initialMinutes * 60;

            // Still inside initial covered time
            if ($totalRideSeconds <= $initialSeconds) {
                continue;
            }

            // Bill every 10 mins and 30 secs after covered time
            if ($diffSinceLastBill >= 630) {
                // If the user already has debt, end ride immediately
                if ($user->debt > 0) {
                    $ride->ended_at = $now;
                    $ride->status = 'ended';
                    $ride->save();
                    continue; // move to the next ride
                }

                $charge = 35;

                if ($user->balance >= $charge) {
                    // normal charge
                    $user->balance -= $charge;
                    $user->save();

                    $ride->billed_intervals++;
                    $ride->last_billed_at = $now;
                    $ride->save();
                } else {
                    // not enough: apply debt for shortfall
                    $shortfall = $charge - $user->balance;
                    $user->debt += $shortfall;
                    $user->balance = 0;
                    $user->save();

                    // mark this block as billed
                    $ride->billed_intervals++;
                    $ride->last_billed_at = $now;
                    $ride->save();
                }
            }
        }
    }
}
