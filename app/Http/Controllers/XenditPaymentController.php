<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class XenditPaymentController extends Controller
{
    public function requestPayment(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'channel_code' => 'required|string'
        ]);

        $amount = $request->input('amount');
        $channelCode = $request->input('channel_code');
        $referenceId = 'topup_user_' . $user->id . '_' . time();
        $payload = [];

        if ($channelCode == 'CARDS') {

            if (!$request->has('payment_token_id')) {
                return response()->json(['message' => 'channel code is CARDS, payment_token_id is required'], 400);
            }
            $paymentTokenId = $request->input('payment_token_id');

            $payload = [
                'reference_id' => $referenceId,
                'payment_token_id' => $paymentTokenId,
                'type' => 'PAY',
                'country' => 'PH',
                'currency' => 'PHP',
                'request_amount' => intval($amount),
                'capture_method' => 'AUTOMATIC',
                'channel_properties' => [
                    'skip_three_ds' => false,
                    'card_on_file_type' => 'CUSTOMER_UNSCHEDULED',
                    "failure_return_url" => "https://ecoo.ride-ph.com/fail",
                    "success_return_url" => "https://ecoo.ride-ph.com/success",
                    'statement_descriptor' => 'Top Up Wallet',
                ],
                "description" => "Top Up Wallet"
            ];
        } else {
            $payload = [
                "reference_id" => $referenceId,
                "type" => "PAY",
                "country" => "PH",
                "currency" => "PHP",
                "request_amount" => intval($amount),
                "capture_method" => "AUTOMATIC",
                "channel_code" => $channelCode,
                "channel_properties" => [
                    "failure_return_url" => "https://ecoo.ride-ph.com/fail",
                    "success_return_url" => "https://ecoo.ride-ph.com/success",
                    "cancel_return_url" => "https://ecoo.ride-ph.com/cancel"
                ],
                "description" => "Top Up Wallet"
            ];
        }

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


    public function generateSessionId(Request $request)
    {
        $user = $request->user();

        $referenceId = 'refId-' . $user->id . '-' . time();
        $customerId = 'custId-' . $user->id . '-' . time();
        $userName = $user->full_name;
        $phoneNumber = $user->phone_number;
        $email = $user->email;

        $payload = [
            "reference_id" => $referenceId,
            "session_type" => "SAVE",
            "mode" => "CARDS_SESSION_JS",
            "amount" => 0,
            "currency" => "PHP",
            "country" => "PH",
            "customer" => [
                "reference_id" => $customerId,
                "type" => "INDIVIDUAL",
                "email" => $email,
                "mobile_number" => $phoneNumber,
                "individual_detail" => [
                    "given_names" => $userName,
                ],
            ],
            "cards_session_js" => [
                "success_return_url" => "https://ecoo.ride-ph.com/success",
                "failure_return_url" => "https://ecoo.ride-ph.com/fail",
            ],
        ];


        $response = Http::withBasicAuth(env('XENDIT_SECRET_KEY'), '')
            ->post('https://api.xendit.co/sessions', $payload);

        if ($response->successful()) {
            return response()->json([
                'session_id' => $response->json()['payment_session_id']
            ]);
        } else {
            Log::error('Xendit payment request failed:', ['response' => $response->body()]);
            return response()->json(['message' => $response->body()], 200);
        }
    }

    public function removeCard(Request $request)
    {
        $request->validate([
            'id' => 'required|string'
        ]);

        $id = $request->input('id');

        $card = Card::find($id);

        if ($card) {
            $response = Http::withBasicAuth(env('XENDIT_SECRET_KEY'), '')
                ->withHeader('api-version', '2024-11-11')
                ->post('https://api.xendit.co/v3/payment_tokens/' . $card->payment_method_id . '/cancel');

            if ($response->successful()) {
                if ($card->delete()) {
                    return response()->json(['message' => 'Card deleted successfully']);
                } else {
                    return response()->json(['message' => 'Cannot delete the card'], 404);
                }
            } else {
                Log::error('Xendit payment request failed:', ['response' => $response->body()]);
                return response()->json(['message' => $response->body()]);
            }
        } else {
            return response()->json(['message' => 'Card not found'], 404);
        }
    }
}
