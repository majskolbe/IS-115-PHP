<?php
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Validators/UserValidator.php';
require_once __DIR__ . '/../Helpers/RedirectHelper.php';
require_once __DIR__ . '/../Services/AccessControl.php';
require_once __DIR__ . '/../Helpers/CsrfHelper.php';

/*
Klasse med ansvar for å håndtere innlogging, registrering og utlogging.
Bruker UserModel til db-operasjoner, UserValidator for input-sjekk,
RedirectHelper for navigasjon og AccessControl for tilgangskontroll.
*/

class AuthController {
    private $userModel;
    private $validator;
    private $accessControl;

    public function __construct(UserModel $userModel) {
        $this->userModel = $userModel;
        $this->validator = new UserValidator($userModel);
        $this->accessControl = new AccessControl();
    }

    //Viser login-siden 
    public function loginPage(): void {
        include __DIR__ . '/../Views/LoginView.php';
    }

    //Viser register-siden
    public function registerPage(): void {
        include __DIR__ . '/../Views/RegisterView.php';
    }

    //sjekker CSRF-token for å forhindre falske forespørseler
    private function checkCsrf(): void {
        $token = $_POST['csrf_token'] ?? '';
        if (!CsrfHelper::validateToken($token)) {
            RedirectHelper::to("login", "error", "Ugyldig forespørsel (CSRF)");
        }
    }

    //Håndterer innlogging
    public function handleLogin(string $username, string $password): void {
        $this->checkCsrf();
        //Henter brukeren fra databasen, basert på brukernavn
        $user = $this->userModel->findByUsername($username);
        //Sjekker om bruken finnes
        if (!$user) {
            RedirectHelper::to("login", "error", "Brukeren eksisterer ikke");
        }
        //Sjekker om brukeren er utestengt, for mange forsøk utestenger i en time
        if ($this->userModel->isLockedOut($username)) {
            RedirectHelper::to("login", "error", "For mange innloggingsforsøk. Du er utestengt i en time.");
        }
        //Verifiserer passordet mot databasen
        if (!$this->userModel->verifyPassword($username, $password)) {
            $this->userModel->incrementFailedAttempts($username);
            RedirectHelper::to("login", "error", "Feil brukernavn eller passord");
        }
        session_regenerate_id(true);
        $_SESSION['user'] = $user;



        //Gyldig innlogging: lagrer brukerdata i session
        $_SESSION['user'] = $user;
        //Nullstiller misslykkede innloggingsforsøk hvis det har vært noen
        $this->userModel->resetFailedAttempts($username);
        //Sender brukeren til riktig sted, basert på rolle
        $page = ($user['role'] === 'admin') ? "admin" : "chat";
        RedirectHelper::to($page);
    }

    //Håndterer registrering
    public function handleRegister(string $fname, string $lname, string $email, string $username, string $password): void {
        $this->checkCsrf(); //CSRF-beskyttelse

        //Validerer input (sjekker felter, e-post, passordkrav, brukernavn)
        $errors = $this->validator->validateRegister($fname, $lname, $email, $username, $password);
        //hvis validering feiler: redirect med feilmelding
        if (!empty($errors)) {
            RedirectHelper::to("register", "error", implode(", ", $errors));
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $role = str_contains($email, "@admin") ? "admin" : "user";
        //Oppretter en ny bruker i databasen
        $this->userModel->createUser($fname, $lname, $email, $username, $passwordHash, $role);
        //Sender til login-siden med bekreftelsesmelding
        RedirectHelper::to("login", "message", "Bruker opprettet! Logg inn.");
    }

    //Logger ut bruker og avslutter session
    public function logout(): void {
        // Tøm alle session-variabler
        $_SESSION = [];

        // Slett session-cookien hvis den brukes
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Ødelegg selve sessionen
        session_destroy();

        // Redirect til login med melding
        RedirectHelper::to("login", "message", "Du er nå logget ut");
    }

    //Tilgangskontroller
    public function requireLogin(): void {
        $this->accessControl->requireLogin();
    }
    public function requireRole(string $role): void {
        $this->accessControl->requireRole($role);
    }
}
