<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dasbor GymPro</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="dashboard-body">
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php require_once 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="top-nav glass">
                <div class="welcome-msg">
                    Selamat datang kembali, <strong><?php echo $_SESSION['user_name']; ?></strong>!
                </div>
                <div class="user-profile">
                    <div class="avatar"><i class="fa fa-user"></i></div>
                </div>
            </div>
            
            <div class="content-area">
