<?php
require_once __DIR__ . '/../config/config.php';
require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class KassalappAPI {
    private $client;
    private $apiKey;
    private const BASE_URL = 'https://kassal.app/api/v1/';

    public function __construct(string $apiKey) {
        $this->apiKey = $apiKey;
        $this->client = new Client([
            'base_uri' => self::BASE_URL,
            'timeout'  => 10.0,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    public function request(string $endpoint, array $params = []) {
        try {
            $response = $this->client->get($endpoint, [
                'query' => $params
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            $this->handleError($e);
        }
    }

    private function handleError(RequestException $e) {
        if ($e->hasResponse()) {
            $statusCode = $e->getResponse()->getStatusCode();
            $body = json_decode($e->getResponse()->getBody(), true);

            switch ($statusCode) {
                case 401:
                    throw new Exception('Ugyldig API-nøkkel');
                case 429:
                    throw new Exception('For mange forespørsler - vent litt');
                case 404:
                    throw new Exception('Ressursen ble ikke funnet');
                default:
                    throw new Exception($body['message'] ?? 'En feil oppstod');
            }
        }
        throw new Exception('Kunne ikke koble til API-et');
    }
}
