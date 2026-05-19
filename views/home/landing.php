<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MachoGym - Tingkatkan Kebugaran Anda</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar glass">
        <div class="logo">MachoGym.</div>
        <div class="nav-links">
            <a href="#home">Beranda</a>
            <a href="#features">Fitur</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="index.php?page=<?php echo $_SESSION['role']; ?>_dashboard" class="btn btn-primary">Dashboard</a>
            <?php else: ?>
                <a href="index.php?page=login" class="btn btn-outline">Masuk</a>
                <a href="index.php?page=register" class="btn btn-primary">Gabung Sekarang</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <h1>Transformasi Tubuhmu,<br>Tingkatkan Pikiranmu.</h1>
        <p>Bergabunglah dengan komunitas kebugaran terbesar di Kramatmulya.
Peralatan lengkap, pelatih profesional, dan suasana yang mendukung.

</p>
        <div class="hero-btns">
            <a href="index.php?page=register" class="btn btn-primary">Mulai Perjalananmu</a>
            <a href="#features" class="btn btn-outline">Jelajahi Fitur</a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="features-header">
            <h2>Mengapa Memilih MachoGym?</h2>
            <p>Macho Gym didirikan dengan visi untuk menyehatkan masyarakat dengan harga murah. peralatan standar nasional dan instruktur yang berpengalaman, kami siap membantu Anda mencapai body goals impian Anda.</p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card glass">
                <div class="feature-icon">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <h3>Pemesanan Mudah</h3>
                <p>Pesan kelas favorit Anda secara instan. Jangan pernah lewatkan sesi dengan sistem pemesanan kami yang mulus.</p>
            </div>
            
            <div class="feature-card glass">
                <div class="feature-icon">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <h3>Pantau Perkembangan</h3>
                <p>Catat latihan Anda dan pantau kemajuan fisik dengan grafik yang indah dan mudah dibaca.</p>
            </div>
            
            <div class="feature-card glass">
                <div class="feature-icon">
                    <i class="fa-solid fa-comments"></i>
                </div>
                <h3>Interaksi Pelatih</h3>
                <p>Berkomunikasi langsung dengan pelatih pribadi Anda. Dapatkan program dan masukan yang disesuaikan.</p>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <footer style="background-color: #0f172a; padding: 40px 20px 20px 20px; text-align: center; border-top: 1px solid rgba(255, 255, 255, 0.05); width: 100%; display: block; box-sizing: border-box;">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; box-sizing: border-box; text-align: center;">
            <h2 style="font-size: 22px; font-weight: 700; color: #ffffff; letter-spacing: 1px; text-transform: uppercase; margin: 0; padding: 0; display: block; font-family: 'Outfit', sans-serif;">MACHO <span style="color: #ef4444;">GYM</span></h2>
            <p style="color: #94a3b8; font-size: 14px; margin: 4px 0 8px 0; padding: 0; display: block; font-family: 'Outfit', sans-serif;">Kramatmulya, Kuningan, Jawa Barat</p>
            <div style="display: flex; gap: 20px; justify-content: center; align-items: center; margin: 5px auto 10px auto; padding: 0;">
                <a href="#" style="color: #ffffff; font-size: 18px; text-decoration: none; display: inline-block; line-height: 1;"><i class="fa-brands fa-instagram"></i></a>
                <a href="#" style="color: #ffffff; font-size: 18px; text-decoration: none; display: inline-block; line-height: 1;"><i class="fa-brands fa-facebook"></i></a>
                <a href="#" style="color: #ffffff; font-size: 18px; text-decoration: none; display: inline-block; line-height: 1;"><i class="fa-brands fa-whatsapp"></i></a>
            </div>
            <hr style="width: 100%; border: none; border-top: 1px solid rgba(255, 255, 255, 0.08); margin: 15px 0; padding: 0; display: block;">
            <p style="color: #64748b; font-size: 13px; margin: 0; padding: 0; display: block; font-family: 'Outfit', sans-serif;">&copy; 2026 Macho Gym System by Rafly.</p>
        </div>
    </footer>

</body>
</html>
