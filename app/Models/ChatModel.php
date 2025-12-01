<?php
require_once __DIR__ . '/../../config/db_connect.php';

/*
Klasse med ansvar for å koble chatbot-logikk til db.
matcher brukerinput, identifiserer intents og returnerer passende svar
*/
class ChatModel {
    private $db;

    // Konstanter for gjenbrukbare regex
    private const EAN_REGEX = '/\b\d{13}\b/';
    private const PRODUCT_DESC_REGEX = '/(?:hva er|beskriv|beskrivelse av|info om|fortell om)\s*(\d{13})/iu';

    public function __construct() {
        try {
            $this->db = (new Database())->tilkobling;
        } catch (Exception $e) {
            error_log("Database connection failed: " . $e->getMessage());
            $this->db = null;
        }
    }

    //hente en enkelt verdi fra db
    private function fetchSingleValue(string $sql, array $params, string $column): ?string {
        if (!$this->db) return null;
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row[$column] ?? null;
        } catch (Exception $e) {
            error_log("Database query failed: " . $e->getMessage());
            return null;
        }
    }

    //henter alle patterns og responses fra db
    private function getAllPatterns(): array {
        if (!$this->db) return [];
        try {
            $stmt = $this->db->query("SELECT intent, pattern, response FROM chat_responses");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("getAllPatterns failed: " . $e->getMessage());
            return [];
        }
    }

    //matcher brukerinput mot regex-mønstere lagret i db
    public function getResponseByPattern(string $userInput): ?string {
        foreach ($this->getAllPatterns() as $row) {
            if (preg_match('/' . $row['pattern'] . '/iu', $userInput)) {
                return $row['response'];
            }
        }
        return null;
    }

    //oppdager intent basert på regex-mønstere
    public function detectIntentByPattern(string $userInput): string {
        if (!$this->db) return 'unknown';

        // Hardkodet regel for produktbeskrivelse
        if (preg_match(self::PRODUCT_DESC_REGEX, $userInput)) {
            return 'product_description';
        }

        // Matcher mot patterns fra databasen
        foreach ($this->getAllPatterns() as $row) {
            if (!empty($row['pattern']) && preg_match('/' . $row['pattern'] . '/iu', $userInput)) {
                return $row['intent'];
            }
        }

        // Fallback: sjekk om input inneholder en EAN-kode
        if (preg_match(self::EAN_REGEX, $userInput)) {
            return 'ean_lookup';
        }

        return 'unknown';
    }

    //henter regex-pattern basert på intent
    public function getPatternByIntent(string $intent): ?string {
        return $this->fetchSingleValue(
            "SELECT pattern FROM chat_responses WHERE intent = :intent LIMIT 1",
            ['intent' => $intent],
            'pattern'
        );
    }

    //henter response basert på intent
    public function getResponseByIntent(string $intent): ?string {
        return $this->fetchSingleValue(
            "SELECT response FROM chat_responses WHERE intent = :intent LIMIT 1",
            ['intent' => $intent],
            'response'
        );
    }
}
?>
