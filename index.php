<?php
session_start();

// Autoload (Composer eller egen autoloader)
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';

// Controllers
require_once __DIR__ . '/app/Controllers/AuthController.php';
require_once __DIR__ . '/app/Controllers/ChatController.php';

// Models
require_once __DIR__ . '/app/Models/UserModel.php';
require_once __DIR__ . '/app/Models/InfoPrintModel.php';

// Helpers & Services
require_once __DIR__ . '/app/Helpers/RedirectHelper.php';
require_once __DIR__ . '/app/Helpers/CsrfHelper.php';
require_once __DIR__ . '/app/Validators/UserValidator.php';
require_once __DIR__ . '/app/Services/AccessControl.php';

// Opprett database og modeller
$pdo = (new Database())->tilkobling;
$userModel = new UserModel($pdo);
$infoModel = new InfoPrintModel($pdo);

// Opprett controllers
$authController = new AuthController($userModel);
$chatController = new ChatController();

// Hent meldinger fra URL (gjøres tilgjengelig for views)
$error = $_GET['error'] ?? null;
$message = $_GET['message'] ?? null;

// Håndter POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF-sjekk
    $token = $_POST['csrf_token'] ?? '';
    if (!CsrfHelper::validateToken($token)) {
        RedirectHelper::to("login", "error", "Ugyldig forespørsel (CSRF)");
    }

    // Registrering
    if (isset($_POST['action']) && $_POST['action'] === 'register') {
        $authController->handleRegister(
            $_POST['fname'] ?? '',
            $_POST['lname'] ?? '',
            $_POST['email'] ?? '',
            $_POST['username'] ?? '',
            $_POST['password'] ?? ''
        );
        exit;
    }

    // Innlogging
    if (isset($_POST['username'], $_POST['password'])) {
        $authController->handleLogin($_POST['username'], $_POST['password']);
        exit;
    }

    // Logout (via POST)
    if (isset($_GET['page']) && $_GET['page'] === 'logout') {
        $authController->logout();
        exit;
    }
}

// Router for å finne riktig side
$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'login':
        $error = $_GET['error'] ?? null;
        $message = $_GET['message'] ?? null;
        require __DIR__ . '/app/Views/LoginView.php';
        break;

    case 'register':
        $error = $_GET['error'] ?? null;
        $message = $_GET['message'] ?? null;
        require __DIR__ . '/app/Views/RegisterView.php';
        break;


    case 'logout':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!CsrfHelper::validateToken($token)) {
                RedirectHelper::to("login", "error", "Ugyldig forespørsel (CSRF)");
            }
            $authController->logout();
        } else {
            RedirectHelper::to("login", "error", "Ugyldig forespørsel (må bruke POST)");
        }
        break;


    case 'chat':
        $authController->requireLogin();
        $eanCodes = $infoModel->getAllEanCodes();
        $exampleQuestions = $infoModel->getAllExampleQuestions();
        require __DIR__ . '/app/Views/ChatView.php';
        break;

    case 'admin':
        $authController->requireRole("admin");
        $users = $userModel->getAllUsers();
        $username = $_SESSION['user']['username'] ?? '';
        require __DIR__ . '/app/Views/AdminView.php';
        break;

    default:
    case 'home':
        if (empty($_SESSION['user'])) {
            $authController->loginPage();
        } else {
            header("Location: index.php?page=chat");
            exit;
        }
        break;
}
