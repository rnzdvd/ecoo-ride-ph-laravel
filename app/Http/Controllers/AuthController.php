<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Ride;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;


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
        // Validate email
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'device_token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'fill required fields [email, device_token]'], 422);
        }

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

        // Return user data with token
        return response()->json([
            'user' => $user,
            'token' => $token
        ], 200);
    }
}
