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
}

?>

