<?php
require_once 'views/layouts/header.php';
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Add Member Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_member') {
    require_once 'models/User.php';
    $user = new User();
    $user->name = $_POST['name'];
    $user->email = $_POST['email'];
    $user->password = $_POST['password'];
    $user->role = 'member';
    $user->phone = $_POST['phone'] ?? null;
    $user->address = $_POST['address'] ?? null;
    
    if ($user->register()) {
        $join_date = $_POST['join_date'] ? $_POST['join_date'] : date('Y-m-d');
        $stmt = $conn->prepare("INSERT INTO members (user_id, status, join_date) VALUES (:user_id, 'active', :join_date)");
        $stmt->bindParam(':user_id', $user->id);
        $stmt->bindParam(':join_date', $join_date);
        $stmt->execute();
        $_SESSION['success'] = "Anggota berhasil ditambahkan.";
        echo "<script>window.location.href='index.php?page=admin_members';</script>";
        exit();
    } else {
        $_SESSION['error'] = "Gagal menambahkan anggota. Email mungkin sudah digunakan.";
    }
}

// Assign Trainer Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'assign_trainer') {
    $member_id = $_POST['member_id'];
    $trainer_id = $_POST['trainer_id'] ? $_POST['trainer_id'] : null;
    
    $stmt_assign = $conn->prepare("UPDATE members SET trainer_id = :trainer_id WHERE id = :member_id");
    $stmt_assign->bindParam(':trainer_id', $trainer_id);
    $stmt_assign->bindParam(':member_id', $member_id);
    
    if ($stmt_assign->execute()) {
        $_SESSION['success'] = "Pelatih berhasil ditugaskan ke anggota.";
    } else {
        $_SESSION['error'] = "Gagal menugaskan pelatih.";
    }
    echo "<script>window.location.href='index.php?page=admin_members';</script>";
    exit();
}

