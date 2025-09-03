<?php

namespace App\Console\Commands;

use App\Models\Ride;
use App\Services\DistanceService;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class ProcessRideDistance extends Command implements ShouldQueue
{

    protected $signature = 'distance:process-rides-distance';
    protected $description = 'Dispatch process rides distance all active rides';

    protected $distanceService;

    public function __construct(DistanceService $distanceService)
    {
        parent::__construct();
        $this->distanceService = $distanceService;
    }

    public function handle()
    {
        $rides = Ride::where('status', 'active')->get();

        if ($rides->isEmpty()) {
            return;
        }
        $scooterIds = $rides->pluck('scooter_id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        $client = new Client();
        $response = $client->request('POST', "http://178.128.24.61:30001/api/get-scooters-location", [
            'json' => ['ids' => $scooterIds],
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        foreach ($rides as $ride) {
            $scooterId = $ride->scooter_id;
            $location = collect($data)->firstWhere('id', $scooterId);
            $oldLat = $ride->curr_lat;
            $oldLng = $ride->curr_lng;
            $newLat = $location['lat'];
            $newLng = $location['lng'];

            $calculatedDistance = $this->distanceService->haversine($oldLat, $oldLng, $newLat, $newLng);
            // $calculatedDistance = $this->distanceService->haversine($oldLat, $oldLng, 7.096754, 125.598058);
            $ride->total_distance += $calculatedDistance;
            $ride->curr_lat = $newLat;
            $ride->curr_lng = $newLng;

            $ride->save();
        }
    }
}
