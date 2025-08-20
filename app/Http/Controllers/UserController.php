<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function getUserBalance(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'debt' => $user->debt,
            'balance' => $user->balance
        ]);
    }

    public function requestPayment(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $amount = $request->input('amount');
        $channelCode = $request->input('channel_code');


        if ($amount <= 0) {
            return response()->json(['message' => 'Invalid amount'], 400);
        }

        if (!$channelCode) {
            return response()->json(['message' => 'Channel code is required'], 422);
        }

        $referenceId = 'topup_user_' . $user->id . '_' . time();

        $payload = [
            "reference_id" => $referenceId,
            "type" => "PAY",
            "country" => "PH",
            "currency" => "PHP",
            "request_amount" => intval($amount),
            "capture_method" => "AUTOMATIC",
            "channel_code" => $channelCode,
            "channel_properties" => [
                "failure_return_url" => "ecooridephapp://ecoo.ride-ph.com/fail",
                "success_return_url" => "ecooridephapp://ecoo.ride-ph.com/success",
                "cancel_return_url" => "ecooridephapp://ecoo.ride-ph.com/cancel"
            ],
            "description" => "Top Up Wallet"
        ];

        $response = Http::withBasicAuth(env('XENDIT_SECRET_KEY'), '')
            ->withHeader('api-version', '2024-11-11')
            ->post('https://api.xendit.co/v3/payment_requests', $payload);

        if ($response->successful()) {
            return $response->json();
        } else {
            Log::error('Xendit payment request failed:', ['response' => $response->body()]);
            return response()->json(['message' => $response->body()], 200);
        }
    }
}
