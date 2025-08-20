<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XenditWebhookController extends Controller
{
    public function handle(Request $request, PushNotificationService $pushNotificationService)
    {

        // Get the payload from Xendit
        $payload = $request->all();


        if ($payload['event'] === 'payment.capture') {
            $status = $payload['data']['status'];
            $referenceId = $payload['data']['reference_id'];
            $amount = $payload['data']['request_amount'];
            preg_match('/topup_user_(\d+)_\d+/', $referenceId, $matches);
            $userId = $matches[1];
            $user = User::find($userId);

            if ($status === 'SUCCEEDED') {
                if (isset($matches[1])) {


                    if ($user) {
                        if ($user->debt > 0) {
                            $payable = $amount - $user->debt;
                            $user->debt = 0; // debt cleared
                        } else {
                            $payable = $amount;
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
            } else {
                $pushNotificationService->sendPushNotification(
                    $user->device_token,
                    'Payment Received',
                    'Payment failed for your account.',
                    ['paymentSuccess' => true]
                );
            }
        }

        // Always return 200 OK so Xendit knows webhook is received
        return response()->json(['success' => true]);
    }
}
