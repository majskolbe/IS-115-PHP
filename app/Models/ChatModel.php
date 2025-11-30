<?php
require_once __DIR__ . '/../../config/db_connect.php';

class ChatModel {
    private $db;

    public function __construct() {
        try {
            $this->db = (new Database())->tilkobling;
        } catch (Exception $e) {
            error_log("Database connection failed: " . $e->getMessage());
            $this->db = null;
        }
    }

    // Matcher brukerinput mot pattern-regex i databasen
    public function getResponseByPattern($userInput) {
        if (!$this->db) return null;

        try {
            $stmt = $this->db->query("SELECT intent, pattern, response FROM chat_responses");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $pattern = '/' . $row['pattern'] . '/iu';
                if (preg_match($pattern, $userInput)) {
                    return $row['response'];
                }
            }
            return null;
        } catch (Exception $e) {
            error_log("getResponseByPattern failed: " . $e->getMessage());
            return null;
        }
    }

    public function detectIntentByPattern($userInput) {
        if (!$this->db) return 'unknown';

        // Hardkodet matching for produktbeskrivelse
        if (preg_match('/(?:hva er|beskriv|beskrivelse av|info om|fortell om)\s*(\d{13})/iu', $userInput)) {
            return 'product_description';
        }

        try {
            $stmt = $this->db->query("SELECT intent, pattern FROM chat_responses WHERE pattern IS NOT NULL");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $regex = '/' . $row['pattern'] . '/iu';
                if (preg_match($regex, $userInput)) {
                    return $row['intent'];
                }
            }
        } catch (Exception $e) {
            error_log("detectIntentByPattern failed: " . $e->getMessage());
            return 'unknown';
        }

        // fallback EAN
        if (preg_match('/\b\d{13}\b/', $userInput)) {
            return 'ean_lookup';
        }

        return 'unknown';
    }

    public function getPatternByIntent(string $intent): ?string {
        if (!$this->db) return null;

        try {
            $stmt = $this->db->prepare("SELECT pattern FROM chat_responses WHERE intent = :intent LIMIT 1");
            $stmt->execute(['intent' => $intent]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['pattern'] ?? null;
        } catch (Exception $e) {
            error_log("getPatternByIntent failed: " . $e->getMessage());
            return null;
        }
    }

    public function getResponseByIntent(string $intent): ?string {
        if (!$this->db) return null;

        try {
            $stmt = $this->db->prepare("SELECT response FROM chat_responses WHERE intent = :intent LIMIT 1");
            $stmt->execute(['intent' => $intent]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['response'] ?? null;
        } catch (Exception $e) {
            error_log("getResponseByIntent failed: " . $e->getMessage());
            return null;
        }
    }
}
?>
