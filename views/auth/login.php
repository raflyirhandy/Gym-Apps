<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - MachoGym</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: radial-gradient(circle at center, #1e293b 0%, var(--bg-dark) 100%);
        }
        .auth-card {
            width: 100%;
            max-width: 400px;
            padding: 40px;
            text-align: center;
        }
        .auth-card h2 {
            margin-bottom: 30px;
            font-size: 32px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-muted);
        }
        .form-control {
            width: 100%;
            padding: 12px 15px;
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: white;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s;
        }
        .form-control:focus {
            border-color: var(--primary);
        }
        .auth-card .btn {
            width: 100%;
            margin-top: 10px;
        }
        .auth-links {
            margin-top: 20px;
            color: var(--text-muted);
            font-size: 14px;
        }
        .auth-links a {
            color: var(--primary);
        }
        .alert {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid #ef4444;
            color: #fca5a5;
        }
        .alert-success {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid var(--primary);
            color: #6ee7b7;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card glass">
            <h2>Selamat Datang Kembali</h2>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <form action="index.php?page=auth_action&action=login" method="POST">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="Masukkan email Anda">
                </div>
                <div class="form-group">
                    <label>Kata Sandi</label>
                    <input type="password" name="password" class="form-control" required placeholder="Masukkan kata sandi Anda">
                </div>
                <button type="submit" class="btn btn-primary">Masuk</button>
            </form>
            <div class="auth-links">
                Belum punya akun? <a href="index.php?page=register">Gabung sekarang</a>
            </div>
            <div class="auth-links" style="margin-top:10px;">
                <a href="index.php?page=home"><i class="fa fa-arrow-left"></i> Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</body>
</html>
