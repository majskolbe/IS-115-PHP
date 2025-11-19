<?php

class UserModel {
    private $db;

    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }

    public function findByUsername(string $username): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function createUser(string $fname, string $lname, string $email, string $username, string $passwordHash, string $role = 'user'): bool {
        $stmt = $this->db->prepare("INSERT INTO users (fname, lname, email, username, password_hash, role) VALUES (:fname, :lname, :email, :username, :password_hash, :role)");
        return $stmt->execute([
            'fname' => $fname,
            'lname' => $lname,
            'email' => $email,
            'username' => $username,
            'password_hash' => $passwordHash,
            'role' => $role
        ]);
    }

    public function verifyPassword(string $username, string $password): ?array {
        $user = $this->findByUsername($username);
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return null;
    }

    public function incrementFailedAttempts(string $username): void {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET failed_attempts = failed_attempts + 1, last_failed_attempt = NOW() 
            WHERE username = :username
        ");
        $stmt->execute(['username' => $username]);
    }

    public function resetFailedAttempts(string $username): void {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET failed_attempts = 0, last_failed_attempt = NULL 
            WHERE username = :username
        ");
        $stmt->execute(['username' => $username]);
    }

    public function isLockedOut(string $username): bool {
        $user = $this->findByUsername($username);
        if (!$user) return false;

        $tooManyAttempts = $user['failed_attempts'] >= 3;
        $withinHour = $user['last_failed_attempt'] && strtotime($user['last_failed_attempt']) > strtotime('-1 hour');

        return $tooManyAttempts && $withinHour;
    }

    public function getAllUsers(): array {
        $stmt = $this->db->prepare("SELECT id, fname, lname, email, username, role FROM users ORDER BY fname, lname");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllEanCodes(): array {
        try {
            $stmt = $this->db->prepare("SELECT id, product_name, ean_code FROM ean_products ORDER BY product_name ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Hvis tabellen ikke finnes, returner tom liste
            return [];
        }
    }

    public function getAllExampleQuestions(): array {
    $stmt = $this->db->prepare("SELECT question FROM questions ORDER BY id ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
?>