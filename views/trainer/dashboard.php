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

// Get assigned members
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM members WHERE trainer_id = :trainer_id");
$stmt->bindParam(':trainer_id', $trainer_id);
$stmt->execute();
$total_assigned_members = $stmt->fetch()['total'];

// Get upcoming classes
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM classes WHERE trainer_id = :trainer_id AND schedule > NOW()");
$stmt->bindParam(':trainer_id', $trainer_id);
$stmt->execute();
$upcoming_classes = $stmt->fetch()['total'];

// Get active workout programs
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM workout_programs WHERE trainer_id = :trainer_id");
$stmt->bindParam(':trainer_id', $trainer_id);
$stmt->execute();
$total_programs = $stmt->fetch()['total'];

// Fetch latest progress activities of assigned members
$activity_stmt = $conn->prepare("
    SELECT p.*, u.name as member_name 
    FROM progress p 
    JOIN members m ON p.member_id = m.id 
    JOIN users u ON m.user_id = u.id 
    WHERE m.trainer_id = :trainer_id 
    ORDER BY p.date DESC LIMIT 5
");
$activity_stmt->bindParam(':trainer_id', $trainer_id);
$activity_stmt->execute();
$recent_activities = $activity_stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Dasbor Pelatih</h2>
    <div style="display: flex; gap: 10px;">
        <a href="index.php?page=trainer_programs" class="btn btn-primary" style="text-decoration: none;"><i class="fa fa-clipboard-list"></i> Buat Program</a>
        <a href="index.php?page=chat" class="btn btn-outline" style="text-decoration: none;"><i class="fa fa-comments"></i> Buka Chat</a>
    </div>
</div>

<!-- Stat Cards -->
<div class="grid-cards" style="margin-bottom: 25px;">
    <div class="stat-card glass">
        <div class="stat-info">
            <h3><?php echo $total_assigned_members; ?></h3>
            <p>Anggota Ditugaskan</p>
        </div>
        <div class="stat-icon">
            <i class="fa fa-users" style="color: var(--primary);"></i>
        </div>
    </div>
    
    <div class="stat-card glass">
        <div class="stat-info">
            <h3><?php echo $upcoming_classes; ?></h3>
            <p>Kelas Mendatang</p>
        </div>
        <div class="stat-icon">
            <i class="fa fa-calendar-check" style="color: #60a5fa;"></i>
        </div>
    </div>

    <div class="stat-card glass">
        <div class="stat-info">
            <h3><?php echo $total_programs; ?></h3>
            <p>Program Latihan Aktif</p>
        </div>
        <div class="stat-icon">
            <i class="fa fa-clipboard-list" style="color: #10b981;"></i>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; align-items: start;">
    <!-- Upcoming Classes Section -->
    <div class="glass" style="padding: 20px; height: 100%;">
        <h3 style="margin-bottom: 15px;"><i class="fa fa-calendar-alt" style="color: #60a5fa; margin-right: 8px;"></i>Kelas Mendatang Saya</h3>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama Kelas</th>
                        <th>Jadwal</th>
                        <th>Dipesan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->prepare("
                        SELECT id, name, schedule, capacity,
                               (SELECT COUNT(*) FROM bookings b WHERE b.class_id = classes.id AND b.status='booked') as booked_count
                        FROM classes
                        WHERE trainer_id = :trainer_id AND schedule > NOW()
                        ORDER BY schedule ASC LIMIT 5
                    ");
                    $stmt->bindParam(':trainer_id', $trainer_id);
                    $stmt->execute();
                    $no_classes = true;
                    while ($row = $stmt->fetch()):
                        $no_classes = false;
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                        <td><?php echo date('d M Y, H:i', strtotime($row['schedule'])); ?></td>
                        <td>
                            <span class="badge badge-success" style="padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 11px;">
                                <?php echo $row['booked_count'] . ' / ' . $row['capacity']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; if ($no_classes): ?>
                    <tr>
                        <td colspan="3" style="padding: 15px; text-align: center; color: var(--text-muted); font-style: italic;">Tidak ada kelas mendatang terdekat.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Activities Section -->
    <div class="glass" style="padding: 20px; height: 100%;">
        <h3 style="margin-bottom: 15px;"><i class="fa fa-chart-line" style="color: #10b981; margin-right: 8px;"></i>Aktivitas Perkembangan Anggota</h3>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <?php 
            $no_activities = true;
            foreach ($recent_activities as $activity): 
                $no_activities = false;
            ?>
                <div style="background: rgba(15, 23, 42, 0.4); padding: 12px; border-radius: 8px; border-left: 3px solid #10b981; border-top: 1px solid var(--border-color); border-right: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <strong style="color: white; font-size: 14px;"><?php echo htmlspecialchars($activity['member_name']); ?></strong>
                        <span style="font-size: 11px; color: var(--text-muted);"><i class="fa fa-clock"></i> <?php echo date('d M Y', strtotime($activity['date'])); ?></span>
                    </div>
                    <div style="font-size: 12px; color: #cbd5e1; display: flex; gap: 15px; margin-bottom: 4px;">
                        <span>Berat: <strong><?php echo $activity['weight'] ? $activity['weight'] . ' kg' : '-'; ?></strong></span>
                        <span>Repetisi: <strong style="color: #60a5fa;"><?php echo $activity['reps'] ? $activity['reps'] . ' reps' : '-'; ?></strong></span>
                        <span>Durasi: <strong style="color: #10b981;"><?php echo $activity['duration'] ? $activity['duration'] . ' menit' : '-'; ?></strong></span>
                    </div>
                    <?php if (!empty($activity['notes'])): ?>
                        <div style="font-size: 12px; color: var(--text-muted); font-style: italic; background: rgba(0,0,0,0.1); padding: 6px; border-radius: 4px; margin-top: 4px;">
                            "<?php echo htmlspecialchars($activity['notes']); ?>"
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; if ($no_activities): ?>
                <div style="padding: 15px; text-align: center; color: var(--text-muted); font-style: italic; background: rgba(15, 23, 42, 0.2); border-radius: 8px; border: 1px dashed var(--border-color);">
                    Belum ada riwayat perkembangan yang dicatat oleh anggota Anda.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
