<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use App\Services\OtpEmailService;
use Carbon\Carbon;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function registerUser(Request $request)
    {
        // Check if email or phone number exists
        $exists = User::where('email', $request->email)
            ->orWhere('phone_number', $request->phone_number)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Email or phone number already used'
            ], 409);
        }

        // Create user using Eloquent
        $user = User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'balance' => 0,
        ]);

        return response()->json($user, 201);
    }

    public function loginViaEmail(Request $request)
    {

        $request->validate([
            'email' => 'required',
            'device_token' => 'required'

        ]);

        // Find user by email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'email not found'], 404);
        }

        // Update device_token
        $user->device_token = $request->device_token;
        $user->save(); // will only update changed fields

        // Generate JWT token
        $token = JWTAuth::fromUser($user);

        // Attach token to the user array
        $userArray = $user->toArray();
        $userArray['access_token'] = $token;

        unset($userArray['device_token'], $userArray['balance'], $userArray['debt']);

        // Return user data with token
        return response()->json([
            'user' => $userArray,
        ], 200);
    }


    public function generateOtp(Request $request, OtpEmailService $otpEmailService)
    {
        $request->validate([
            'email' => 'required',
        ]);

        // Delete old OTPs for this email
        Otp::where('email', $request->email)->delete();

        // Create OTP
        $otp = rand(100000, 999999);
        $now = Carbon::now();

        try {
            $otpEmailService->sendOtpEmail($request->email, $otp);

            // If email sent successfully, save OTP to DB
            Otp::create([
                'email' => $request->email,
                'otp' => $otp,
                'time_sent' => $now,
                'expires_at' => $now->copy()->addMinutes(5),
            ]);

            return response()->json(['message' => 'OTP sent successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send OTP',
                'error'   => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'OTP sent successfully',
        ]);
    }

    public function confirmOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp'   => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $record = Otp::where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP',
            ], 400);
        }

        if (Carbon::now()->greaterThan($record->expires_at)) {
            $record->delete();

            return response()->json([
                'success' => false,
                'message' => 'OTP expired',
            ], 400);
        }

        $record->delete();

        return response()->json([
            'success' => true,
            'message' => 'Success',
        ]);
    }

    public function checkIfUserExists(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $exists = User::where('email', $request->email)->exists();

        return response()->json([
            'exist' => $exists
        ]);
    }

    public function refresh()
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());
            return response()->json([
                'access_token' => $newToken,
            ]);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token refresh failed'], 401);
        }
    }
}
