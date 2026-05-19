<?php
require_once 'config/database.php';

class MemberClassController {
    public function handleAction() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'member') {
            echo "Access Denied.";
            exit();
        }

        $action = $_GET['action'] ?? '';
        $class_id = $_GET['class_id'] ?? null;
        $user_id = $_SESSION['user_id'];

        if (!$class_id) {
            header("Location: index.php?page=member_classes");
            exit();
        }

        $db = new Database();
        $conn = $db->getConnection();

        // Get member ID
        $stmt = $conn->prepare("SELECT id FROM members WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $member_data = $stmt->fetch();
        
        if (!$member_data) {
            $_SESSION['error'] = "Data member tidak ditemukan.";
            header("Location: index.php?page=member_classes");
            exit();
        }
        $member_id = $member_data['id'];

        switch ($action) {
            case 'book':
                // Check if class is full
                $stmt = $conn->prepare("
                    SELECT c.capacity, (SELECT COUNT(*) FROM bookings b WHERE b.class_id = c.id AND b.status='booked') as booked_count 
                    FROM classes c WHERE c.id = :class_id
                ");
                $stmt->bindParam(':class_id', $class_id);
                $stmt->execute();
                $class_data = $stmt->fetch();

                if ($class_data && $class_data['booked_count'] < $class_data['capacity']) {
                    // Check if already booked
                    $check_stmt = $conn->prepare("SELECT id FROM bookings WHERE class_id = :class_id AND member_id = :member_id AND status = 'booked'");
                    $check_stmt->bindParam(':class_id', $class_id);
                    $check_stmt->bindParam(':member_id', $member_id);
                    $check_stmt->execute();
                    
                    if ($check_stmt->rowCount() == 0) {
                        // Insert booking
                        $insert_stmt = $conn->prepare("INSERT INTO bookings (class_id, member_id, status) VALUES (:class_id, :member_id, 'booked')");
                        $insert_stmt->bindParam(':class_id', $class_id);
                        $insert_stmt->bindParam(':member_id', $member_id);
                        $insert_stmt->execute();
                        $_SESSION['success'] = "Berhasil memesan kelas.";
                    } else {
                        $_SESSION['error'] = "Anda sudah memesan kelas ini sebelumnya.";
                    }
                } else {
                    $_SESSION['error'] = "Kelas sudah penuh.";
                }
                break;

            case 'cancel':
                // Cancel booking
                $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE class_id = :class_id AND member_id = :member_id");
                $stmt->bindParam(':class_id', $class_id);
                $stmt->bindParam(':member_id', $member_id);
                $stmt->execute();
                $_SESSION['success'] = "Pesanan kelas berhasil dibatalkan.";
                break;
        }

        header("Location: index.php?page=member_classes");
        exit();
    }
}
?>
