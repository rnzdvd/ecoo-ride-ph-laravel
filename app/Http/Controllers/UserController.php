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
}
