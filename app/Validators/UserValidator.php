<?php
/**
 * UserValidator
 * -------------------------
 * Ansvar: Validerer input ved registrering.
 */
class UserValidator {
    private $userModel;

    public function __construct(UserModel $userModel) {
        $this->userModel = $userModel;
    }

    public function validateRegister(string $fname, string $lname, string $email, string $username, string $password): array {
        $errors = [];

        if (empty($fname)) $errors[] = "Fornavn må oppgis";
        if (empty($lname)) $errors[] = "Etternavn må oppgis";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Ugyldig e-post";
        if (empty($username)) $errors[] = "Brukernavn må oppgis";

        if (strlen($password) < 8) $errors[] = "Passord må være minst 8 tegn";
        if (!preg_match('/[A-Z]/', $password)) $errors[] = "Passord må inneholde en stor bokstav";
        if (!preg_match('/[0-9]/', $password)) $errors[] = "Passord må inneholde et tall";

        if ($this->userModel->findByUsername($username)) {
            $errors[] = "Brukernavn er allerede i bruk";
        }

        return $errors;
    }
}
