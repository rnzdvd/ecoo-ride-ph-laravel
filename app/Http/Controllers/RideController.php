<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Ride;
use App\Services\PushNotificationService;

class RideController extends Controller
{

    public function startRide(Request $request)
    {
        $initialCharge = 0;
        $user = $request->user();

        if (!$request->has('scooter_id')) {
            return response()->json([
                'message' => 'scooter_id is required.',
            ], 422);
        }

        if (!$request->has('option')) {
            return response()->json([
                'message' => 'option is required.',
            ], 422);
        }

        if ($request->option == '10min') {
            $initialCharge = 35;
        } else {
            $initialCharge = 65;
        }

        if ($user->balance < $initialCharge) {
            return response()->json([
                'message' => 'Insufficient balance to start ride.'
            ], 400);
        }

        // Check if the user has an ongoing ride
        $existingRide = Ride::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($existingRide) {
            return response()->json([
                'message' => 'You already have an active ride.',
                'ride_id' => $existingRide->id,
            ], 400);
        }

        $request->validate([
            'scooter_id' => 'required|string',
            'option' => 'required|string',
        ]);


        // Check if user has enough balance
        if ($request->option === '10min') {
            $user->decrement('balance', 35);
        } else {
            $user->decrement('balance', 65);
        }

        // Create ride record
        $ride = Ride::create([
            'user_id' => $user->id,
            'scooter_id' => $request->scooter_id,
            'started_at' => now(),
            'last_billed_at' => now(),
            'billed_intervals' => 1,
            'status' => 'active',
            'option' => $request->option
        ]);

        return response()->json([
            'message' => 'Ride started successfully.',
            'ride_id' => $ride->id
        ]);
    }

    public function endRide(Request $request)
    {
        $user = $request->user();


        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Find the user’s active ride
        $ride = Ride::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$ride) {
            return response()->json(['message' => 'No active ride found.'], 404);
        }

        // Mark ride as ended
        $ride->update([
            'ended_at' => now(),
            'status' => 'ended',
            'end_reason' => 'manual',
        ]);

        return response()->json([
            'message' => 'Ride ended successfully.',
            'ride_id' => $ride->id,
        ]);
    }
}
