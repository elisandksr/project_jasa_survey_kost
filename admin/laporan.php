<?php
require_once '../config.php';

// Check Admin Login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login_admin.php");
    exit();
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// 1. Total Klien
$res_klien = $conn->query("SELECT COUNT(*) as total FROM klien");
$total_klien = $res_klien->fetch_assoc()['total'];

// 2. Total Pemesanan (All Time)
$res_order = $conn->query("SELECT COUNT(*) as total FROM pemesanan");
$total_order = $res_order->fetch_assoc()['total'];

// 3. Total Survey Selesai
$res_survey = $conn->query("SELECT COUNT(*) as total FROM hasil_survey");
$total_survey = $res_survey->fetch_assoc()['total'];

// 4. Total Pendapatan (Valid Payments)
$res_revenue = $conn->query("SELECT SUM(total_pembayaran) as total FROM pembayaran WHERE status = 'Valid'");
$row_revenue = $res_revenue->fetch_assoc();
$total_revenue = $row_revenue['total'] ?? 0;

// Format Revenue
function formatCurrencyShort($n) {
    if ($n >= 1000000000) return round($n / 1000000000, 1) . 'M';
    if ($n >= 1000000) return round($n / 1000000, 1) . 'JT';
    if ($n >= 1000) return round($n / 1000, 1) . 'K';
    return $n;
}
$revenue_display = "Rp " . formatCurrencyShort($total_revenue);
$revenue_full = "Rp " . number_format($total_revenue, 0, ',', '.'); 

// 5. Pemesanan Bulan Ini
$current_month = date('Y-m');
$stmt_month = $conn->prepare("SELECT COUNT(*) as total FROM pemesanan WHERE jadwal_survey LIKE ?");
$param_month = $current_month . '%';
$stmt_month->bind_param("s", $param_month);
$stmt_month->execute();
$res_month = $stmt_month->get_result();
$total_month = $res_month->fetch_assoc()['total'];
$stmt_month->close();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan & Statistik - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin_global.css?v=<?php echo time(); ?>">
</head>
<body>

    <aside class="sidebar">
        <div class="profile-section">
            <div class="profile-icon-circle">👤</div>
            <div class="profile-name"><?php echo htmlspecialchars($admin_name); ?></div>
            <div class="profile-role">Administrator</div>
        </div>
        

        
        <ul class="nav-links">
            <li><a href="dashboard.php"><span class="icon">📊</span> Dashboard</a></li>
            <li><a href="pengguna.php"><span class="icon">👥</span> Data Klien</a></li>
            <li><a href="pemesanan.php"><span class="icon">📝</span> Data Pemesanan</a></li>
            <li><a href="pembayaran.php"><span class="icon">💳</span> Data Pembayaran</a></li>
            <li><a href="survey.php"><span class="icon">📋</span> Hasil Survey</a></li>
            <li><a href="laporan.php" class="active"><span class="icon">📄</span> Laporan</a></li>
            <li><a href="../logout.php" class="logout-btn"><span class="icon">🚪</span> Keluar</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <div class="page-title">
                <h1>Laporan & Statistik</h1>
                <p>Ringkasan kinerja operasional bisnis</p>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card card-orange">
                <div class="stat-icon">👥</div>
                <span class="stat-info"><span>Total Klien</span><h3><?php echo $total_klien; ?></h3></span>
                <div class="stat-desc">User terdaftar dalam sistem</div>
            </div>

            <div class="stat-card card-blue">
                <div class="stat-icon">📦</div>
                <span class="stat-info"><span>Total Pemesanan</span><h3><?php echo $total_order; ?></h3></span>
                <div class="stat-desc">Akumulasi seluruh order masuk</div>
            </div>

            <div class="stat-card card-green">
                <div class="stat-icon">💰</div>
                <span class="stat-info"><span>Total Pendapatan</span><h3 title="<?php echo $revenue_full; ?>"><?php echo $revenue_display; ?></h3></span>
                <div class="stat-desc">Pemasukan valid terverifikasi</div>
            </div>

            <div class="stat-card card-purple">
                <div class="stat-icon">✅</div>
                <span class="stat-info"><span>Survey Selesai</span><h3><?php echo $total_survey; ?></h3></span>
                <div class="stat-desc">Pekerjaan survey yang tuntas</div>
            </div>

            <div class="stat-card card-red">
                <div class="stat-icon">📅</div>
                <span class="stat-info"><span>Pesanan Bulan Ini</span><h3><?php echo $total_month; ?></h3></span>
                <div class="stat-desc">Performance bulan <?php echo date('F Y'); ?></div>
            </div>
        </div>
    </main>

</body>
</html>
