<?php
//inkluderer API klassen
require_once __DIR__ . '/KassalappAPI.php';

//Klasse med ansvar for å hente og formatere produktinformasjon
class StrekkkodeScanner {
    private $api;

    //mottar KassalappAPI-instans, og lagrer den
    public function __construct(KassalappAPI $api) {
        $this->api = $api;
    }

    //tar inn EAN-kode
    public function skannProdukt(string $ean) {
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

    //formaterer rådata om produkt til strukturert array
    private function formaterProduktInfo($data) {
        //sjekker at produktlista finnes og er en array
        if (empty($data['products']) || !is_array($data['products'])) {
            return null;
        }

        //henter generell produktinfo fra første produkt i lista
        $info = [
            'ean' => $data['ean'] ?? null,
            'navn' => $data['products'][0]['name'] ?? 'Ukjent produkt',
            'merke' => $data['products'][0]['brand'] ?? 'Ukjent merke',
            'beskrivelse' => $data['products'][0]['description'] ?? 'Ingen beskrivelse',
            'bilde' => $data['products'][0]['image'] ?? null,
            'priser' => []
        ];

        //henter pris og butikkinfo for alle produktene
        foreach ($data['products'] as $produkt) {
            $pris = $produkt['current_price']['price'] ?? null;
            $butikk = $produkt['store']['name'] ?? null;

            //hopper over produkter uten pris eller butikknavn
            if (empty($pris) || empty($butikk)) continue;

            //legger til prisinformasjon i lista
            $info['priser'][] = [
                'butikk' => $butikk,
                'pris' => $pris,
                'url' => $produkt['url'] ?? null,
            ];
        }

        //sorterer prislisten fra lav til høy pris
        usort($info['priser'], fn($a, $b) => $a['pris'] <=> $b['pris']);

        return $info;
    }

    public function finnBilligstePris(array $priser){
        return $priser[0] ?? null;
    }

    public function hentAndrePriser(array $priser){
        return count($priser) > 1 ? array_slice($priser, 1) : [];
    }
}
