<?php
require_once 'views/layouts/header.php';
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Get member_id
$stmt = $conn->prepare("SELECT id FROM members WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$member_id = $stmt->fetch()['id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_progress'])) {
    $date = $_POST['date'];
    $weight = $_POST['weight'];
    $reps = $_POST['reps'];
    $duration = $_POST['duration'];
    $notes = $_POST['notes'];

    $ins = $conn->prepare("INSERT INTO progress (member_id, date, weight, reps, duration, notes) VALUES (:m, :d, :w, :r, :dur, :n)");
    $ins->execute(['m'=>$member_id, 'd'=>$date, 'w'=>$weight, 'r'=>$reps, 'dur'=>$duration, 'n'=>$notes]);
    
    echo "<script>window.location.href='index.php?page=member_progress';</script>";
    exit;
}

// Fetch progress data for chart
$stmt = $conn->prepare("SELECT date, weight, reps, duration FROM progress WHERE member_id = :m ORDER BY date ASC");
$stmt->execute(['m' => $member_id]);
$progress_data = $stmt->fetchAll();

$dates = [];
$weights = [];
$reps = [];

foreach ($progress_data as $row) {
    $dates[] = $row['date'];
    $weights[] = $row['weight'];
    $reps[] = $row['reps'];
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Perkembangan Saya</h2>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-bottom: 30px;">
    <!-- Add Progress Form -->
    <div class="glass" style="padding: 20px;">
        <h3>Catat Latihan</h3>
        <form method="POST" style="margin-top: 15px;">
            <div class="form-group">
                <label>Tanggal</label>
                <input type="date" name="date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="form-group">
                <label>Beban Diangkat (kg)</label>
                <input type="number" step="0.1" name="weight" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Total Repetisi</label>
                <input type="number" name="reps" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Durasi (menit)</label>
                <input type="number" name="duration" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Catatan</label>
                <textarea name="notes" class="form-control" rows="3"></textarea>
            </div>
            <button type="submit" name="add_progress" class="btn btn-primary" style="width: 100%;">Simpan Catatan</button>
        </form>
    </div>

    <!-- Chart -->
    <div class="glass" style="padding: 20px; display: flex; flex-direction: column;">
        <h3>Grafik Perkembangan</h3>
        <div style="flex-grow: 1; position: relative; min-height: 300px;">
            <canvas id="progressChart"></canvas>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('progressChart').getContext('2d');
    
    const dates = <?php echo json_encode($dates); ?>;
    const weights = <?php echo json_encode($weights); ?>;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'Beban Diangkat (kg)',
                data: weights,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.2)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: '#f8fafc'
                    }
                }
            },
            scales: {
                y: {
                    grid: { color: 'rgba(255, 255, 255, 0.1)' },
                    ticks: { color: '#94a3b8' }
                },
                x: {
                    grid: { color: 'rgba(255, 255, 255, 0.1)' },
                    ticks: { color: '#94a3b8' }
                }
            }
        }
    });
});
</script>

<?php require_once 'views/layouts/footer.php'; ?>
