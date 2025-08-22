<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XenditWebhookController extends Controller
{
    public function handle(Request $request, PushNotificationService $pushNotificationService)
    {
        $payload = $request->all();
        Log::info('Xendit Webhook Payload: ', $payload);

        $status = $payload['data']['status'];
        $referenceId = $payload['data']['reference_id'];
        $amount = $payload['data']['request_amount'];
        $channelCode = $payload['data']['channel_code'];
        $failureReason = $payload['data']['failure_code'] ?? 'no failure reason';

        $deductedAmount = floor($amount / 100) * 100;
        preg_match('/topup_user_(\d+)_\d+/', $referenceId, $matches);
        $userId = $matches[1];
        $netSale = 0;
        $user = User::find($userId);

        if ($channelCode === 'GCASH') {
            $netSale = $amount - $amount * 0.023; // 2.3% fee
        } else if ($channelCode === 'PAYMAYA') {
            $netSale = $amount - $amount * 0.02; // 2% fee
        } else if ($channelCode === 'CREDIT_CARD') {
        }

        if ($status === 'SUCCEEDED') {
            if (isset($matches[1])) {
                if ($user) {
                    if ($user->debt > 0) {
                        $payable = $deductedAmount - $user->debt;
                        $user->debt = 0; // debt cleared
                    } else {
                        $payable = $deductedAmount;
                    }

                    // Add remaining to balance
                    $user->balance += $payable;
                    $user->save();

                    $pushNotificationService->sendPushNotification(
                        $user->device_token,
                        'Payment Received',
                        '₱' . $payable . ' is succesfully added to your account.',
                        ['paymentSuccess' => true]
                    );
                }
            }
        } else if ($status === 'FAILED') {
            $pushNotificationService->sendPushNotification(
                $user->device_token,
                'Payment Failed',
                'Payment failed for your account.',
                ['paymentSuccess' => false]
            );
        }

        Sale::create([
            'user_id' => $userId,
            'amount' => $netSale,
            'payment_method' => $channelCode,
            'status' => $status,
            'reference_id' => $referenceId,
            'failure_reason' => $failureReason
        ]);


        // Always return 200 OK so Xendit knows webhook is received
        return response()->json(['success' => true]);
    }
}
