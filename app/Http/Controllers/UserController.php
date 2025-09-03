<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getUserBalance(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'debt' => $user->debt,
            'balance' => $user->balance
        ]);
    }

    public function getUserCards(Request $request)
    {
        $user = $request->user();
        return response()->json($user->cards);
    }

    public function getUserStats(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'total_distance' => $user->total_distance,
            'total_rides' => $user->total_rides,
        ]);
    }
}
