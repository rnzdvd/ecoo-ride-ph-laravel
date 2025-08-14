<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ride;
use App\Services\PushNotificationService;
use App\Services\ScooterService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;


class ProcessRideBilling extends Command implements ShouldQueue
{
    protected $signature = 'billing:process-rides';
    protected $description = 'Dispatch billing jobs for all active rides';


    protected $pushNotificationService;
    protected $scooterService;

    public function __construct(PushNotificationService $pushNotificationService, ScooterService $scooterService)
    {
        parent::__construct();
        $this->pushNotificationService = $pushNotificationService;
        $this->scooterService = $scooterService;
    }

    public function handle()
    {
        $now = now();
        $rides = Ride::where('status', 'active')->get();

        if ($rides->isEmpty()) {
            Log::info("No active rides at " . $now);
            return;
        }

        foreach ($rides as $ride) {
            $user = $ride->user;

            $startTime = $ride->started_at;
            $lastBilledAt = $ride->last_billed_at;
            $totalRideSeconds = Carbon::parse($startTime)->diffInSeconds($now);

            // Set initial free minutes
            $initialMinutes = $ride->option === '20min' ? 20 : 10;
            $initialSeconds = $initialMinutes * 60;

            // First bill delay after free period (in seconds)
            $firstBillingDelay = 60; // 1 min after free time
            $billingInterval = 660;  // 11 min in seconds

            // Still inside free time → skip
            if ($totalRideSeconds <= $initialSeconds) {
                continue;
            }

            // Determine if it's first bill or subsequent bills
            if (Carbon::parse($startTime)->eq(Carbon::parse($lastBilledAt))) {
                // First bill after free time
                if ($totalRideSeconds >= $initialSeconds + $firstBillingDelay) {
                    $this->processBilling($ride, $user, $now);
                }
            } else {
                // Time since last bill
                $diffSinceLastBill = Carbon::parse($lastBilledAt)->diffInSeconds($now);

                if ($diffSinceLastBill >= $billingInterval) {
                    $this->processBilling($ride, $user, $now);
                }
            }
        }
    }

    /**
     * Handle the actual billing logic
     */
    private function processBilling($ride, $user, $now)
    {
        // If the user already has debt → end ride immediately
        if ($user->debt > 0) {
            $ride->ended_at = $now;
            $ride->status = 'ended';
            $ride->end_reason = 'low_balance';
            $ride->save();

            // Lock scooter
            $this->scooterService->lockScooter($ride->scooter_id);

            // Push notification
            if ($user->device_token) {
                $this->pushNotificationService->sendPushNotification(
                    $user->device_token,
                    'Ride Ended',
                    'Your ride has ended due to low wallet balance.',
                    ['rideEnded' => true]
                );
            }
            return;
        }

        // Charge amount
        $charge = 35;

        if ($user->balance >= $charge) {
            $user->balance -= $charge;
        } else {
            $shortfall = $charge - $user->balance;
            $user->debt += $shortfall;
            $user->balance = 0;
        }

        $user->save();

        // Update ride billing info
        $ride->billed_intervals++;
        $ride->last_billed_at = $now;
        $ride->save();
    }
}
