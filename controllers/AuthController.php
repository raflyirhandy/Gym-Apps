<?php
require_once 'models/User.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'login') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $user = new User();
        $email = $_POST['email'];
        $password = $_POST['password'];

        if ($user->login($email, $password)) {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_name'] = $user->name;
            $_SESSION['role'] = $user->role;

            header("Location: index.php?page=" . $user->role . "_dashboard");
            exit();
        } else {
            $_SESSION['error'] = "Email atau kata sandi tidak valid.";
            header("Location: index.php?page=login");
            exit();
        }
    }
} elseif ($action == 'register') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $user = new User();
        $user->name = $_POST['name'];
        $user->email = $_POST['email'];
        $user->phone = $_POST['phone'] ?? null;
        $user->address = $_POST['address'] ?? null;
        $user->password = $_POST['password'];
        $user->role = 'member'; // Default role is member

        if ($user->register()) {
            // Also add to members table
            require_once 'config/database.php';
            $db = new Database();
            $conn = $db->getConnection();
            $stmt = $conn->prepare("INSERT INTO members (user_id, join_date, status) VALUES (:user_id, NOW(), 'inactive')");
            $stmt->bindParam(':user_id', $user->id);
            $stmt->execute();

            $_SESSION['success'] = "Pendaftaran berhasil. Silakan masuk, upload bukti pembayaran pada dashboard member Anda, lalu tunggu konfirmasi admin.";
            header("Location: index.php?page=login");
            exit();
        } else {
            $_SESSION['error'] = "Email sudah ada atau pendaftaran gagal.";
            header("Location: index.php?page=register");
            exit();
        }
    }
} elseif ($action == 'logout' || (isset($_GET['page']) && $_GET['page'] == 'logout')) {
    session_destroy();
    header("Location: index.php?page=login");
    exit();
}
?>
