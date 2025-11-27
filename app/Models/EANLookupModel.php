<?php
//inkluderer API klassen
require_once __DIR__ . '/KassalappAPI.php';

//Klasse med ansvar for å hente og formatere produktinformasjon
class EANLookupModel {
    private $api;

    //mottar KassalappAPI-instans, og lagrer den
    public function __construct(KassalappAPI $api){
        $this->api = $api;
    }

    //henter produkti
    public function skannProdukt(string $ean){
        try {
            //henter produktinfo fra API basert på EAN
            $produkt = $this->api->request("products/ean/{$ean}");

            //returnerer null om det ikke er tilgjengelig data
            if (empty($produkt['data'])) {
                return null;
            }

            //kaller på formaterProduktInfo()
            return $this->formaterProduktInfo($produkt['data']);
        } catch (Exception $e) {
            //returnerer null ved feil
            return null;
        }
    }

    
    //henter ut relevant data om produktet
    private function hentGenerellInfo($data){
        if (empty($data['products']) || !is_array($data['products'])) {
            return null;
        }

        return [
            'ean' => $data['ean'] ?? null,
            'navn' => $data['products'][0]['name'] ?? 'Ukjent produkt',
            'merke' => $data['products'][0]['brand'] ?? 'Ukjent merke',
            'bilde' => $data['products'][0]['image'] ?? null,
            'beskrivelse' =>$data['products'][0]['description'] ?? null
        ];
    }


    //henter pris for produktet
    private function hentPris($data){
        $priser = [];

        foreach ($data['products'] as $produkt) {
            $pris = $produkt['current_price']['price'] ?? null;
            $butikk = $produkt['store']['name'] ?? null;

            if (empty($pris) || empty($butikk)) continue;

            $priser[] = [
                'butikk' => $butikk,
                'pris' => $pris
            ];
        }

        return $priser;
    }

    //formaterer infoen om produktet til strukturert array
    private function formaterProduktInfo($data) {
        $info = $this->hentGenerellInfo($data);
        if ($info === null) return null;

        $info['priser'] = $this->hentPris($data);
        usort($info['priser'], fn($a, $b) => $a['pris'] <=> $b['pris']);

        return $info;
    }

    //billigst pris = element 0 i prislisten
    public function finnBilligstePris(array $priser){
        return $priser[0] ?? null;
    }

    //returnerer resterende innhold av listen, uten element 0
    public function hentAndrePriser(array $priser){
        return count($priser) > 1 ? array_slice($priser, 1) : [];
    }
}
