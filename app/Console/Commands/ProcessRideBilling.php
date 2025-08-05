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

        foreach ($rides as $ride) {
            $lastBilledAt = $ride->last_billed_at ?? $ride->started_at;
            $diffInSeconds = Carbon::parse($lastBilledAt)->diffInSeconds($now);
            $user = $ride->user;

            if ($diffInSeconds >= 600) {
                if ($user->balance >= 50) {
                    DB::transaction(function () use ($ride, $user) {
                        $user->balance -= 50;
                        $user->save();

                        $ride->last_billed_at = now();
                        $ride->billed_intervals += 1;
                        $ride->save();
                    });

                    Log::info("✅ Deducted 50 pesos from user {$user->id} for ride {$ride->id}");
                } else {
                    $ride->ended_at = now();
                    $ride->status = 'ended';
                    $ride->save();

                    Log::warning("⚠️ Ride {$ride->id} ended due to insufficient balance");
                }
            }
        }
    }
}
