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

class ScooterController extends Controller
{
    public function getOnlineScooters(): JsonResponse
    {
        $client = new Client();

        $response = $client->request('GET', 'http://178.128.24.61:30001/api/scooters');

        $body = $response->getBody();
        $data = json_decode($body, true);

        return response()->json([
            'data' => $data,
        ]);
    }

    public function getScooterById(Request $request): JsonResponse
    {

        $request->validate([
            'id' => 'required',
        ]);

        $id = $request->query('id');

        $client = new Client();

        try {
            $response = $client->request('GET', "http://178.128.24.61:30001/api/scooters/{$id}");
            $data = json_decode($response->getBody(), true);
            return response()->json($data);
        } catch (ClientException $e) {
            $responseBody = $e->getResponse()->getBody()->getContents();
            $errorData = json_decode($responseBody, true);

            return response()->json($errorData, 200);
        } catch (\Exception $e) {
            // For any other unexpected errors
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function lockScooter(Request $request): JsonResponse
    {

        $request->validate([
            'id' => 'required',
        ]);

        $id = $request->query('id');

        $client = new Client();

        try {
            $response = $client->request('POST', "http://178.128.24.61:30001/api/scooters/lock/{$id}");
            $data = json_decode($response->getBody(), true);
            return response()->json($data);
        } catch (ClientException $e) {
            $responseBody = $e->getResponse()->getBody()->getContents();
            $errorData = json_decode($responseBody, true);

            return response()->json($errorData, 200);
        } catch (\Exception $e) {
            // For any other unexpected errors
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function unlockScooter(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required',
        ]);

        $id = $request->query('id');

        $client = new Client();

        try {
            $response = $client->request('POST', "http://178.128.24.61:30001/api/scooters/unlock/{$id}");
            $data = json_decode($response->getBody(), true);
            return response()->json($data);
        } catch (ClientException $e) {
            $responseBody = $e->getResponse()->getBody()->getContents();
            $errorData = json_decode($responseBody, true);

            return response()->json($errorData, 200);
        } catch (\Exception $e) {
            // For any other unexpected errors
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function setSentLocationFrequency(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required',
            'frequency' => 'required|integer|min:1',
        ]);


        $id = $request->query('id');
        $frequency = $request->query('frequency');

        $client = new Client();

        try {
            $response = $client->request('POST', "http://178.128.24.61:30001/api/scooters/location-frequency/{$id}?frequency={$frequency}");
            $data = json_decode($response->getBody(), true);
            return response()->json($data);
        } catch (ClientException $e) {
            $responseBody = $e->getResponse()->getBody()->getContents();
            $errorData = json_decode($responseBody, true);

            return response()->json($errorData, 200);
        } catch (\Exception $e) {
            // For any other unexpected errors
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }
}
