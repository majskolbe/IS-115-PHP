<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Controllers/AuthController.php';
require_once __DIR__ . '/app/Controllers/ChatController.php';
require_once __DIR__ . '/app/Models/UserModel.php';
require_once __DIR__ . '/app/Models/InfoPrintModel.php';

session_start();

// Opprett database og modeller
$pdo = (new Database())->tilkobling;
$userModel = new UserModel($pdo);
$infoModel = new InfoPrintModel($pdo);
$authController = new AuthController($userModel);
$chatController = new ChatController();

// Håndter POST requests for login og register
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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
}

//router for å finne riktig side
$page = $_GET['page'] ?? 'home';

switch ($page) {

    case 'login':
        $authController->loginPage();
        break;

    case 'register':
        $authController->registerPage();
        break;

    case 'logout':
        $authController->logout();
        break;

    case 'chat':
        // krever innlogging for tilgang
        $authController->requireLogin();

        // Data til ChatView sidebarene
        $eanCodes = $infoModel->getAllEanCodes();
        $exampleQuestions = $infoModel->getAllExampleQuestions();

        require __DIR__ . '/app/Views/ChatView.php';
        break;

    case 'admin':
        //kun admin-brukere
        $authController->requireRole("admin");

        $users = $userModel->getAllUsers();
        require __DIR__ . '/app/Views/AdminView.php';
        break;

    default:
    case 'home':
        // ikke innlogget -> send til login
        if (!isset($_SESSION['user'])) {
            $authController->loginPage();
        } else {
            header("Location: index.php?page=chat");
            exit;
        }
        break;
}
?>
