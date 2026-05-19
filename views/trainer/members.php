<?php
require_once 'views/layouts/header.php';
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT id FROM trainers WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$trainer_data = $stmt->fetch();
$trainer_id = $trainer_data ? $trainer_data['id'] : null;

if (!$trainer_id) {
    echo "<div class='alert alert-danger'>Profil pelatih belum diatur. Hubungi Admin.</div>";
    require_once 'views/layouts/footer.php';
    exit;
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'claim_member') {
        $member_id = $_POST['member_id'];
        
        $stmt_claim = $conn->prepare("UPDATE members SET trainer_id = :trainer_id WHERE id = :member_id");
        $stmt_claim->bindParam(':trainer_id', $trainer_id);
        $stmt_claim->bindParam(':member_id', $member_id);
        
        if ($stmt_claim->execute()) {
            $_SESSION['success'] = "Anggota berhasil ditambahkan ke binaan Anda.";
        } else {
            $_SESSION['error'] = "Gagal menambahkan anggota.";
        }
        echo "<script>window.location.href='index.php?page=trainer_members';</script>";
        exit();
    } elseif ($_POST['action'] == 'release_member') {
        $member_id = $_POST['member_id'];
        
        $stmt_release = $conn->prepare("UPDATE members SET trainer_id = NULL WHERE id = :member_id AND trainer_id = :trainer_id");
        $stmt_release->bindParam(':member_id', $member_id);
        $stmt_release->bindParam(':trainer_id', $trainer_id);
        
        if ($stmt_release->execute()) {
            $_SESSION['success'] = "Anggota berhasil dilepas dari binaan Anda.";
        } else {
            $_SESSION['error'] = "Gagal melepas anggota.";
        }
        echo "<script>window.location.href='index.php?page=trainer_members';</script>";
        exit();
    }
}

