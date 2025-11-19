<?php
class AuthController {
    private $userModel;

    public function __construct(UserModel $userModel) {
        $this->userModel = $userModel;
    }

    //funskjon for å validere brukerinput
    private function validerInput(array $data): array {
        $feil = [];

        if (!$data['fname']) $feil[] = "Fornavn må oppgis";
        if (!$data['lname']) $feil[] = "Etternavn må oppgis";
        if (!$data['epost'] || !filter_var($data['epost'], FILTER_VALIDATE_EMAIL)) $feil[] = "Ugyldig e-post";
        if (!$data['username']) $feil[] = "Brukernavn må oppgis";
        
        if (empty($data['password']) || strlen($data['password']) < 8) {
            $feil[] = "Passord må være minst 8 tegn langt";
        }
        if (!empty($data['password']) && !preg_match('/[A-Z]/', $data['password'])) {
            $feil[] = "Passord må inneholde minst en stor bokstav";
        }
        if (!empty($data['password']) && !preg_match('/[0-9]/', $data['password'])) {
            $feil[] = "Passord må inneholde minst ett siffer";
        }
        
        return $feil;
    } 
   
    public function loginPage(): void {
        include __DIR__ . '/../Views/LoginView.php';
    }

    public function registerPage(): void {
        include __DIR__ . '/../Views/RegisterView.php';
    }

    public function handleLogin(string $username, string $password): void {
        // Sjekk om brukeren eksisterer
        $userExists = $this->userModel->findByUsername($username);
        
        if (!$userExists) {
            header("Location: index.php?page=login&error=Brukeren eksisterer ikke");
            exit;
        }

        if ($this->userModel->isLockedOut($username)) {
            header("Location: index.php?page=login&error=Brukeren er midlertidig utestengt");
            exit;
        }

        $user = $this->userModel->verifyPassword($username, $password);

        if ($user) {
            $_SESSION['user'] = $user;
            $this->userModel->resetFailedAttempts($username);

            $target = ($user['role'] === 'admin') ? 'admin' : 'chat';
            header("Location: index.php?page=$target");
            exit;
        } else {
            $this->userModel->incrementFailedAttempts($username);
            header("Location: index.php?page=login&error=Feil kombinasjon av brukernavn og passord");
            exit;
        }
    }

    public function handleRegister(string $fname, string $lname, string $email, string $username, string $password): void {
        // Valider input
        $feil = $this->validerInput([
            'fname' => $fname,
            'lname' => $lname,
            'email' => $email,
            'username' => $username,
            'password' => $password
        ]);
        
        if (!empty($feil)) {
            $errorMessage = implode(", ", $feil);
            header("Location: index.php?page=register&error=" . urlencode($errorMessage));
            exit;
        }
        
        if ($this->userModel->findByUsername($username)) {
            header("Location: index.php?page=register&error=Brukernavn er allerede i bruk");
            exit;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Sjekk om e-posten inneholder @admin for å gi admin-rolle
        $role = (strpos($email, '@admin') !== false) ? 'admin' : 'user';
        
        $this->userModel->createUser($fname, $lname, $email, $username, $passwordHash, $role);

        header("Location: index.php?page=login&message=Bruker opprettet, logg inn!");
        exit;
    }

    public function logout(): void {
        session_destroy();
        header("Location: index.php?page=login&message=Du er nå logget ut");
        exit;
    }

    public function checkAccess(?string $requiredRole = null): void {
        if (!isset($_SESSION['user'])) {
            header("Location: index.php?page=login&error=Du må logge inn");
            exit;
        }

        if ($requiredRole && $_SESSION['user']['role'] !== $requiredRole) {
            header("Location: index.php?page=access_denied");
            exit;
        }
    }
}
?>
