<?php
require_once 'views/layouts/header.php';
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Action Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add_class') {
        $name = $_POST['name'];
        $description = $_POST['description'] ?? null;
        $trainer_id = $_POST['trainer_id'];
        $schedule = $_POST['schedule'];
        $capacity = $_POST['capacity'];

        $stmt = $conn->prepare("INSERT INTO classes (name, description, trainer_id, schedule, capacity) VALUES (:name, :description, :trainer_id, :schedule, :capacity)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':trainer_id', $trainer_id);
        $stmt->bindParam(':schedule', $schedule);
        $stmt->bindParam(':capacity', $capacity);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Kelas berhasil ditambahkan.";
        } else {
            $_SESSION['error'] = "Gagal menambahkan kelas.";
        }
        echo "<script>window.location.href='index.php?page=admin_classes';</script>";
        exit();
    } elseif ($_POST['action'] == 'edit_class') {
        $id = $_POST['class_id'];
        $name = $_POST['name'];
        $description = $_POST['description'] ?? null;
        $trainer_id = $_POST['trainer_id'];
        $schedule = $_POST['schedule'];
        $capacity = $_POST['capacity'];

        $stmt = $conn->prepare("UPDATE classes SET name = :name, description = :description, trainer_id = :trainer_id, schedule = :schedule, capacity = :capacity WHERE id = :id");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':trainer_id', $trainer_id);
        $stmt->bindParam(':schedule', $schedule);
        $stmt->bindParam(':capacity', $capacity);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Kelas berhasil diubah.";
        } else {
            $_SESSION['error'] = "Gagal mengubah kelas.";
        }
        echo "<script>window.location.href='index.php?page=admin_classes';</script>";
        exit();
    } elseif ($_POST['action'] == 'delete_class') {
        $id = $_POST['class_id'];
        $stmt = $conn->prepare("DELETE FROM classes WHERE id = :id");
        $stmt->bindParam(':id', $id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Kelas berhasil dihapus.";
        } else {
            $_SESSION['error'] = "Gagal menghapus kelas.";
        }
        echo "<script>window.location.href='index.php?page=admin_classes';</script>";
        exit();
    }
}