// Fetch all progress logs for trainer's members
$progress_stmt = $conn->prepare("
    SELECT p.*, m.id as member_id 
    FROM progress p 
    JOIN members m ON p.member_id = m.id 
    WHERE m.trainer_id = :trainer_id 
    ORDER BY p.date DESC
");
$progress_stmt->bindParam(':trainer_id', $trainer_id);
$progress_stmt->execute();
$progress_logs = $progress_stmt->fetchAll();

// Group logs by member_id
$logs_by_member = [];
foreach ($progress_logs as $log) {
    $logs_by_member[$log['member_id']][] = [
        'date' => date('d M Y', strtotime($log['date'])),
        'weight' => $log['weight'] ? $log['weight'] . ' kg' : '-',
        'reps' => $log['reps'] ? $log['reps'] . ' reps' : '-',
        'duration' => $log['duration'] ? $log['duration'] . ' menit' : '-',
        'notes' => htmlspecialchars($log['notes'] ?? '-')
    ];
}

// Fetch members who DO NOT have a trainer currently
$unassigned_stmt = $conn->prepare("
    SELECT m.id, u.name, u.email 
    FROM members m 
    JOIN users u ON m.user_id = u.id 
    WHERE m.trainer_id IS NULL AND m.status = 'active'
    ORDER BY u.name ASC
");
$unassigned_stmt->execute();
$unassigned_members = $unassigned_stmt->fetchAll();
?>

<script>
// Expose grouped logs to JS
const memberProgressLogs = <?php echo json_encode($logs_by_member); ?>;
</script>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Anggota Saya</h2>
    <button class="btn btn-primary" onclick="document.getElementById('claimMemberForm').style.display = 'block';"><i class="fa fa-user-plus"></i> Tambah Anggota Binaan</button>
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

<!-- Form Tambah Anggota Binaan (Claim) -->
<div id="claimMemberForm" class="glass" style="display: none; padding: 20px; margin-bottom: 20px; border-top: 4px solid var(--primary);">
    <h3>Tambahkan Anggota ke Daftar Binaan Anda</h3>
    <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 15px;">Berikut adalah daftar anggota aktif yang saat ini belum memiliki pelatih pendamping.</p>
    <form action="index.php?page=trainer_members" method="POST" style="display: flex; gap: 15px; align-items: center;">
        <input type="hidden" name="action" value="claim_member">
        <div style="flex-grow: 1;">
            <select name="member_id" class="form-control" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
                <option value="" disabled selected style="background-color: #0f172a;">-- Pilih Anggota --</option>
                <?php foreach ($unassigned_members as $member): ?>
                    <option value="<?php echo $member['id']; ?>" style="background-color: #0f172a;"><?php echo htmlspecialchars($member['name']) . " (" . htmlspecialchars($member['email']) . ")"; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">Tambahkan</button>
            <button type="button" class="btn btn-outline" onclick="document.getElementById('claimMemberForm').style.display = 'none';">Batal</button>
        </div>
    </form>
</div>

<!-- Progress Log Modal / Card Viewer -->
<div id="progressViewer" class="glass" style="display: none; padding: 25px; margin-bottom: 25px; border-top: 4px solid #60a5fa; position: relative;">
    <button onclick="document.getElementById('progressViewer').style.display = 'none';" style="position: absolute; top: 15px; right: 15px; background: none; border: none; color: white; font-size: 20px; cursor: pointer;"><i class="fa fa-times"></i></button>
    <h3 style="color: #60a5fa; margin-bottom: 15px;"><i class="fa fa-chart-line"></i> Riwayat Perkembangan Anggota: <span id="progress_member_name" style="color: white;">-</span></h3>
    
    <div class="table-container">
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #1f2937; color: white;">
                    <th style="padding: 10px; text-align: left;">Tanggal</th>
                    <th style="padding: 10px; text-align: center;">Berat Beban</th>
                    <th style="padding: 10px; text-align: center;">Repetisi (Reps)</th>
                    <th style="padding: 10px; text-align: center;">Durasi</th>
                    <th style="padding: 10px; text-align: left;">Catatan</th>
                </tr>
            </thead>
            <tbody id="progress_table_body">
                <!-- Dynamically populated -->
            </tbody>
        </table>
    </div>
</div>

<div class="glass" style="padding: 20px;">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama Anggota</th>
                    <th>Email</th>
                    <th>Tanggal Bergabung</th>
                    <th>Status</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->prepare("
                    SELECT m.id, u.name, u.email, m.join_date, m.status 
                    FROM members m 
                    JOIN users u ON m.user_id = u.id 
                    WHERE m.trainer_id = :trainer_id
                    ORDER BY u.name ASC
                ");
                $stmt->bindParam(':trainer_id', $trainer_id);
                $stmt->execute();
                $no = 1;
                while ($row = $stmt->fetch()):
                ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo $row['join_date'] ? date('d M Y', strtotime($row['join_date'])) : '-'; ?></td>
                    <td>
                        <span class="badge <?php echo $row['status'] == 'active' ? 'badge-success' : 'badge-danger'; ?>">
                            <?php echo $row['status'] == 'active' ? 'Aktif' : 'Tidak Aktif'; ?>
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                            <button class="btn btn-info" onclick="viewProgress(<?php echo $row['id']; ?>, '<?php echo addslashes($row['name']); ?>')" style="padding: 5px 10px; font-size: 12px; background-color: #3b82f6; border-color: #3b82f6; color: white;">
                                <i class="fa fa-chart-line"></i> Progress
                            </button>
                            
                            <a href="index.php?page=trainer_programs" class="btn btn-warning" style="padding: 5px 10px; font-size: 12px; color: black; background-color: var(--warning); border-color: var(--warning); text-decoration: none;">
                                <i class="fa fa-clipboard-list"></i> Beri Program
                            </a>
                            
                            <a href="index.php?page=chat" class="btn btn-outline" style="padding: 5px 10px; font-size: 12px; text-decoration: none;">
                                <i class="fa fa-comments"></i> Chat
                            </a>

                            <form action="index.php?page=trainer_members" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin melepas anggota ini dari binaan Anda?');" style="margin: 0;">
                                <input type="hidden" name="action" value="release_member">
                                <input type="hidden" name="member_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px; background-color: #ef4444; border-color: #ef4444; color: white;">
                                    <i class="fa fa-user-minus"></i> Lepas
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php $no++; endwhile; if ($no == 1): ?>
                <tr>
                    <td colspan="5" style="padding: 20px; text-align: center; color: var(--text-muted); font-style: italic;">Belum ada anggota yang ditugaskan kepada Anda. Silakan klik tombol "Tambah Anggota Binaan" di atas untuk menambahkan anggota baru ke daftar Anda.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function viewProgress(memberId, name) {
    document.getElementById('progress_member_name').innerText = name;
    const body = document.getElementById('progress_table_body');
    body.innerHTML = '';
    
    const logs = memberProgressLogs[memberId] || [];
    
    if (logs.length === 0) {
        body.innerHTML = `<tr><td colspan="5" style="padding: 15px; text-align: center; color: var(--text-muted); font-style: italic;">Anggota ini belum mencatat riwayat perkembangan latihan.</td></tr>`;
    } else {
        logs.forEach(log => {
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid var(--border-color)';
            tr.innerHTML = `
                <td style="padding: 12px 10px;">${log.date}</td>
                <td style="padding: 12px 10px; text-align: center; font-weight: bold;">${log.weight}</td>
                <td style="padding: 12px 10px; text-align: center; font-weight: bold; color: #60a5fa;">${log.reps}</td>
                <td style="padding: 12px 10px; text-align: center; font-weight: bold; color: #10b981;">${log.duration}</td>
                <td style="padding: 12px 10px; color: #cbd5e1;">${log.notes}</td>
            `;
            body.appendChild(tr);
        });
    }
    
    document.getElementById('progressViewer').style.display = 'block';
    document.getElementById('progressViewer').scrollIntoView({ behavior: 'smooth' });
}
</script>

<?php require_once 'views/layouts/footer.php'; ?>
