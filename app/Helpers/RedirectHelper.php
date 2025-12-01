<?php
/*
Klasse med ansvar for å håndtere HTTP-redirects med melding(valgfri)
*/
class RedirectHelper {
    public static function to(string $page, string $type = null, string $message = null): void {
        $url = "index.php?page=$page";
        if ($type && $message) {
            $url .= "&$type=" . urlencode($message);
        }
        header("Location: $url");
        exit;
    }
}
