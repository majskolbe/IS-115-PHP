<?php
/*
Klasse med ansvar for å kontrollere tilgang basert på session og rolle
 */
class AccessControl {
    public function requireLogin(): void {
        if (!isset($_SESSION['user'])) {
            RedirectHelper::to("login", "error", "Du må logge inn");
        }
    }

    public function requireRole(string $role): void {
        $this->requireLogin();
        if ($_SESSION['user']['role'] !== $role) {
            RedirectHelper::to("chat", "error", "Du har ikke tilgang til denne siden");
        }
    }
}
