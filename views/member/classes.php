<?php
require_once 'views/layouts/header.php';
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT id FROM members WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$member_id = $stmt->fetch()['id'];
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Daftar Kelas</h2>
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

<div class="glass" style="padding: 20px;">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama Kelas</th>
                    <th>Pelatih</th>
                    <th>Jadwal</th>
                    <th>Kapasitas</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->query("
                    SELECT c.id, c.name, c.schedule, c.capacity, tu.name as trainer_name,
                           (SELECT COUNT(*) FROM bookings b WHERE b.class_id = c.id AND b.status='booked') as booked_count,
                           (SELECT COUNT(*) FROM bookings b WHERE b.class_id = c.id AND b.member_id = $member_id AND b.status='booked') as is_booked
                    FROM classes c
                    JOIN trainers t ON c.trainer_id = t.id
                    JOIN users tu ON t.user_id = tu.id
                    WHERE c.schedule > NOW()
                    ORDER BY c.schedule ASC
                ");
                while ($row = $stmt->fetch()):
                ?>
                <tr>
                    <td><strong><?php echo $row['name']; ?></strong></td>
                    <td><?php echo $row['trainer_name']; ?></td>
                    <td><?php echo date('d M Y, H:i', strtotime($row['schedule'])); ?></td>
                    <td>
                        <span class="badge <?php echo $row['booked_count'] >= $row['capacity'] ? 'badge-danger' : 'badge-success'; ?>">
                            <?php echo $row['booked_count'] . ' / ' . $row['capacity']; ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($row['is_booked']): ?>
                            <a href="index.php?page=member_book_class&action=cancel&class_id=<?php echo $row['id']; ?>" class="btn btn-outline" style="padding: 5px 10px; font-size: 12px; text-decoration: none;" onclick="return confirm('Batalkan pesanan kelas ini?')">Batalkan</a>
                        <?php elseif ($row['booked_count'] < $row['capacity']): ?>
                            <a href="index.php?page=member_book_class&action=book&class_id=<?php echo $row['id']; ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px; text-decoration: none;">Pesan</a>
                        <?php else: ?>
                            <button class="btn btn-outline" style="padding: 5px 10px; font-size: 12px; cursor: not-allowed;" disabled>Penuh</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
