<?php

namespace App\Services;

use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Configuration;
use Brevo\Client\Model\SendSmtpEmail;
use GuzzleHttp\Client;

class OtpEmailService
{
    public function sendOtpEmail(string $recipientEmail, string $otp)
    {
        $config = Configuration::getDefaultConfiguration()
            ->setApiKey('api-key', env('BREVO_API_KEY'));

        $apiInstance = new TransactionalEmailsApi(
            new Client(),
            $config
        );

        $sendSmtpEmail = new SendSmtpEmail([
            'subject' => 'Your OTP Code',
            'sender' => ['name' => 'Ecoo Ride PH', 'email' => 'renzdavidplanos@gmail.com'],
            'to' => [
                ['email' => $recipientEmail, 'name' => $recipientEmail],
            ],
            'htmlContent' => '
                <div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
                    <h2 style="color: #2E86C1;">Ecoo Ride PH - OTP Verification</h2>
                    <p>Hello,</p>
                    <p>We received a request to verify your email address. Please use the code below to complete your verification:</p>
                    <p style="font-size: 20px; font-weight: bold; color: #2E86C1; background: #f4f4f4; padding: 10px; display: inline-block; border-radius: 5px;">
                        ' . $otp . '
                    </p>
                    <p>This code will expire in 5 minutes for security reasons.</p>
                    <p>If you did not request this code, please ignore this email.</p>
                    <br>
                    <p>Best regards,<br><strong>Ecoo Ride PH Team</strong></p>
                </div>
            '
        ]);

        return $apiInstance->sendTransacEmail($sendSmtpEmail);
    }
}
