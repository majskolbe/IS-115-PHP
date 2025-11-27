<?php
require_once __DIR__ . '/../../config/db_connect.php';

class ChatModel {
    private $db;

    public function __construct() {
        $this->db = (new Database())->tilkobling;
    }

    // Matcher brukerinput mot pattern-regex i databasen
   public function getResponseByPattern($userInput) {
        try {
            $stmt = $this->db->query("SELECT intent, pattern, response FROM chat_responses");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $pattern = '/' . $row['pattern'] . '/iu';

                if (preg_match($pattern, $userInput)) {
                    // RIKTIG: returner selve svaret, ikke intent-navnet
                    return $row['response'];
                }
            }

            return null;
        } catch (Exception $e) {
            return null;
        }
    }


    public function detectIntentByPattern($userInput) {
        //hardkodet inn matching for produktbeskrivelse
        if (preg_match('/(?:hva er|beskriv|beskrivelse av|info om|fortell om)\s*(\d{13})/iu', $userInput)) {
            return 'product_description';
        }

        $stmt = $this->db->query("SELECT intent, pattern FROM chat_responses WHERE pattern IS NOT NULL");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $regex = '/' . $row['pattern'] . '/iu';
            if (preg_match($regex, $userInput)) {
                return $row['intent'];
            }
        }

        // fallback EAN
        if (preg_match('/\b\d{13}\b/', $userInput)) {
            return 'ean_lookup';
        }

        return 'unknown';
    }



    public function getPatternByIntent(string $intent): ?string {
        try {
            $stmt = $this->db->prepare("SELECT pattern FROM chat_responses WHERE intent = :intent LIMIT 1");
            $stmt->execute(['intent' => $intent]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['pattern'] ?? null;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getResponseByIntent(string $intent): ?string {
        try {
            $stmt = $this->db->prepare("SELECT response FROM chat_responses WHERE intent = :intent LIMIT 1");
            $stmt->execute(['intent' => $intent]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['response'] ?? null;
        } catch (Exception $e) {
            return null;
        }
    }





}

?>
