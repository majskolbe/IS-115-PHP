<?php
require_once __DIR__ . '/../../config/db_connect.php';

class ChatModel {
    private $db;
    private $tableExists = true;

    public function __construct() {
        try {
            $this->db = (new Database())->tilkobling;
            // Check if table exists
            $result = $this->db->query("SHOW TABLES LIKE 'chatbot_hints'");
            if (!$result || $result->num_rows == 0) {
                $this->tableExists = false;
            }
        } catch (Exception $e) {
            $this->tableExists = false;
        }
    }

    public function getHintReply($userInput) {
        if (!$this->tableExists) {
            return null; // Table doesn't exist, return null to allow EAN-based search
        }
        
        try {
            $stmt = $this->db->prepare("SELECT reply FROM chatbot_hints WHERE question LIKE ?");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->db->error);
            }
            
            $likeInput = '%' . $userInput . '%';
            $stmt->bind_param("s", $likeInput);
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            return ($result->num_rows > 0) ? $result->fetch_assoc()['reply'] : null;
        } catch (Exception $e) {
            return null; // Silently return null if query fails
        }
    }
}
?>

