<?php
/*
Klasse med ansvar for å håndtere HTTP-redirects med melding(valgfri)
bygger url og bruker header for å sende HTTP-redirect
*/
class RedirectHelper {
    public static function to(string $page, string $type = null, string $message = null): void {
        $url = "index.php?page=$page"; //basis-url
        //hvis type og mld er satt legges de til som query-parametere
        if ($type && $message) {
            $url .= "&$type=" . urlencode($message);
        }
        header("Location: $url");
        exit;
    }
}
