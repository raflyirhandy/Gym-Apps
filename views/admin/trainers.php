<?php
require_once 'views/layouts/header.php';
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();
?>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add_trainer') {
        require_once 'models/User.php';
        $user = new User();
        $user->name = $_POST['name'];
        $user->email = $_POST['email'];
        $user->password = $_POST['password']; // Will be saved as plain text per previous request
        $user->role = 'trainer';
        $user->phone = $_POST['phone'] ?? null;
        $user->address = $_POST['address'] ?? null;
        
        if ($user->register()) {
            $specialization = $_POST['specialization'];
            $stmt = $conn->prepare("INSERT INTO trainers (user_id, specialization) VALUES (:user_id, :specialization)");
            $stmt->bindParam(':user_id', $user->id);
            $stmt->execute();
            $_SESSION['success'] = "Pelatih berhasil ditambahkan.";
            echo "<script>window.location.href='index.php?page=admin_trainers';</script>";
            exit();
        } else {
            $_SESSION['error'] = "Gagal menambahkan pelatih. Email mungkin sudah digunakan.";
        }
    } elseif ($_POST['action'] == 'edit_trainer') {
        $trainer_id = $_POST['trainer_id'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'] ?? null;
        $address = $_POST['address'] ?? null;
        $specialization = $_POST['specialization'];
        $password = $_POST['password'] ?? '';

        // First get the user_id for this trainer
        $stmt = $conn->prepare("SELECT user_id FROM trainers WHERE id = :id");
        $stmt->bindParam(':id', $trainer_id);
        $stmt->execute();
        $trainer_data = $stmt->fetch();
        
        if ($trainer_data) {
            $user_id = $trainer_data['user_id'];
            
            // Update user
            if (!empty($password)) {
                $stmt = $conn->prepare("UPDATE users SET name = :name, email = :email, phone = :phone, address = :address, password = :password WHERE id = :user_id");
                $stmt->bindParam(':password', $password);
            } else {
                $stmt = $conn->prepare("UPDATE users SET name = :name, email = :email, phone = :phone, address = :address WHERE id = :user_id");
            }
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            // Update trainer specialization
            $stmt = $conn->prepare("UPDATE trainers SET specialization = :specialization WHERE id = :id");
            $stmt->bindParam(':specialization', $specialization);
            $stmt->bindParam(':id', $trainer_id);
            $stmt->execute();

            $_SESSION['success'] = "Pelatih berhasil diubah.";
            echo "<script>window.location.href='index.php?page=admin_trainers';</script>";
            exit();
        } else {
            $_SESSION['error'] = "Gagal mengubah pelatih.";
        }
    }
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Kelola Pelatih</h2>
    <button class="btn btn-primary" onclick="document.getElementById('addTrainerForm').style.display = 'block';"><i class="fa fa-plus"></i> Tambah Pelatih</button>
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

<div id="addTrainerForm" class="glass" style="display: none; padding: 20px; margin-bottom: 20px; border-top: 4px solid var(--primary);">
    <h3>Tambah Pelatih Baru</h3>
    <form action="index.php?page=admin_trainers" method="POST" style="margin-top: 15px;">
        <input type="hidden" name="action" value="add_trainer">
        
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
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Spesialisasi</label>
            <input type="text" name="specialization" class="form-control" placeholder="Contoh: Weightlifting & Bodybuilding" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">Simpan Pelatih</button>
            <button type="button" class="btn btn-outline" onclick="document.getElementById('addTrainerForm').style.display = 'none';">Batal</button>
        </div>
    </form>
</div>

<div id="editTrainerForm" class="glass" style="display: none; padding: 20px; margin-bottom: 20px; border-top: 4px solid var(--warning);">
    <h3>Edit Pelatih</h3>
    <form action="index.php?page=admin_trainers" method="POST" style="margin-top: 15px;">
        <input type="hidden" name="action" value="edit_trainer">
        <input type="hidden" name="trainer_id" id="edit_trainer_id">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Nama Lengkap</label>
                <input type="text" name="name" id="edit_name" class="form-control" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Email</label>
                <input type="email" name="email" id="edit_email" class="form-control" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Nomor HP</label>
                <input type="text" name="phone" id="edit_phone" class="form-control" style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Password (Kosongkan jika tidak ingin diubah)</label>
                <input type="password" name="password" class="form-control" style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
            </div>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Spesialisasi</label>
            <input type="text" name="specialization" id="edit_specialization" class="form-control" placeholder="Contoh: Weightlifting & Bodybuilding" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Alamat</label>
            <textarea name="address" id="edit_address" class="form-control" style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); border-radius: 6px; color: white;"></textarea>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-warning" style="color: black;">Simpan Perubahan</button>
            <button type="button" class="btn btn-outline" onclick="document.getElementById('editTrainerForm').style.display = 'none';">Batal</button>
        </div>
    </form>
</div>

<div class="glass" style="padding: 20px;">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Spesialisasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->query("
                    SELECT t.id, u.name, u.email, u.phone, u.address, t.specialization 
                    FROM trainers t 
                    JOIN users u ON t.user_id = u.id 
                    ORDER BY t.id DESC
                ");
                while ($row = $stmt->fetch()):
                ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['specialization'] ? $row['specialization'] : '<em>Belum diatur</em>'; ?></td>
                    <td>
                        <button class="btn btn-warning" onclick="editTrainer(<?php echo $row['id']; ?>, '<?php echo addslashes($row['name']); ?>', '<?php echo addslashes($row['email']); ?>', '<?php echo addslashes($row['phone']); ?>', '<?php echo addslashes($row['address']); ?>', '<?php echo addslashes($row['specialization']); ?>')" style="padding: 5px 10px; font-size: 12px; color: black; background-color: var(--warning); border-color: var(--warning);">Edit</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function editTrainer(id, name, email, phone, address, specialization) {
    document.getElementById('addTrainerForm').style.display = 'none';
    
    document.getElementById('edit_trainer_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_address').value = address;
    document.getElementById('edit_specialization').value = specialization;
    
    document.getElementById('editTrainerForm').style.display = 'block';
    document.getElementById('editTrainerForm').scrollIntoView({ behavior: 'smooth' });
}
</script>

<?php require_once 'views/layouts/footer.php'; ?>
