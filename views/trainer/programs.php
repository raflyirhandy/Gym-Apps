<?php
require_once 'views/layouts/header.php';
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Get trainer_id based on user_id
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
    if ($_POST['action'] == 'add_program') {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $member_id = $_POST['member_id'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        $stmt_add = $conn->prepare("INSERT INTO workout_programs (trainer_id, member_id, title, description, start_date, end_date) VALUES (:trainer_id, :member_id, :title, :description, :start_date, :end_date)");
        $stmt_add->bindParam(':trainer_id', $trainer_id);
        $stmt_add->bindParam(':member_id', $member_id);
        $stmt_add->bindParam(':title', $title);
        $stmt_add->bindParam(':description', $description);
        $stmt_add->bindParam(':start_date', $start_date);
        $stmt_add->bindParam(':end_date', $end_date);

        if ($stmt_add->execute()) {
            $_SESSION['success'] = "Program latihan berhasil dibuat.";
        } else {
            $_SESSION['error'] = "Gagal membuat program latihan.";
        }
        echo "<script>window.location.href='index.php?page=trainer_programs';</script>";
        exit();
    } elseif ($_POST['action'] == 'edit_program') {
        $id = $_POST['program_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $member_id = $_POST['member_id'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        $stmt_edit = $conn->prepare("UPDATE workout_programs SET title = :title, description = :description, member_id = :member_id, start_date = :start_date, end_date = :end_date WHERE id = :id AND trainer_id = :trainer_id");
        $stmt_edit->bindParam(':title', $title);
        $stmt_edit->bindParam(':description', $description);
        $stmt_edit->bindParam(':member_id', $member_id);
        $stmt_edit->bindParam(':start_date', $start_date);
        $stmt_edit->bindParam(':end_date', $end_date);
        $stmt_edit->bindParam(':id', $id);
        $stmt_edit->bindParam(':trainer_id', $trainer_id);

        if ($stmt_edit->execute()) {
            $_SESSION['success'] = "Program latihan berhasil diperbarui.";
        } else {
            $_SESSION['error'] = "Gagal memperbarui program latihan.";
        }
        echo "<script>window.location.href='index.php?page=trainer_programs';</script>";
        exit();
    } elseif ($_POST['action'] == 'delete_program') {
        $id = $_POST['program_id'];
        $stmt_del = $conn->prepare("DELETE FROM workout_programs WHERE id = :id AND trainer_id = :trainer_id");
        $stmt_del->bindParam(':id', $id);
        $stmt_del->bindParam(':trainer_id', $trainer_id);

        if ($stmt_del->execute()) {
            $_SESSION['success'] = "Program latihan berhasil dihapus.";
        } else {
            $_SESSION['error'] = "Gagal menghapus program latihan.";
        }
        echo "<script>window.location.href='index.php?page=trainer_programs';</script>";
        exit();
    }
}

// Fetch assigned members for dropdown selection
$members_stmt = $conn->prepare("
    SELECT m.id, u.name 
    FROM members m 
    JOIN users u ON m.user_id = u.id 
    WHERE m.trainer_id = :trainer_id AND m.status = 'active'
    ORDER BY u.name ASC
");
$members_stmt->bindParam(':trainer_id', $trainer_id);
$members_stmt->execute();
$assigned_members = $members_stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Program Latihan</h2>
    <button class="btn btn-primary" onclick="document.getElementById('addProgramForm').style.display = 'block'; document.getElementById('editProgramForm').style.display = 'none';"><i class="fa fa-plus"></i> Tambah Program</button>
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

<!-- Form Tambah Program -->
<div id="addProgramForm" class="glass" style="display: none; padding: 20px; margin-bottom: 20px; border-top: 4px solid var(--primary);">
    <h3>Buat Program Latihan Baru</h3>
    <form action="index.php?page=trainer_programs" method="POST" style="margin-top: 15px;">
        <input type="hidden" name="action" value="add_program">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Judul Program</label>
                <input type="text" name="title" class="form-control" placeholder="Contoh: Bulking 1 Bulan / Cutting Intensif" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Anggota (Member)</label>
                <select name="member_id" class="form-control" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
                    <option value="" disabled selected style="background-color: #0f172a;">Pilih Anggota</option>
                    <?php foreach ($assigned_members as $member): ?>
                        <option value="<?php echo $member['id']; ?>" style="background-color: #0f172a;"><?php echo htmlspecialchars($member['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Tanggal Mulai</label>
                <input type="date" name="start_date" class="form-control" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Tanggal Selesai</label>
                <input type="date" name="end_date" class="form-control" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
            </div>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Deskripsi & Panduan Latihan</label>
            <textarea name="description" class="form-control" placeholder="Contoh: Fokus latihan compound. Squat 4x8, Bench Press 4x8. Surplus kalori + 300 kcal." required style="width: 100%; padding: 10px; height: 120px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;"></textarea>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">Simpan Program</button>
            <button type="button" class="btn btn-outline" onclick="document.getElementById('addProgramForm').style.display = 'none';">Batal</button>
        </div>
    </form>
</div>

<!-- Form Edit Program -->
<div id="editProgramForm" class="glass" style="display: none; padding: 20px; margin-bottom: 20px; border-top: 4px solid var(--warning);">
    <h3>Edit Program Latihan</h3>
    <form action="index.php?page=trainer_programs" method="POST" style="margin-top: 15px;">
        <input type="hidden" name="action" value="edit_program">
        <input type="hidden" name="program_id" id="edit_program_id">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Judul Program</label>
                <input type="text" name="title" id="edit_title" class="form-control" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Anggota (Member)</label>
                <select name="member_id" id="edit_member_id" class="form-control" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
                    <?php foreach ($assigned_members as $member): ?>
                        <option value="<?php echo $member['id']; ?>" style="background-color: #0f172a;"><?php echo htmlspecialchars($member['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Tanggal Mulai</label>
                <input type="date" name="start_date" id="edit_start_date" class="form-control" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Tanggal Selesai</label>
                <input type="date" name="end_date" id="edit_end_date" class="form-control" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
            </div>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Deskripsi & Panduan Latihan</label>
            <textarea name="description" id="edit_description" class="form-control" required style="width: 100%; padding: 10px; height: 120px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;"></textarea>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-warning" style="color: black;">Simpan Perubahan</button>
            <button type="button" class="btn btn-outline" onclick="document.getElementById('editProgramForm').style.display = 'none';">Batal</button>
        </div>
    </form>
</div>

<!-- Modal Detail Program -->
<div id="detailProgramModal" class="glass" style="display: none; padding: 25px; margin-bottom: 20px; border-top: 4px solid #10b981; position: relative;">
    <button onclick="document.getElementById('detailProgramModal').style.display = 'none';" style="position: absolute; top: 15px; right: 15px; background: none; border: none; color: white; font-size: 20px; cursor: pointer;"><i class="fa fa-times"></i></button>
    <h3 style="color: #10b981; margin-bottom: 15px;"><i class="fa fa-clipboard-list"></i> Detail Program Latihan</h3>
    <div style="display: grid; grid-template-columns: 120px 1fr; gap: 10px; margin-bottom: 15px;">
        <span style="color: var(--text-muted);">Judul:</span>
        <strong id="detail_title" style="color: white;">-</strong>
        <span style="color: var(--text-muted);">Member:</span>
        <span id="detail_member">-</span>
        <span style="color: var(--text-muted);">Periode:</span>
        <span id="detail_period">-</span>
    </div>
    <div style="border-top: 1px solid var(--border-color); padding-top: 15px; margin-top: 15px;">
        <label style="display: block; margin-bottom: 8px; color: var(--text-muted); font-size: 13px;">Panduan Latihan:</label>
        <p id="detail_desc" style="white-space: pre-wrap; background: rgba(15, 23, 42, 0.3); padding: 15px; border-radius: 6px; border: 1px solid var(--border-color); line-height: 1.6; color: #cbd5e1;"></p>
    </div>
</div>

<!-- Tabel List Program -->
<div class="glass" style="padding: 20px;">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Judul Program</th>
                    <th>Anggota</th>
                    <th>Mulai</th>
                    <th>Selesai</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->prepare("
                    SELECT p.id, p.title, p.description, p.start_date, p.end_date, p.member_id, u.name as member_name 
                    FROM workout_programs p 
                    JOIN members m ON p.member_id = m.id
                    JOIN users u ON m.user_id = u.id 
                    WHERE p.trainer_id = :trainer_id
                    ORDER BY p.start_date DESC
                ");
                $stmt->bindParam(':trainer_id', $trainer_id);
                $stmt->execute();
                $no = 1;
                while ($row = $stmt->fetch()):
                ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['member_name']); ?></td>
                    <td><?php echo date('d M Y', strtotime($row['start_date'])); ?></td>
                    <td><?php echo date('d M Y', strtotime($row['end_date'])); ?></td>
                    <td>
                        <div style="display: flex; gap: 8px; justify-content: center;">
                            <button class="btn btn-info" onclick="showDetail('<?php echo addslashes($row['title']); ?>', '<?php echo addslashes($row['member_name']); ?>', '<?php echo date('d M Y', strtotime($row['start_date'])) . ' s/d ' . date('d M Y', strtotime($row['end_date'])); ?>', '<?php echo addslashes($row['description']); ?>')" style="padding: 5px 10px; font-size: 12px; background-color: #06b6d4; border-color: #06b6d4; color: white;">Lihat</button>
                            
                            <button class="btn btn-warning" onclick="editProgram(<?php echo $row['id']; ?>, '<?php echo addslashes($row['title']); ?>', <?php echo $row['member_id']; ?>, '<?php echo $row['start_date']; ?>', '<?php echo $row['end_date']; ?>', '<?php echo addslashes($row['description']); ?>')" style="padding: 5px 10px; font-size: 12px; color: black; background-color: var(--warning); border-color: var(--warning);">Edit</button>
                            
                            <form action="index.php?page=trainer_programs" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus program latihan ini?');" style="margin: 0;">
                                <input type="hidden" name="action" value="delete_program">
                                <input type="hidden" name="program_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px; background-color: #ef4444; border-color: #ef4444; color: white;">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php $no++; endwhile; if ($no == 1): ?>
                <tr>
                    <td colspan="5" style="padding: 20px; text-align: center; color: var(--text-muted); font-style: italic;">Belum ada program latihan yang dibuat.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function showDetail(title, member, period, desc) {
    document.getElementById('detail_title').innerText = title;
    document.getElementById('detail_member').innerText = member;
    document.getElementById('detail_period').innerText = period;
    document.getElementById('detail_desc').innerText = desc;
    
    document.getElementById('addProgramForm').style.display = 'none';
    document.getElementById('editProgramForm').style.display = 'none';
    document.getElementById('detailProgramModal').style.display = 'block';
    document.getElementById('detailProgramModal').scrollIntoView({ behavior: 'smooth' });
}

function editProgram(id, title, memberId, startDate, endDate, desc) {
    document.getElementById('addProgramForm').style.display = 'none';
    document.getElementById('detailProgramModal').style.display = 'none';
    
    document.getElementById('edit_program_id').value = id;
    document.getElementById('edit_title').value = title;
    document.getElementById('edit_member_id').value = memberId;
    document.getElementById('edit_start_date').value = startDate;
    document.getElementById('edit_end_date').value = endDate;
    document.getElementById('edit_description').value = desc;
    
    document.getElementById('editProgramForm').style.display = 'block';
    document.getElementById('editProgramForm').scrollIntoView({ behavior: 'smooth' });
}
</script>

<?php require_once 'views/layouts/footer.php'; ?>
