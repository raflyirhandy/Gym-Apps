<?php
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();
$user_id = $_SESSION['user_id'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'get_users') {
    // Get users that can be chatted with (Trainers for Member, Members for Trainer)
    if ($_SESSION['role'] == 'member') {
        $stmt = $conn->prepare("SELECT id, name FROM users WHERE role = 'trainer'");
    } else {
        $stmt = $conn->prepare("SELECT id, name FROM users WHERE role = 'member'");
    }
    $stmt->execute();
    echo json_encode($stmt->fetchAll());
}
elseif ($action == 'get_messages') {
    $contact_id = isset($_GET['contact_id']) ? $_GET['contact_id'] : 0;
    
    $stmt = $conn->prepare("
        SELECT * FROM messages 
        WHERE (sender_id = :u AND receiver_id = :c) 
           OR (sender_id = :c AND receiver_id = :u)
        ORDER BY created_at ASC
    ");
    $stmt->execute(['u' => $user_id, 'c' => $contact_id]);
    
    // Mark as read
    $upd = $conn->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = :u AND sender_id = :c");
    $upd->execute(['u' => $user_id, 'c' => $contact_id]);
    
    echo json_encode($stmt->fetchAll());
}
elseif ($action == 'send_message') {
    $data = json_decode(file_get_contents('php://input'), true);
    $contact_id = isset($data['contact_id']) ? $data['contact_id'] : 0;
    $message = isset($data['message']) ? htmlspecialchars($data['message']) : '';
    
    if ($contact_id && $message) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (:u, :c, :m)");
        if ($stmt->execute(['u' => $user_id, 'c' => $contact_id, 'm' => $message])) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to send']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    }
}
?>
