<?php
require_once __DIR__ . '/../../config/db_connect.php';

class ChatModel {
    private $db;
    private $tableExists = true;

    public function __construct() {
        try {
            $this->db = (new Database())->tilkobling;
            $stmt = $this->db->query("SHOW TABLES LIKE 'chatbot_hints'");
            $this->tableExists = $stmt->rowCount() > 0;
        } catch (Exception $e) {
            $this->tableExists = false;
        }
    }

    // 🔍 Henter hint-svar fra chatbot_hints-tabellen
    public function getHintReply($userInput) {
        if (!$this->tableExists) {
            return null;
        }

        try {
            $stmt = $this->db->prepare("SELECT reply FROM chatbot_hints WHERE question LIKE :input");
            $likeInput = '%' . $userInput . '%';
            $stmt->bindParam(':input', $likeInput, PDO::PARAM_STR);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['reply'] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    //  Ny funksjon: Hent pris for EAN hos spesifikk butikk
    public function getPriceByEANAndStore($ean, $store) {
        try {
            $query = "SELECT price FROM products WHERE ean = :ean AND store = :store LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':ean', $ean);
            $stmt->bindParam(':store', $store);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['price'] : null;
        } catch (Exception $e) {
            return null;
        }
    }
}
?>
