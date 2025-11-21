<?php
/*
Håndterer innlogging, registrering, utlogging og tilgangssjekk.
Kommuniserer med UserModel for databaseoperasjoner.
*/
class AuthController {

    private $userModel;

    public function __construct(UserModel $userModel) {
        $this->userModel = $userModel;
    }

    //Hjelpefunksjon for redirect med valgfri melding
    private function redirect(string $page, string $type = null, string $message = null): void {
        //setter opp url for redirect
        $url = "index.php?page=$page";
        if ($type && $message) {
            $url .= "&$type=" . urlencode($message);
        }
        header("Location: $url");
        exit; //stopper scriptet
    }

    //Validerer input ved registrering    
    private function validateInput(array $data, ?UserModel $userModel = null): array {
        $errors = [];

        if (empty($data['fname'])) $errors[] = "Fornavn må oppgis";
        if (empty($data['lname'])) $errors[] = "Etternavn må oppgis";
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
            $errors[] = "Ugyldig e-post";
        }
        if (empty($data['username'])) $errors[] = "Brukernavn må oppgis";

        $pw = $data['password'] ?? '';
        if (strlen($pw) < 8) $errors[] = "Passord må være minst 8 tegn";
        if (!preg_match('/[A-Z]/', $pw)) $errors[] = "Passord må inneholde en stor bokstav";
        if (!preg_match('/[0-9]/', $pw)) $errors[] = "Passord må inneholde et tall";

        // Sjekk om brukernavn allerede eksisterer
        if ($userModel && !empty($data['username'])) {
            if ($userModel->findByUsername($data['username'])) {
                $errors[] = "Brukernavn er allerede i bruk";
            }
        }

        return $errors;
    }

    //Viser views
    public function loginPage(): void { include __DIR__ . '/../Views/LoginView.php'; }
    public function registerPage(): void { include __DIR__ . '/../Views/RegisterView.php'; }
    //void = ingen returverdi, kun kandling
   
    //Håndterer innlogging
    public function handleLogin(string $username, string $password): void {
        //henter bruker fra db basert på brukernavn
        $user = $this->userModel->findByUsername($username);

        //sjekker om bruker eksisterer
        if (!$user) {
            $this->redirect("login", "error", "Brukeren eksisterer ikke");
        }

        //sjekker om bruker er utestengt
        if ($this->userModel->isLockedOut($username)) {
            $this->redirect("login", "error", "For mange innloggingsforsøk. Du er utestengt i en time.");
        }

        //verifiserer passord
        if (!$this->userModel->verifyPassword($username, $password)) {
            //hvis feil øker mislykkede forsøk
            $this->userModel->incrementFailedAttempts($username);
            $this->redirect("login", "error", "Feil brukernavn eller passord");
        }

        // Gyldig login, lagrer brukerdata i session
        $_SESSION['user'] = $user;
        //nullstiller mislykkede login-forsøk
        $this->userModel->resetFailedAttempts($username);

        //bestemmer hvilken side brukeren kommer til basert på rolle
        $page = ($user['role'] === 'admin') ? "admin" : "chat";
        $this->redirect($page);
    }

    //Håndterer registrering
    public function handleRegister(string $fname, string $lname, string $email, string $username, string $password): void {

        //validerer input
        $errors = $this->validateInput([
            'fname' => $fname,
            'lname' => $lname,
            'email' => $email,
            'username' => $username,
            'password' => $password
        ], $this->userModel);

        //skriver ut feilmeldingene fra validering
        if (!empty($errors)) {
            $this->redirect("register", "error", implode(", ", $errors));
        }

        //krypterer passord med hashing
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        //setter bruker som admin om mail inneholder @admin
        $role = str_contains($email, "@admin") ? "admin" : "user";

        //lagrer i databasen og redirect til login
        $this->userModel->createUser($fname, $lname, $email, $username, $passwordHash, $role);
        $this->redirect("login", "message", "Bruker opprettet! Logg inn.");
    }

    //logger ut bruker ved å slette session
    public function logout(): void {
        session_destroy();
        $this->redirect("login", "message", "Du er nå logget ut");
    }

    //kalles på beskyttede sider. gir kun tilgang om bruker er logget in
    public function requireLogin(): void {
        if (!isset($_SESSION['user'])) {
            $this->redirect("login", "error", "Du må logge inn");
        }
    }

    //sjekker tilgang til side etter rolle
    public function requireRole(string $role): void {
        $this->requireLogin();

        if ($_SESSION['user']['role'] !== $role) {
            $this->redirect("chat", "error", "Du har ikke tilgang til denne siden");
        }
    }
}

?>