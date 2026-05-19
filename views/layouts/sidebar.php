<div class="sidebar glass">
    <div class="logo">
        <span>MachoGym</span>
    </div>
    <ul class="sidebar-menu">
        <?php if($_SESSION['role'] == 'admin'): ?>
            <li><a href="index.php?page=admin_dashboard"><i class="fa fa-home"></i> Dasbor</a></li>
            <li><a href="index.php?page=admin_members"><i class="fa fa-users"></i> Anggota</a></li>
            <li><a href="index.php?page=admin_trainers"><i class="fa fa-dumbbell"></i> Pelatih</a></li>
            <li><a href="index.php?page=admin_classes"><i class="fa fa-calendar-alt"></i> Kelas</a></li>
            <li><a href="index.php?page=admin_non_member_payments"><i class="fa fa-wallet"></i> Kunjungan Harian</a></li>
        <?php elseif($_SESSION['role'] == 'trainer'): ?>
            <li><a href="index.php?page=trainer_dashboard"><i class="fa fa-home"></i> Dasbor</a></li>
            <li><a href="index.php?page=trainer_members"><i class="fa fa-users"></i> Anggota Saya</a></li>
            <li><a href="index.php?page=trainer_programs"><i class="fa fa-clipboard-list"></i> Program</a></li>
            <li><a href="index.php?page=chat"><i class="fa fa-comments"></i> Pesan</a></li>
        <?php elseif($_SESSION['role'] == 'member'): ?>
            <li><a href="index.php?page=member_dashboard"><i class="fa fa-home"></i> Dasbor</a></li>
            <li><a href="index.php?page=member_classes"><i class="fa fa-calendar-check"></i> Kelas</a></li>
            <li><a href="index.php?page=member_progress"><i class="fa fa-chart-line"></i> Perkembangan</a></li>
            <li><a href="index.php?page=chat"><i class="fa fa-comments"></i> Pelatih</a></li>
        <?php endif; ?>
        <li class="logout-link"><a href="index.php?page=logout"><i class="fa fa-sign-out-alt"></i> Keluar</a></li>
    </ul>
</div>
