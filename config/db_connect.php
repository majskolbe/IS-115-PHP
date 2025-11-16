<?php
class Database{
    private $servernavn = "localhost";
    private $brukernavn = "root";
    private $passord = "";
    private $dbnavn = "prosjekt";
    public $tilkobling;

    public function __construct(){
        $this->tilkobling = new mysqli($this->servernavn, $this->brukernavn, $this->passord, $this->dbnavn);
        if ($this->tilkobling->connect_error) {
            throw new Exception("Database connection failed: " . $this->tilkobling->connect_error);
        }
    }
}


?>
