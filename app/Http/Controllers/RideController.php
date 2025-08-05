<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Ride;


class RideController extends Controller
{

    public function startRide(Request $request)
    {

        if (!$request->has('scooter_id')) {
            return response()->json([
                'message' => 'scooter_id is required.',
            ], 422);
        }

        $user = $request->user();

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
        ]);


        // Check if user has enough balance
        if ($user->balance < 50) {
            return response()->json([
                'message' => 'Insufficient balance to start ride.'
            ], 400);
        }

        // Deduct initial ₱50
        $user->decrement('balance', 50);

        // Create ride record
        $ride = Ride::create([
            'user_id' => $user->id,
            'scooter_id' => $request->scooter_id,
            'started_at' => now(),
            'last_billed_at' => now(),          // For billing logic
            'billed_intervals' => 1,            // Initial deduction already done
            'status' => 'active',               // Mark ride as active
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
        ]);

        return response()->json([
            'message' => 'Ride ended successfully.',
            'ride_id' => $ride->id,
        ]);
    }
}
