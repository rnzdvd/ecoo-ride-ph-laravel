<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Ride;
use App\Services\ScooterService;
use Carbon\Carbon;

class RideController extends Controller
{

    public function startRide(Request $request, ScooterService $scooterService)
    {
        $initialCharge = 0;
        $user = $request->user();
        $request->validate([
            'scooter_id' => 'required',
            'option'     => 'required',
        ]);

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


        $unlockResponse = $scooterService->unlockScooter($request->scooter_id);

        if ($unlockResponse['success'] !== true) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unlock scooter'
            ], 500);
        } else {

            $user->decrement('balance', $initialCharge);


            // Create ride record
            $ride = Ride::create([
                'user_id' => $user->id,
                'scooter_id' => $request->scooter_id,
                'started_at' => now(),
                'last_billed_at' => now(),
                'billed_intervals' => 1,
                'status' => 'active',
                'option' => $request->option,
                'total_distance' => 0,
                'total_charged' => $initialCharge,
            ]);

            $rideArray = $ride->toArray();
            $rideArray['total_duration'] = 0;

            return response()->json([
                'message' => 'Ride started successfully.',
                'ride' => $rideArray
            ]);
        }
    }

    public function endRide(Request $request, ScooterService $scooterService)
    {
        $request->validate([
            'id' => 'required',
        ]);

        $ride = Ride::find($request->id);

        if (!$ride) {
            return response()->json([
                'message' => 'Ride not found.',
            ], 404);
        }

        $lockResponse = $scooterService->lockScooter($ride->scooter_id);

        if ($lockResponse['success'] !== true) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to lock scooter'
            ], 500);
        } else {
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

    public function getRide(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);

        $ride = Ride::find($request->id);

        if (!$ride) {
            return response()->json([
                'message' => 'Ride not found.',
            ], 404);
        }

        $rideArray = $ride->toArray();
        $rideArray['total_duration'] = Carbon::parse($ride->started_at)->diffInSeconds($ride->status === 'ended' ? $ride->ended_at : now());

        return response()->json([
            'message' => 'Ride found.',
            'ride' => $rideArray
        ]);
    }

    public function getRideHistory(Request $request)
    {
        $user = $request->user();

        $rides =  $user
            ->rides()
            ->where('status', 'ended')   // or ->whereNotNull('ended_at')
            ->get();

        return response()->json($rides);
    }
}
