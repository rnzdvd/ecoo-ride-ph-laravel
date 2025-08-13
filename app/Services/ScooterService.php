<?php

namespace App\Services;

use GuzzleHttp\Client;

class ScooterService
{
    protected $http;

    public function __construct()
    {
        $this->http = new Client([
            'timeout' => 60, // optional timeout to avoid hanging
        ]);
    }

    public function lockScooter($id)
    {
        if (!$id) {
            throw new \InvalidArgumentException('Missing scooter ID');
        }

        try {
            $response = $this->http->post("http://178.128.24.61:30001/api/scooters/lock/{$id}");
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            return ['success' => false, 'action' => 'lock'];
        }
    }

    public function unlockScooter($id)
    {
        if (!$id) {
            throw new \InvalidArgumentException('Missing scooter ID');
        }

        try {
            $response = $this->http->post("http://178.128.24.61:30001/api/scooters/unlock/{$id}");
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            return ['success' => false, 'action' => 'unlock'];
        }
    }
}
