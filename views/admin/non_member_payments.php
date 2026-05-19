<?php
require_once 'views/layouts/header.php';
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Create table if it doesn't exist
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

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add_non_member') {
        $name = $_POST['name'];
        $amount = $_POST['amount'];
        $payment_date = $_POST['payment_date'] ? $_POST['payment_date'] : date('Y-m-d');

        $stmt = $conn->prepare("INSERT INTO non_member_payments (name, amount, payment_date) VALUES (:name, :amount, :payment_date)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':payment_date', $payment_date);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Pembayaran harian pengunjung berhasil dicatat.";
        } else {
            $_SESSION['error'] = "Gagal mencatat pembayaran harian.";
        }
        echo "<script>window.location.href='index.php?page=admin_non_member_payments';</script>";
        exit();
    } elseif ($_POST['action'] == 'delete_non_member') {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM non_member_payments WHERE id = :id");
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Data kunjungan harian berhasil dihapus.";
        } else {
            $_SESSION['error'] = "Gagal menghapus data kunjungan harian.";
        }
        echo "<script>window.location.href='index.php?page=admin_non_member_payments';</script>";
        exit();
    }
}

// Fetch stats
$count_stmt = $conn->query("SELECT COUNT(*) as total, SUM(amount) as total_revenue FROM non_member_payments");
$stats = $count_stmt->fetch();
$total_visits = $stats['total'] ?? 0;
$total_revenue = $stats['total_revenue'] ?? 0;
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Pembayaran Non-Membership (Harian)</h2>
    <button class="btn btn-primary" onclick="document.getElementById('addNonMemberForm').style.display = 'block';"><i class="fa fa-plus"></i> Catat Pembayaran Baru</button>
</div>

<?php if(isset($_SESSION['success'])): ?>
    <div class="alert alert-success" style="padding:10px; background: rgba(34, 197, 94, 0.2); border: 1px solid #22c55e; border-radius: 5px; color: #86efac; margin-bottom:15px;">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>
<?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-danger" style="padding:10px; background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; border-radius: 5px; color: #fca5a5; margin-bottom:15px;">
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<!-- Stat Summary -->
<div class="grid-cards" style="margin-bottom: 25px; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
    <div class="stat-card glass" style="border-left: 4px solid var(--primary);">
        <div class="stat-info">
            <h3><?php echo $total_visits; ?> Kunjungan</h3>
            <p>Total Pengunjung Gym Harian</p>
        </div>
        <div class="stat-icon">
            <i class="fa fa-walking" style="color: var(--primary);"></i>
        </div>
    </div>
    <div class="stat-card glass" style="border-left: 4px solid #10b981;">
        <div class="stat-info">
            <h3>Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></h3>
            <p>Pemasukan Keuangan Harian</p>
        </div>
        <div class="stat-icon">
            <i class="fa fa-wallet" style="color: #10b981;"></i>
        </div>
    </div>
</div>

<!-- Form Catat Pembayaran Harian -->
<div id="addNonMemberForm" class="glass" style="display: none; padding: 20px; margin-bottom: 20px; border-top: 4px solid var(--primary);">
    <h3>Catat Pembayaran Pengunjung Harian</h3>
    <form action="index.php?page=admin_non_member_payments" method="POST" style="margin-top: 15px;">
        <input type="hidden" name="action" value="add_non_member">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Nama Pengunjung</label>
                <input type="text" name="name" class="form-control" placeholder="Contoh: Andi Wijaya" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Biaya Gym (Rupiah)</label>
                <input type="number" name="amount" class="form-control" placeholder="Contoh: 35000" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Tanggal Latihan</label>
                <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
            </div>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">Simpan Pembayaran</button>
            <button type="button" class="btn btn-outline" onclick="document.getElementById('addNonMemberForm').style.display = 'none';">Batal</button>
        </div>
    </form>
</div>

<!-- Tabel Daftar Pembayaran Harian -->
<div class="glass" style="padding: 20px;">
    <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 15px;">
        <h3 style="margin: 0; font-size: 18px;">Daftar Kunjungan Harian Terbaru</h3>
    </div>

    <div class="table-container">
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #1f2937; color: white;">
                    <th style="padding: 12px; text-align: left;">No</th>
                    <th style="padding: 12px; text-align: left;">Nama Pengunjung</th>
                    <th style="padding: 12px; text-align: left;">Tanggal</th>
                    <th style="padding: 12px; text-align: right;">Jumlah Bayar</th>
                    <th style="padding: 12px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->query("SELECT * FROM non_member_payments ORDER BY payment_date DESC, id DESC");
                $no = 1;
                while ($row = $stmt->fetch()):
                ?>
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 15px 12px; vertical-align: middle;"><?php echo $no++; ?></td>
                    <td style="padding: 15px 12px; vertical-align: middle; font-weight: bold;"><?php echo htmlspecialchars($row['name']); ?></td>
                    <td style="padding: 15px 12px; vertical-align: middle;"><?php echo date('d M Y', strtotime($row['payment_date'])); ?></td>
                    <td style="padding: 15px 12px; vertical-align: middle; text-align: right; font-weight: bold; color: #10b981;">Rp <?php echo number_format($row['amount'], 0, ',', '.'); ?></td>
                    <td style="padding: 15px 12px; vertical-align: middle; text-align: center;">
                        <form action="index.php?page=admin_non_member_payments" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan kunjungan ini?');" style="margin: 0;">
                            <input type="hidden" name="action" value="delete_non_member">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px; background-color: #ef4444; border-color: #ef4444; color: white; border-radius: 4px;">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; if ($no == 1): ?>
                <tr>
                    <td colspan="5" style="padding: 20px; text-align: center; color: var(--text-muted); font-style: italic;">Belum ada catatan pembayaran harian hari ini.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
