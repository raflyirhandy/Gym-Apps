<?php
require_once 'config/database.php';

class AdminMemberController {
    public function handleAction() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            echo "Access Denied.";
            exit();
        }

        $action = $_GET['action'] ?? '';
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header("Location: index.php?page=admin_members");
            exit();
        }

        $db = new Database();
        $conn = $db->getConnection();

        switch ($action) {
            case 'accept':
                // Update member status
                $stmt = $conn->prepare("UPDATE members SET status = 'active' WHERE id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                
                // Update payment status
                $stmt = $conn->prepare("UPDATE payments SET status = 'paid' WHERE member_id = :id ORDER BY id DESC LIMIT 1");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                
                // Ambil info member untuk dikirimi email kartu member aktif
                $stmt_info = $conn->prepare("
                    SELECT u.name, u.email, m.join_date 
                    FROM members m 
                    JOIN users u ON m.user_id = u.id 
                    WHERE m.id = :id 
                    LIMIT 1
                ");
                $stmt_info->bindParam(':id', $id);
                $stmt_info->execute();
                $member_info = $stmt_info->fetch();

                if ($member_info) {
                    // Kirim email notifikasi berupa kartu member aktif
                    require_once 'vendor/autoload.php';
                    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                    try {
                        // Server settings
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com'; 
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'raflyxlite@gmail.com'; // Email Anda
                        $mail->Password   = 'euyemyzlinirkvhg'; // App Password Gmail
                        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port       = 587;

                        // Recipients
                        $mail->setFrom('raflyxlite@gmail.com', 'Macho Gym Admin');
                        $mail->addAddress($member_info['email'], $member_info['name']);

                        // Content
                        $mail->isHTML(true);
                        $mail->Subject = 'Keanggotaan Aktif! Ini Kartu Member Anda';
                        
                        // HTML Email Template for Member Card
                        $join_date_formatted = $member_info['join_date'] ? date('d F Y', strtotime($member_info['join_date'])) : date('d F Y');
                        $mailBody = "
                        <div style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; display: flex; justify-content: center;'>
                            <div style='background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); width: 400px; border-radius: 15px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); color: white; padding: 20px; position: relative; overflow: hidden; margin: 0 auto;'>
                                <div style='text-align: center; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 10px; margin-bottom: 20px;'>
                                    <h2 style='margin: 0; font-size: 24px; letter-spacing: 2px;'>GYM FIT</h2>
                                    <p style='margin: 5px 0 0; font-size: 12px; color: #ccc;'>MEMBER CARD</p>
                                </div>
                                
                                <div style='margin-bottom: 20px;'>
                                    <p style='margin: 0; font-size: 12px; color: #ccc;'>Nama Member</p>
                                    <h3 style='margin: 5px 0 15px; font-size: 20px; text-transform: uppercase;'>" . htmlspecialchars($member_info['name']) . "</h3>
                                    
                                    <p style='margin: 0; font-size: 12px; color: #ccc;'>Email</p>
                                    <p style='margin: 5px 0 15px; font-size: 16px;'>" . htmlspecialchars($member_info['email']) . "</p>
                                    
                                    <p style='margin: 0; font-size: 12px; color: #ccc;'>Tanggal Bergabung</p>
                                    <p style='margin: 5px 0 0; font-size: 16px;'>" . $join_date_formatted . "</p>
                                </div>
                                
                                <div style='text-align: right; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 10px; margin-top: 20px;'>
                                    <p style='margin: 0; font-size: 10px; color: #ccc;'>Selamat! Keanggotaan/Membership Anda telah aktif. Tunjukkan kartu ini saat datang ke gym.</p>
                                </div>
                            </div>
                        </div>
                        ";
                        
                        $mail->Body = $mailBody;
                        $mail->send();
                        $_SESSION['success'] = "Member berhasil diaktifkan, pembayaran diverifikasi, dan email kartu member aktif telah dikirim.";
                    } catch (\Exception $e) {
                        error_log("Email gagal dikirim. Error: {$mail->ErrorInfo}");
                        $_SESSION['success'] = "Member berhasil diaktifkan dan pembayaran diverifikasi (Email kartu member gagal dikirim).";
                    }
                } else {
                    $_SESSION['success'] = "Member berhasil diaktifkan dan pembayaran diverifikasi.";
                }
                break;
                
            case 'block':
                // Block member
                $stmt = $conn->prepare("UPDATE members SET status = 'inactive' WHERE id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                
                $_SESSION['success'] = "Member berhasil diblokir (di-nonaktifkan).";
                break;
                
            case 'delete':
                // Get user_id of the member to delete the user entirely
                $stmt = $conn->prepare("SELECT user_id FROM members WHERE id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $member_data = $stmt->fetch();
                
                if ($member_data) {
                    $user_id = $member_data['user_id'];
                    // Delete from users table (this will cascade delete members and payments if FK cascade is set, otherwise delete directly)
                    $stmt = $conn->prepare("DELETE FROM users WHERE id = :user_id");
                    $stmt->bindParam(':user_id', $user_id);
                    $stmt->execute();
                    $_SESSION['success'] = "Data member berhasil dihapus permanen.";
                }
                break;
        }

        header("Location: index.php?page=admin_members");
        exit();
    }
}
?>
