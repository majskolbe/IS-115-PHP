<?php
/*
Klasse med ansvar for frontend visning.
Tar imot data fra ChatService via ChatController og formaterer
data til HTML som vises i chatten.
*/
class ChatResponseView {
    public static function render(string $intent, array $data): string {
        switch ($intent) {
            case 'store_price_lookup':
                return self::renderStorePrice($data);
            case 'product_description':
                return self::renderDescription($data);
            case 'ean_lookup':
                return self::renderEANLookup($data);
            default:
                return self::renderFallback($data);
        }
    }

    //formaterer prisoppslag på spesifikk butikk
    private static function renderStorePrice(array $data): string {
        if (isset($data['error'])) {
            return "<p>{$data['error']}</p>";
        }

        if (!isset($data['ean'], $data['store'], $data['result']['prices'])) {
            return "<p>Ugyldig data for butikkoppslag.</p>";
        }

        $ean = htmlspecialchars($data['ean']);
        $store = htmlspecialchars($data['store']);
        $prices = $data['result']['prices'];

        foreach ($prices as $price) {
            if (strcasecmp($price['store'], $store) === 0) {
                $amount = htmlspecialchars($price['price']);
                return "<p>Prisen på EAN <strong>$ean</strong> hos <strong>$store</strong> er <strong>$amount kr</strong>.</p>";
            }
        }

        return "<p>Beklager, jeg fant ingen pris for EAN <strong>$ean</strong> hos <strong>$store</strong>.</p>";
    }

    //formaterer produktbeskrivelse
    private static function renderDescription(array $data): string {
        if (isset($data['error'])) {
            return "<p>{$data['error']}</p>";
        }

        $description = htmlspecialchars($data['result']['description'] ?? '');
        if (!$description) {
            return "<p>Beklager, jeg fant ingen beskrivelse for EAN.</p>";
        }

        return "<div class=\"product-info\"><p>$description</p></div>";
    }

    //formaterer EAN-oppslag med billigste pris og alternative priser
    private static function renderEANLookup(array $data): string {
        if (isset($data['error'])) {
            return "<p>{$data['error']}</p>";
        }

        $name = htmlspecialchars($data['name'] ?? 'Ukjent');
        $brand = htmlspecialchars($data['brand'] ?? '');
        $image = $data['image'] ?? null;
        $lowestPrice = $data['prices'][0] ?? null;
        $alternativePrices = array_slice($data['prices'], 1);

        $html = "<div class='ean-result'>";

        if ($image) {
            $html .= "<img src='$image' alt='Produktbilde' class='product-image' />";
        }

        if ($lowestPrice) {
            $price = htmlspecialchars($lowestPrice['price']);
            $store = htmlspecialchars($lowestPrice['store']);
            $html .= "<p class='price-best'>Billigste pris for $name er <strong>$price kr</strong> hos <strong>$store</strong>.</p>";
        }

        if (!empty($alternativePrices)) {
            $html .= "Her er også prisene på andre butikker:<ul class='price-list'>";
            foreach ($alternativePrices as $price) {
                $store = htmlspecialchars($price['store']);
                $amount = htmlspecialchars($price['price']);
                $html .= "<li><strong>$store</strong> – $amount kr</li>";
            }
            $html .= "</ul>";
        }

        $html .= "</div>";
        return $html;
    }

    private static function renderFallback(array $data): string {
        $reply = $data['reply'] ?? "Beklager, jeg forstod ikke helt.";
        return "<p>$reply</p>";
    }
}
