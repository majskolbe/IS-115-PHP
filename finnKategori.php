<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once 'inc/KassalappApi_inc.php';

$api = new KassalappAPI(KASSALAPP_API_KEY);


class KategoriNavigator {
    private $api;
    private $kategorier = [];

    public function __construct(KassalappAPI $api) {
        $this->api = $api;
        $this->lastKategorier();
    }

    private function lastKategorier() {
        $respons = $this->api->request('categories');

        foreach ($respons['data'] as $kategori) {
            $this->kategorier[$kategori['id']] = [
                'navn' => $kategori['name'],
                'antall_produkter' => $kategori['products_count'] ?? 0
            ];
        }
    }

    public function visKategoritre() {
        echo "📂 Produktkategorier:\n";
        foreach ($this->kategorier as $id => $kategori) {
            echo "  [{$id}] {$kategori['navn']} ({$kategori['antall_produkter']} produkter)\n";
        }
    }

    public function finnKategoriId($søkeord) {
        foreach ($this->kategorier as $id => $kategori) {
            if (stripos($kategori['navn'], $søkeord) !== false) {
                return $id;
            
            }
        }
        return null;
    }
}

$navigator = new KategoriNavigator($api);
$navigator->visKategoritre();

// Finn produkter i en spesifikk kategori
$fruktKategori = $navigator->finnKategoriId('frukt');
if ($fruktKategori) {
    $fruktProdukter = $api->request('products', [
        'category_id' => $fruktKategori,
        'sort' => 'price_asc'
    ]);
}



?>