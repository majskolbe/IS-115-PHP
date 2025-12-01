<?php
class CsrfHelper {

    /**
     * Lager en CSRF-token og lagrer den i session.
     * Brukes for å beskytte skjemaer mot angrep (Cross-Site Request Forgery).
     * Returnerer alltid den gjeldende tokenen.
     */
    public static function generateToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            // Genererer en tilfeldig streng og konverterer til hex
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Sjekker om en innsendt CSRF-token er gyldig.
     * Sammenligner tokenen fra skjemaet med den som ligger i session.
     * Returnerer true hvis de matcher, ellers false.
     */
    public static function validateToken(string $token): bool {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
