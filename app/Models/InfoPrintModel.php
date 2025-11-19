<?php
class InfoPrintModel {
    private $db;

    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }

    /**
     * Hent alle EAN-produkter fra databasen
     */
    public function getAllEanCodes(): array {
        try {
            $stmt = $this->db->prepare("SELECT id, product_name, ean_code FROM ean_products ORDER BY product_name ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Hent alle eksempelspørsmål fra databasen
     */
    public function getAllExampleQuestions(): array {
        try {
            $stmt = $this->db->prepare("SELECT id, question FROM questions ORDER BY id ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>