// Fetch Trainers for dropdown
$trainers_stmt = $conn->query("
    SELECT t.id, u.name 
    FROM trainers t 
    JOIN users u ON t.user_id = u.id 
    ORDER BY u.name ASC
");
$trainers = $trainers_stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Kelola Kelas</h2>
    <button class="btn btn-primary" onclick="document.getElementById('addClassForm').style.display = 'block'; document.getElementById('editClassForm').style.display = 'none';"><i class="fa fa-plus"></i> Tambah Kelas</button>
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

<!-- Form Tambah Kelas -->
<div id="addClassForm" class="glass" style="display: none; padding: 20px; margin-bottom: 20px; border-top: 4px solid var(--primary);">
    <h3>Tambah Kelas Baru</h3>
    <form action="index.php?page=admin_classes" method="POST" style="margin-top: 15px;">
        <input type="hidden" name="action" value="add_class">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Nama Kelas</label>
                <input type="text" name="name" class="form-control" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Pelatih</label>
                <select name="trainer_id" class="form-control" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
                    <option value="" disabled selected style="background-color: #0f172a;">Pilih Pelatih</option>
                    <?php foreach ($trainers as $trainer): ?>
                        <option value="<?php echo $trainer['id']; ?>" style="background-color: #0f172a;"><?php echo htmlspecialchars($trainer['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Jadwal (Tanggal & Waktu)</label>
                <input type="datetime-local" name="schedule" class="form-control" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Kapasitas Maksimal</label>
                <input type="number" name="capacity" class="form-control" min="1" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
            </div>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Deskripsi Kelas</label>
            <textarea name="description" class="form-control" style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;"></textarea>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">Simpan Kelas</button>
            <button type="button" class="btn btn-outline" onclick="document.getElementById('addClassForm').style.display = 'none';">Batal</button>
        </div>
    </form>
</div>

<!-- Form Edit Kelas -->
<div id="editClassForm" class="glass" style="display: none; padding: 20px; margin-bottom: 20px; border-top: 4px solid var(--warning);">
    <h3>Edit Kelas</h3>
    <form action="index.php?page=admin_classes" method="POST" style="margin-top: 15px;">
        <input type="hidden" name="action" value="edit_class">
        <input type="hidden" name="class_id" id="edit_class_id">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Nama Kelas</label>
                <input type="text" name="name" id="edit_name" class="form-control" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Pelatih</label>
                <select name="trainer_id" id="edit_trainer_id" class="form-control" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
                    <?php foreach ($trainers as $trainer): ?>
                        <option value="<?php echo $trainer['id']; ?>" style="background-color: #0f172a;"><?php echo htmlspecialchars($trainer['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Jadwal (Tanggal & Waktu)</label>
                <input type="datetime-local" name="schedule" id="edit_schedule" class="form-control" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Kapasitas Maksimal</label>
                <input type="number" name="capacity" id="edit_capacity" class="form-control" min="1" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
            </div>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Deskripsi Kelas</label>
            <textarea name="description" id="edit_description" class="form-control" style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;"></textarea>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-warning" style="color: black;">Simpan Perubahan</button>
            <button type="button" class="btn btn-outline" onclick="document.getElementById('editClassForm').style.display = 'none';">Batal</button>
        </div>
    </form>
</div>

<div class="glass" style="padding: 20px;">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama Kelas</th>
                    <th>Pelatih</th>
                    <th>Jadwal</th>
                    <th>Kapasitas</th>
                    <th>Dipesan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->query("
                    SELECT c.id, c.name, c.description, c.schedule, c.capacity, c.trainer_id, tu.name as trainer_name,
                           (SELECT COUNT(*) FROM bookings b WHERE b.class_id = c.id AND b.status='booked') as booked_count
                    FROM classes c
                    JOIN trainers t ON c.trainer_id = t.id
                    JOIN users tu ON t.user_id = tu.id
                    ORDER BY c.schedule ASC
                ");
                while ($row = $stmt->fetch()):
                    // Format datetime-local string
                    $schedule_val = date('Y-m-d\TH:i', strtotime($row['schedule']));
                ?>
                <tr>
                    <td><strong><?php echo $row['name']; ?></strong></td>
                    <td><?php echo $row['trainer_name']; ?></td>
                    <td><?php echo date('d M Y, H:i', strtotime($row['schedule'])); ?></td>
                    <td><?php echo $row['capacity']; ?></td>
                    <td>
                        <span class="badge <?php echo $row['booked_count'] >= $row['capacity'] ? 'badge-danger' : 'badge-success'; ?>">
                            <?php echo $row['booked_count'] . ' / ' . $row['capacity']; ?>
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 5px;">
                            <button class="btn btn-warning" onclick="editClass(<?php echo $row['id']; ?>, '<?php echo addslashes($row['name']); ?>', '<?php echo addslashes($row['description']); ?>', <?php echo $row['trainer_id']; ?>, '<?php echo $schedule_val; ?>', <?php echo $row['capacity']; ?>)" style="padding: 5px 10px; font-size: 12px; color: black; background-color: var(--warning); border-color: var(--warning);">Edit</button>
                            
                            <form action="index.php?page=admin_classes" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelas ini?');" style="margin: 0;">
                                <input type="hidden" name="action" value="delete_class">
                                <input type="hidden" name="class_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px; background-color: #ef4444; border-color: #ef4444; color: white;">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function editClass(id, name, description, trainerId, schedule, capacity) {
    document.getElementById('addClassForm').style.display = 'none';
    
    document.getElementById('edit_class_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_trainer_id').value = trainerId;
    document.getElementById('edit_schedule').value = schedule;
    document.getElementById('edit_capacity').value = capacity;
    
    document.getElementById('editClassForm').style.display = 'block';
    document.getElementById('editClassForm').scrollIntoView({ behavior: 'smooth' });
}
</script>

<?php require_once 'views/layouts/footer.php'; ?>
