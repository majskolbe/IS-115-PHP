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
        //sjekker intent
        $stmt = $this->db->query("SELECT intent, pattern FROM chat_responses WHERE pattern IS NOT NULL");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            if (preg_match('/' . $row['pattern'] . '/iu', $userInput)) {
                return $row['intent'];
            }
        }

        // Deretter: sjekk EAN som fallback-intent
        if (preg_match('/\b\d{13}\b/', $userInput)) {
            return 'ean_lookup';
        }

        return 'unknown';
    }




}

?>
