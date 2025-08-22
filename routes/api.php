<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RideController;
use App\Http\Controllers\ScooterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\XenditWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth.jwt'])->group(function () {
    // Route::post('/lock-scooter', [ScooterController::class, 'lockScooter']);
    // Route::post('/unlock-scooter', [ScooterController::class, 'unlockScooter']);
    Route::post('/start-ride', [RideController::class, 'startRide']);
    Route::post('/end-ride', [RideController::class, 'endRide']);
    Route::get('/get-ride', [RideController::class, 'getRide']);
    Route::get('/get-ride-history', [RideController::class, 'getRideHistory']);
    Route::get('/get-user-balance', [UserController::class, 'getUserBalance']);
    Route::post('/request-payment', [UserController::class, 'requestPayment']);
});

Route::post('/set-sent-location-frequency', [ScooterController::class, 'setSentLocationFrequency']);
Route::get('/online-scooters', [ScooterController::class, 'getOnlineScooters']);
Route::get('/scooter-details', [ScooterController::class, 'getScooterById']);

Route::post('/register-user', [AuthController::class, 'registerUser']);
Route::post('/login-via-email', [AuthController::class, 'loginViaEmail']);
Route::post('/request-otp', [AuthController::class, 'generateOtp']);
Route::post('/confirm-otp', [AuthController::class, 'confirmOtp']);
Route::post('/check-user', [AuthController::class, 'checkIfUserExists']);
Route::post('/auth/refresh', [AuthController::class, 'refresh']);


Route::post('/webhook/xendit/payment', [XenditWebhookController::class, 'handle']);
