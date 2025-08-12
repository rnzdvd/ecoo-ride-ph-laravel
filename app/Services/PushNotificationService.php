<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    protected $expoPushUrl = 'https://exp.host/--/api/v2/push/send';

    /**
     * Send a push notification to an Expo push token.
     *
     * @param string $expoPushToken
     * @param string $title
     * @param string $body
     * @param array $data Optional extra data payload
     * @return array|null
     */
    public function sendPushNotification(string $expoPushToken, string $title, string $body, array $data = [])
    {
        $payload = [
            'to' => $expoPushToken,
            'title' => $title,
            'body' => $body,
            'data' => (object) $data, // cast array to object to make sure it sends JSON object, not array
        ];

        $response = Http::post($this->expoPushUrl, $payload);

        if ($response->successful()) {
            return $response->json();
        } else {
            // You can log the error or throw exception
            Log::error('Expo Push failed: ' . $response->body());
            return null;
        }
    }
}
