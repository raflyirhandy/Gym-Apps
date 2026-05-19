<?php
require_once 'config/database.php';

class PaymentController {
    public function processPayment() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            session_start();
            $user_id = $_SESSION['user_id'];
            
            $db = new Database();
            $conn = $db->getConnection();
            
            // Get member_id
            $stmt = $conn->prepare("SELECT id FROM members WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $member_data = $stmt->fetch();
            
            if (!$member_data) {
                $_SESSION['error'] = "Data member tidak ditemukan.";
                header("Location: index.php?page=payment");
                exit();
            }
            
            $member_id = $member_data['id'];
            
            // Handle File Upload
            if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png'];
                $filename = $_FILES['proof_image']['name'];
                $filetype = $_FILES['proof_image']['type'];
                $filesize = $_FILES['proof_image']['size'];
                $tempname = $_FILES['proof_image']['tmp_name'];
                
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (!in_array($ext, $allowed)) {
                    $_SESSION['error'] = "Format file tidak didukung. Harap upload JPG atau PNG.";
                    header("Location: index.php?page=payment");
                    exit();
                }
                
                if ($filesize > 2 * 1024 * 1024) { // 2MB
                    $_SESSION['error'] = "Ukuran file terlalu besar. Maksimal 2MB.";
                    header("Location: index.php?page=payment");
                    exit();
                }
                
                // Generate unique filename
                $new_filename = "proof_" . $member_id . "_" . time() . "." . $ext;
                $upload_dir = 'assets/uploads/payments/';
                
                if (move_uploaded_file($tempname, $upload_dir . $new_filename)) {
                    // Insert into database
                    $amount = 50000.00;
                    $query = "INSERT INTO payments (member_id, amount, payment_date, status, proof_image) VALUES (:member_id, :amount, NOW(), 'pending', :proof_image)";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':member_id', $member_id);
                    $stmt->bindParam(':amount', $amount);
                    $stmt->bindParam(':proof_image', $new_filename);
                    
                    if ($stmt->execute()) {
                        echo "<script>alert('Bukti pembayaran berhasil dikirim! Tunggu verifikasi Admin.'); window.location.href='index.php?page=member_dashboard';</script>";
                        exit();
                    } else {
                        $_SESSION['error'] = "Gagal menyimpan data pembayaran.";
                        header("Location: index.php?page=payment");
                        exit();
                    }
                } else {
                    $_SESSION['error'] = "Gagal mengunggah file. Pastikan folder tujuan dapat ditulisi.";
                    header("Location: index.php?page=payment");
                    exit();
                }
            } else {
                $_SESSION['error'] = "Harap pilih file bukti transfer.";
                header("Location: index.php?page=payment");
                exit();
            }
        } else {
            header("Location: index.php?page=payment");
            exit();
        }
    }
}
?>
