<?php
class Database {
    private $servernavn = "localhost";
    private $brukernavn = "root";
    private $passord = "";
    private $dbnavn = "prosjekt";
    public $tilkobling;

    public function __construct() {
        try {
            $dsn = "mysql:host={$this->servernavn};dbname={$this->dbnavn};charset=utf8mb4";
            $this->tilkobling = new PDO($dsn, $this->brukernavn, $this->passord);
            $this->tilkobling->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
}
?>
