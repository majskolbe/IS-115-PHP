<?php


class UserModel {
    public function findByUsername($username) { /* ... */ }
    public function verifyPassword($username, $password) { /* ... */ }
    public function incrementFailedAttempts($username) { /* ... */ }
    public function resetFailedAttempts($username) { /* ... */ }
    public function isLockedOut($username) { /* ... */ }
}

?>