// Fetch all trainers
$trainers_stmt = $conn->query("
    SELECT t.id, u.name 
    FROM trainers t 
    JOIN users u ON t.user_id = u.id 
    ORDER BY u.name ASC
");
$all_trainers = $trainers_stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Kelola Anggota</h2>
    <button class="btn btn-primary" onclick="document.getElementById('addMemberForm').style.display = 'block';"><i class="fa fa-plus"></i> Tambah Anggota</button>
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

<div id="addMemberForm" class="glass" style="display: none; padding: 20px; margin-bottom: 20px; border-top: 4px solid var(--primary);">
    <h3>Tambah Anggota Baru</h3>
    <form action="index.php?page=admin_members" method="POST" style="margin-top: 15px;">
        <input type="hidden" name="action" value="add_member">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Nama Lengkap</label>
                <input type="text" name="name" class="form-control" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Email</label>
                <input type="email" name="email" class="form-control" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Nomor HP</label>
                <input type="text" name="phone" class="form-control" style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Password</label>
                <input type="password" name="password" class="form-control" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Tanggal Bergabung</label>
                <input type="date" name="join_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
            </div>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Alamat</label>
            <textarea name="address" class="form-control" style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;"></textarea>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">Simpan Anggota</button>
            <button type="button" class="btn btn-outline" onclick="document.getElementById('addMemberForm').style.display = 'none';">Batal</button>
        </div>
    </form>
</div>

<div class="glass" style="padding: 20px;">
    <?php
    $count_stmt = $conn->query("SELECT COUNT(*) as total FROM members");
    $total_members = $count_stmt->fetch()['total'];
    ?>
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 15px;">
        <h3 style="margin: 0; font-size: 18px;">Daftar Member Terbaru</h3>
        <span style="background-color: #6b7280; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold;">Total: <?php echo $total_members; ?> Member</span>
    </div>

    <div class="table-container">
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #1f2937; color: white;">
                    <th style="padding: 12px; text-align: left;">No</th>
                    <th style="padding: 12px; text-align: left;">Info Member</th>
                    <th style="padding: 12px; text-align: left;">Pelatih</th>
                    <th style="padding: 12px; text-align: center;">Bukti Bayar</th>
                    <th style="padding: 12px; text-align: center;">Status</th>
                    <th style="padding: 12px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->query("
                    SELECT m.id, m.join_date, u.name, u.email, m.status, m.trainer_id,
                           (SELECT proof_image FROM payments WHERE member_id = m.id ORDER BY id DESC LIMIT 1) as proof_image
                    FROM members m 
                    JOIN users u ON m.user_id = u.id 
                    ORDER BY m.id ASC
                ");
                $no = 1;
                while ($row = $stmt->fetch()):
                    $member_display_id = "MEM" . ($row['join_date'] ? date('Ymd', strtotime($row['join_date'])) : date('Ymd')) . str_pad($row['id'], 4, '0', STR_PAD_LEFT);
                ?>
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 15px 12px; vertical-align: middle;"><?php echo $no++; ?></td>
                    <td style="padding: 15px 12px; vertical-align: middle;">
                        <div style="font-weight: bold; margin-bottom: 4px;"><?php echo htmlspecialchars($row['name']); ?></div>
                        <div style="color: var(--text-muted); font-size: 13px; margin-bottom: 4px;"><?php echo htmlspecialchars($row['email']); ?></div>
                        <div style="color: var(--text-muted); font-size: 13px;">ID: <?php echo $member_display_id; ?></div>
                    </td>
                    <td style="padding: 15px 12px; vertical-align: middle;">
                        <form action="index.php?page=admin_members" method="POST" style="margin: 0;">
                            <input type="hidden" name="action" value="assign_trainer">
                            <input type="hidden" name="member_id" value="<?php echo $row['id']; ?>">
                            <select name="trainer_id" onchange="this.form.submit()" style="width: 100%; padding: 8px; background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-color); border-radius: 6px; color: white; font-size: 13px; cursor: pointer;">
                                <option value="">- Tanpa Pelatih -</option>
                                <?php foreach ($all_trainers as $trainer): ?>
                                    <option value="<?php echo $trainer['id']; ?>" <?php echo ($row['trainer_id'] == $trainer['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($trainer['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                    <td style="padding: 15px 12px; vertical-align: middle; text-align: center;">
                        <?php if ($row['proof_image']): ?>
                            <a href="assets/uploads/payments/<?php echo $row['proof_image']; ?>" target="_blank" class="btn btn-info" style="padding: 6px 12px; font-size: 12px; background-color: #06b6d4; border-color: #06b6d4; color: white; border-radius: 4px; display: inline-block; text-decoration: none;">
                                <i class="fa fa-eye"></i> Lihat Bukti
                            </a>
                        <?php else: ?>
                            <span style="background-color: #6b7280; color: white; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: bold;">- Belum Upload -</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 15px 12px; vertical-align: middle; text-align: center;">
                        <?php if ($row['status'] == 'active'): ?>
                            <span style="background-color: #10b981; color: white; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: bold;">Aktif</span>
                        <?php else: ?>
                            <span style="background-color: #eab308; color: black; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: bold;">Tidak Aktif</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 15px 12px; vertical-align: middle; text-align: center; min-width: 150px;">
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <?php if ($row['status'] != 'active' && !$row['proof_image']): ?>
                                <button class="btn" style="background-color: #9ca3af; color: white; padding: 6px 10px; font-size: 12px; border-radius: 4px; cursor: not-allowed;" disabled>
                                    <i class="fa fa-hourglass-half"></i> Menunggu Bukti
                                </button>
                            <?php elseif ($row['status'] != 'active' && $row['proof_image']): ?>
                                <a href="index.php?page=admin_member_action&action=accept&id=<?php echo $row['id']; ?>" class="btn" style="background-color: #10b981; color: white; padding: 6px 10px; font-size: 12px; border-radius: 4px; text-decoration: none; text-align: center;">
                                    <i class="fa fa-check"></i> Terima
                                </a>
                            <?php elseif ($row['status'] == 'active'): ?>
                                <a href="index.php?page=admin_member_action&action=block&id=<?php echo $row['id']; ?>" class="btn" style="background-color: #eab308; color: black; padding: 6px 10px; font-size: 12px; border-radius: 4px; text-decoration: none; text-align: center;">
                                    <i class="fa fa-ban"></i> Blokir
                                </a>
                            <?php endif; ?>
                            
                            <a href="index.php?page=admin_member_action&action=delete&id=<?php echo $row['id']; ?>" class="btn" style="background-color: #ef4444; color: white; padding: 6px 10px; font-size: 12px; border-radius: 4px; text-decoration: none; text-align: center;" onclick="return confirm('Apakah Anda yakin ingin menghapus member ini permanen?')">
                                <i class="fa fa-trash"></i> Hapus
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
