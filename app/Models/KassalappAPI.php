<?php
//laster inn nødvendige avhengigheter via Composer
require __DIR__ . '/../../vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;


/*
Klasse med ansvar for kommunikasjonen med kassalapp sin API.
Bruker guzzle for å gjøre API-kall enklere
*/
class KassalappAPI {
    private $client;
    private $apiKey;
    private const BASE_URL = 'https://kassal.app/api/v1/';

    //initilaliserer HTTP-klienten med base-URL og nødvendige headers
    public function __construct(string $apiKey) {
        $this->apiKey = $apiKey;

        //oppretter gussle-klient med standardinstillinger
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

    
    //Sender GET-forespørsler til gitt endepunkt med valgfri query-parametere
    public function request(string $endpoint, array $params = []) {
        try {
            //utfrer GET-forespørsel med eventuelle parametere
            $response = $this->client->get($endpoint, ['query' => $params]);
            
            //returnerer JSON-respons som array
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            
            //håndterer feil med egen metode
            $this->handleError($e);
        }
    }

    //håndterer API-feil
    private function handleError(RequestException $e) {
        if ($e->hasResponse()) {
            $statusCode = $e->getResponse()->getStatusCode();
            $body = json_decode($e->getResponse()->getBody(), true);

            //kaster spesifikke feilmeldinger basert på statuskoden
            switch ($statusCode) {
                case 401: throw new Exception('Ugyldig API-nøkkel');
                case 429: throw new Exception('For mange forespørsler - vent litt');
                case 404: throw new Exception('Ressursen ble ikke funnet');
                default: throw new Exception($body['message'] ?? 'En feil oppstod');
            }
        }
        //generell feilmelding om ingen respons er tilgjengelig
        throw new Exception('Kunne ikke koble til API-et');
    }
}
