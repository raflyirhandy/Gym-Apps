<?php
session_start();
require_once 'config/database.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Protect routes
$protected_routes = [
    'admin_dashboard' => 'admin',
    'admin_members' => 'admin',
    'admin_trainers' => 'admin',
    'admin_classes' => 'admin',
    'admin_non_member_payments' => 'admin',
    'admin_member_action' => 'admin',
    'trainer_dashboard' => 'trainer',
    'trainer_members' => 'trainer',
    'trainer_programs' => 'trainer',
    'member_dashboard' => 'member',
    'member_classes' => 'member',
    'member_progress' => 'member',
    'payment' => 'member',
    'process_payment' => 'member',
    'member_book_class' => 'member',
    'chat' => ['trainer', 'member']
];

if (array_key_exists($page, $protected_routes)) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php?page=login");
        exit();
    }
    $allowed_roles = is_array($protected_routes[$page]) ? $protected_routes[$page] : [$protected_routes[$page]];
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        echo "Access Denied. You do not have permission to view this page.";
        exit();
    }
}

// Route to correct view or controller
switch ($page) {
    case 'home':
        require 'views/home/landing.php';
        break;
    case 'login':
        require 'views/auth/login.php';
        break;
    case 'register':
        require 'views/auth/register.php';
        break;
    case 'logout':
        require 'controllers/AuthController.php'; 
        break;
    case 'auth_action':
        require 'controllers/AuthController.php';
        break;
        
    // Admin Routes
    case 'admin_dashboard':
        require 'views/admin/dashboard.php';
        break;
    case 'admin_members':
        require 'views/admin/members.php';
        break;
    case 'admin_trainers':
        require 'views/admin/trainers.php';
        break;
    case 'admin_classes':
        require 'views/admin/classes.php';
        break;
    case 'admin_non_member_payments':
        require 'views/admin/non_member_payments.php';
        break;
    case 'admin_member_action':
        require 'controllers/AdminMemberController.php';
        $controller = new AdminMemberController();
        $controller->handleAction();
        break;

    // Trainer Routes
    case 'trainer_dashboard':
        require 'views/trainer/dashboard.php';
        break;
    case 'trainer_members':
        require 'views/trainer/members.php';
        break;
    case 'trainer_programs':
        require 'views/trainer/programs.php';
        break;

    // Member Routes
    case 'member_dashboard':
        require 'views/member/dashboard.php';
        break;
    case 'member_classes':
        require 'views/member/classes.php';
        break;
    case 'member_progress':
        require 'views/member/progress.php';
        break;
    case 'payment':
        require 'views/member/payment.php';
        break;
    case 'process_payment':
        require 'controllers/PaymentController.php';
        $controller = new PaymentController();
        $controller->processPayment();
        break;
    case 'member_book_class':
        require 'controllers/MemberClassController.php';
        $controller = new MemberClassController();
        $controller->handleAction();
        break;

    // Chat
    case 'chat':
        require 'views/chat/index.php';
        break;
        
    // AJAX Controllers
    case 'api_progress':
        require 'controllers/ProgressController.php';
        break;
    case 'api_chat':
        require 'controllers/MessageController.php';
        break;
    case 'api_class':
        require 'controllers/ClassController.php';
        break;

    default:
        echo "<h1>404 Not Found</h1>";
        break;
}
?>
