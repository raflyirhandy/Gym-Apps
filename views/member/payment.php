<?php require_once 'views/layouts/header.php'; ?>

<style>
    .payment-container {
        max-width: 600px;
        margin: 0 auto;
    }
    .payment-card {
        background: var(--bg-dark);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        overflow: hidden;
    }
    .payment-header {
        background-color: var(--primary);
        color: white;
        padding: 15px 20px;
        font-size: 18px;
        font-weight: bold;
    }
    .payment-body {
        padding: 20px;
    }
    .instruction-box {
        background-color: rgba(14, 165, 233, 0.1);
        border: 1px solid rgba(14, 165, 233, 0.3);
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .form-group {
        margin-bottom: 15px;
    }
    .form-group label {
        display: block;
        margin-bottom: 5px;
        color: var(--text-muted);
    }
    .form-control-file {
        display: block;
        width: 100%;
        padding: 10px;
        background: rgba(15, 23, 42, 0.5);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        color: white;
    }
    .btn-full {
        width: 100%;
        margin-bottom: 10px;
    }
</style>

<div class="payment-container">
    <div class="payment-card glass">
        <div class="payment-header">
            Form Pembayaran Membership
        </div>
        <div class="payment-body">
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger" style="margin-bottom:15px; padding:10px; background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; border-radius: 5px; color: #fca5a5;">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success" style="margin-bottom:15px; padding:10px; background: rgba(34, 197, 94, 0.2); border: 1px solid #22c55e; border-radius: 5px; color: #86efac;">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <p style="margin-bottom: 15px;">Silakan transfer ke rekening berikut:</p>
            
            <div class="instruction-box">
                <p><strong>DANA: 081573086465</strong></p>
                <p>a.n Macho Gym Official</p>
                <p>Nominal: <strong>Rp 50.000 / bulan</strong></p>
            </div>
            
            <form action="index.php?page=process_payment" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Upload Bukti Transfer (Foto/Screenshot)</label>
                    <input type="file" name="proof_image" class="form-control-file" accept="image/jpeg, image/png, image/jpg" required>
                    <small style="color: var(--text-muted); display: block; margin-top: 5px;">Format: JPG, JPEG, PNG. Maks 2MB.</small>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">Kirim Bukti</button>
                <a href="index.php?page=member_dashboard" class="btn btn-outline btn-full" style="display: block; text-align: center; color: white;">Kembali</a>
            </form>
        </div>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
