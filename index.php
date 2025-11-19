<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Controllers/AuthController.php';
require_once __DIR__ . '/app/Models/UserModel.php';
require_once __DIR__ . '/app/Controllers/ChatController.php';

session_start();

// Opprett database og modeller
$pdo = (new Database())->tilkobling;
$userModel = new UserModel($pdo);
$authController = new AuthController($userModel);
$chatController = new ChatController();

// Håndter POST requests for login og register
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'register') {
        if (isset($_POST['fname']) && isset($_POST['lname']) && isset($_POST['email']) && isset($_POST['username']) && isset($_POST['password'])) {
            $authController->handleRegister($_POST['fname'], $_POST['lname'], $_POST['email'], $_POST['username'], $_POST['password']);
            exit;
        }
    } elseif (isset($_POST['username']) && isset($_POST['password'])) {
        $authController->handleLogin($_POST['username'], $_POST['password']);
        exit;
    }
}

// Finn ønsket side
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
        $authController->checkAccess(); // alle innloggede brukere
        $eanCodes = $userModel->getAllEanCodes();
        require_once __DIR__ . '/app/Views/ChatView.php';
        break;

    case 'admin':
        $authController->checkAccess('admin'); // kun admin
        $users = $userModel->getAllUsers();
        require_once __DIR__ . '/app/Views/AdminView.php';
        break;

    case 'access_denied':
        require_once __DIR__ . '/app/Views/AccessDeniedView.php';
        break;

    case 'home':
    default:
        if (!isset($_SESSION['user'])) {
            $authController->loginPage();
        } else {
            header("Location: index.php?page=chat");
            exit;
        }
}
?>
