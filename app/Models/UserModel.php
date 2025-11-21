<?php

/*
Håndterer kommunikasjon med databasen
Henter og oppretter brukere, sjekekr passord
registrerer antall mislykkede innloggingsforsøk
håndterer utestenging
*/
class UserModel {

    private $db;

    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }

    /*Henter bruker gjennom brukernavn */
    public function findByUsername(string $username): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :u");
        $stmt->execute(['u' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /*Oppretter ny bruker */
    public function createUser(string $fname, string $lname, string $email, string $username, string $hash, string $role): bool {
        $stmt = $this->db->prepare("
            INSERT INTO users (fname, lname, email, username, password_hash, role)
            VALUES (:f, :l, :e, :u, :p, :r)
        ");
        return $stmt->execute([
            'f' => $fname,
            'l' => $lname,
            'e' => $email,
            'u' => $username,
            'p' => $hash,
            'r' => $role
        ]); //true ved vellykket
    }

    /*Verifiserer passord mot hash */
    public function verifyPassword(string $username, string $password): ?array {
        $user = $this->findByUsername($username);
        //returnerer bruker om bruker finnes og passord matcher hash
        return ($user && password_verify($password, $user['password_hash']))
            ? $user
            : null;
    }

    /* Registrerer mislykket innlogging */
    public function incrementFailedAttempts(string $username): void {
        //øker med 1 og setter tidspunkt
        $this->db->prepare("
            UPDATE users SET failed_attempts = failed_attempts + 1,
                             last_failed_attempt = NOW()
            WHERE username = :u
        ")->execute(['u' => $username]);
    }

    /*Nullstiller mislykkede innloggingsforsøk */
    public function resetFailedAttempts(string $username): void {
        $this->db->prepare("
            UPDATE users SET failed_attempts = 0,
                             last_failed_attempt = NULL
            WHERE username = :u
        ")->execute(['u' => $username]);
    }

    /*Sjekker om bruker er utestengt */
    public function isLockedOut(string $username): bool {
        $user = $this->findByUsername($username);
        if (!$user) return false;

        $tooMany = $user['failed_attempts'] >= 3;
        $withinHour = $user['last_failed_attempt'] &&
                      strtotime($user['last_failed_attempt']) > strtotime('-1 hour');

        //både 3 feilede forsøk og siste forsøk innen siste time = utestengt
        return $tooMany && $withinHour;
    }

    /*Henter alle brukere til adminsidens tabell */
    public function getAllUsers(): array {
        $stmt = $this->db->query("
            SELECT id, fname, lname, email, username, role
            FROM users ORDER BY fname ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>