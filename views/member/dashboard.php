<?php
require_once 'views/layouts/header.php';
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Get member_id and details
$stmt = $conn->prepare("
    SELECT m.id, m.status, m.join_date, u.phone, u.address 
    FROM members m
    JOIN users u ON m.user_id = u.id
    WHERE m.user_id = :user_id
");
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$member_data = $stmt->fetch();
$member_id = $member_data ? $member_data['id'] : null;
$member_status = $member_data ? $member_data['status'] : 'inactive';
$join_date = $member_data ? date('d M Y', strtotime($member_data['join_date'])) : '-';
$phone = $member_data ? $member_data['phone'] : '-';
$address = $member_data ? $member_data['address'] : '-';
$member_display_id = "MEM" . ($member_data ? date('Ymd', strtotime($member_data['join_date'])) : date('Ymd')) . str_pad($member_id, 4, '0', STR_PAD_LEFT);

if (!$member_id) {
    echo "<div class='alert alert-danger'>Profil anggota tidak ditemukan.</div>";
    require_once 'views/layouts/footer.php';
    exit;
}

// Get Booked Classes
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM bookings WHERE member_id = :member_id AND status = 'booked'");
$stmt->bindParam(':member_id', $member_id);
$stmt->execute();
$total_booked = $stmt->fetch()['total'];

?>

<h2 style="margin-bottom: 20px;">Dasbor Anggota</h2>

<div class="glass" style="padding: 20px; margin-bottom: 30px; border-top: 5px solid #dc3545;">
    <h3 style="margin-bottom: 15px; color: #dc3545;">Status Keanggotaan</h3>
    <div style="font-size: 16px; margin-bottom: 15px;">
        <p><strong>ID Member:</strong> <?php echo $member_display_id; ?></p>
        <p style="margin-top: 10px;">Status: 
            <?php if ($member_status == 'active'): ?>
                <span style="background-color: #198754; color: white; padding: 3px 8px; border-radius: 4px; font-size: 14px; font-weight: bold;">Aktif</span>
            <?php else: ?>
                <span style="background-color: #ffc107; color: black; padding: 3px 8px; border-radius: 4px; font-size: 14px; font-weight: bold;">Tidak Aktif</span>
            <?php endif; ?>
        </p>
        <p style="margin-top: 10px;"><strong>No HP:</strong> <?php echo htmlspecialchars($phone); ?></p>
        <p style="margin-top: 10px;"><strong>Alamat:</strong> <?php echo htmlspecialchars($address); ?></p>
        <p style="margin-top: 10px; color: var(--text-muted); font-size: 14px;">Bergabung sejak: <?php echo $join_date; ?></p>
    </div>
    
    <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 15px 0;">
    
    <?php if ($member_status != 'active'): ?>
        <div style="background-color: rgba(255, 193, 7, 0.2); border: 1px solid #ffc107; padding: 15px; border-radius: 5px; margin-bottom: 15px; color: #eab308;">
            Membership Anda belum aktif. Silakan lakukan pembayaran atau upload bukti transfer.
        </div>
        <a href="index.php?page=payment" class="btn btn-primary">Perpanjang / Bayar Membership</a>
    <?php endif; ?>
</div>

<div class="grid-cards">
    <div class="stat-card glass">
        <div class="stat-info">
            <h3><?php echo $total_booked; ?></h3>
            <p>Kelas Dipesan</p>
        </div>
        <div class="stat-icon">
            <i class="fa fa-calendar-check"></i>
        </div>
    </div>
    
    <div class="stat-card glass">
        <div class="stat-info">
            <h3><a href="index.php?page=member_progress" style="color:inherit">Lihat</a></h3>
            <p>Perkembangan Saya</p>
        </div>
        <div class="stat-icon">
            <i class="fa fa-chart-line"></i>
        </div>
    </div>
    
    <div class="stat-card glass">
        <div class="stat-info">
            <h3><a href="index.php?page=chat" style="color:inherit">Obrolan</a></h3>
            <p>Hubungi Pelatih</p>
        </div>
        <div class="stat-icon">
            <i class="fa fa-comments"></i>
        </div>
    </div>
</div>

<div class="glass" style="padding: 20px; margin-bottom: 30px;">
    <h3 style="margin-bottom: 15px;">Kelas Dipesan Mendatang</h3>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama Kelas</th>
                    <th>Jadwal</th>
                    <th>Pelatih</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->prepare("
                    SELECT b.id as booking_id, c.name, c.schedule, tu.name as trainer_name
                    FROM bookings b
                    JOIN classes c ON b.class_id = c.id
                    JOIN trainers t ON c.trainer_id = t.id
                    JOIN users tu ON t.user_id = tu.id
                    WHERE b.member_id = :member_id AND b.status = 'booked' AND c.schedule > NOW()
                    ORDER BY c.schedule ASC LIMIT 5
                ");
                $stmt->bindParam(':member_id', $member_id);
                $stmt->execute();
                while ($row = $stmt->fetch()):
                ?>
                <tr>
                    <td><strong><?php echo $row['name']; ?></strong></td>
                    <td><?php echo date('d M Y, H:i', strtotime($row['schedule'])); ?></td>
                    <td><?php echo $row['trainer_name']; ?></td>
                    <td><button class="btn btn-outline" style="padding: 5px 10px; font-size:12px;">Batal</button></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
