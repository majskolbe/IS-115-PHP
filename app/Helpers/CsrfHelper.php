<?php
/*
klasse med ansvar for å generere og validere CSRF-token
*/
class CsrfHelper {
    //genererer og returnerer en CSRF-token
    public static function generateToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            // Genererer en tilfeldig streng og konverterer til hex
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    //validerer en CSRF-token fra forespørselen
    public static function validateToken(string $token): bool {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
