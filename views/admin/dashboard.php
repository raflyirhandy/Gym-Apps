<?php
require_once 'views/layouts/header.php';
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Get stats
$stmt = $conn->query("SELECT COUNT(*) as total FROM members");
$total_members = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM trainers");
$total_trainers = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM classes");
$total_classes = $stmt->fetch()['total'];

// Create non_member_payments if it doesn't exist (fallback safety)
$conn->exec("
    CREATE TABLE IF NOT EXISTS `non_member_payments` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `amount` decimal(10,2) NOT NULL,
        `payment_date` date NOT NULL,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// Calculate Revenues
$stmt = $conn->query("SELECT SUM(amount) as total FROM payments WHERE status = 'paid'");
$membership_revenue = $stmt->fetch()['total'] ?? 0;

$stmt = $conn->query("SELECT SUM(amount) as total, COUNT(*) as count FROM non_member_payments");
$non_member_data = $stmt->fetch();
$non_membership_revenue = $non_member_data['total'] ?? 0;
$total_non_members = $non_member_data['count'] ?? 0;

$grand_total_revenue = $membership_revenue + $non_membership_revenue;
?>

<h2 style="margin-bottom: 20px;">Dasbor Admin</h2>

<div class="grid-cards" style="margin-bottom: 25px;">
    <div class="stat-card glass">
        <div class="stat-info">
            <h3><?php echo $total_members; ?></h3>
            <p>Total Anggota</p>
        </div>
        <div class="stat-icon">
            <i class="fa fa-users" style="color: var(--primary);"></i>
        </div>
    </div>
    
    <div class="stat-card glass">
        <div class="stat-info">
            <h3><?php echo $total_trainers; ?></h3>
            <p>Total Pelatih</p>
        </div>
        <div class="stat-icon">
            <i class="fa fa-dumbbell" style="color: #60a5fa;"></i>
        </div>
    </div>
    
    <div class="stat-card glass">
        <div class="stat-info">
            <h3><?php echo $total_non_members; ?></h3>
            <p>Pengunjung Harian</p>
        </div>
        <div class="stat-icon">
            <i class="fa fa-walking" style="color: #f43f5e;"></i>
        </div>
    </div>
    
    <div class="stat-card glass" style="border-bottom: 4px solid #10b981;">
        <div class="stat-info">
            <h3>Rp <?php echo number_format($grand_total_revenue, 0, ',', '.'); ?></h3>
            <p>Total Pendapatan</p>
            <span style="font-size: 11px; color: var(--text-muted); display: block; margin-top: 4px;">
                Member: Rp <?php echo number_format($membership_revenue, 0, ',', '.'); ?> | 
                Harian: Rp <?php echo number_format($non_membership_revenue, 0, ',', '.'); ?>
            </span>
        </div>
        <div class="stat-icon">
            <i class="fa fa-coins" style="color: #10b981;"></i>
        </div>
    </div>
</div>

<div class="glass" style="padding: 20px;">
    <h3 style="margin-bottom: 15px;">Anggota Terbaru</h3>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Tanggal Bergabung</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->query("SELECT u.name, u.email, m.status, m.join_date FROM members m JOIN users u ON m.user_id = u.id ORDER BY m.id DESC LIMIT 5");
                while ($row = $stmt->fetch()):
                ?>
                <tr>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td>
                        <span class="badge <?php echo $row['status'] == 'active' ? 'badge-success' : 'badge-danger'; ?>">
                            <?php echo ucfirst($row['status']); ?>
                        </span>
                    </td>
                    <td><?php echo $row['join_date']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
