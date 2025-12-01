<?php
/*
Hjelpeklasse knyttet til meldingsbehandling
*/
class MessageUtils {

    //fjerner overflødig whitespace for enklere prossesering
    public static function normalize(string $message): string {
        return trim(preg_replace('/\s+/', ' ', $message));
    }

    //trekker ut EAN, søker etter 13-sifret kode i tekststreng
    public static function extractEAN(string $text): ?string {
        return preg_match('/\b\d{13}\b/', $text, $matches) ? $matches[0] : null;
    }
}
