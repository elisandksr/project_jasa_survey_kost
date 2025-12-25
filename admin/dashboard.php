<?php
session_start();
include '../config.php';

// Cek level admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login_admin.php");
    exit();
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// --- STATS QUERIES ---
$res = $conn->query("SELECT COUNT(*) as total FROM pemesanan");
$total_orders = $res->fetch_assoc()['total'];

$res = $conn->query("SELECT COUNT(*) as total FROM pembayaran WHERE status = 'Menunggu'");
$pending_payments = $res->fetch_assoc()['total'];

$res = $conn->query("SELECT COUNT(*) as total FROM hasil_survey");
$completed_surveys = $res->fetch_assoc()['total'];

$res = $conn->query("SELECT SUM(total_pembayaran) as total FROM pembayaran WHERE status = 'Valid' OR status = 'Selesai'");
$revenue = $res->fetch_assoc()['total'] ?? 0;

// --- RECENT ORDERS ---
$stmt = $conn->prepare("SELECT p.*, k.nama_lengkap, l.jenis_layanan, pay.status as status_bayar 
                        FROM pemesanan p
                        JOIN klien k ON p.id_klien = k.id_klien
                        JOIN layanan l ON p.id_layanan = l.id_layanan
                        LEFT JOIN pembayaran pay ON p.id_pemesanan = pay.id_pemesanan
                        ORDER BY p.id_pemesanan DESC LIMIT 5");
$stmt->execute();
$recent = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SurveyKost</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
            <li><a href="dashboard.php" class="active"><span class="icon">📊</span> Dashboard</a></li>
            <li><a href="pengguna.php"><span class="icon">👥</span> Data Pengguna</a></li>
            <li><a href="pemesanan.php"><span class="icon">📝</span> Pemesanan</a></li>
            <li><a href="pembayaran.php"><span class="icon">💳</span> Pembayaran</a></li>
            <li><a href="survey.php"><span class="icon">📋</span> Hasil Survey</a></li>
            <li><a href="laporan.php"><span class="icon">📄</span> Laporan</a></li>
            <li><a href="../logout.php" class="logout-btn"><span class="icon">🚪</span> Keluar</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <div class="page-title">
                <h1>Halo, <?php echo htmlspecialchars($admin_name); ?> 👋</h1>
                <p>Ringkasan aktivitas sistem hari ini</p>
            </div>
        </div>

        <!-- STATS -->
        <div class="stats-grid">
            <div class="stat-card horizontal card-orange">
                <div class="stat-info"><span>Total Pesanan</span><h3><?php echo $total_orders; ?></h3></div>
                <div class="stat-icon">📦</div>
            </div>
            <div class="stat-card horizontal card-green">
                <div class="stat-info"><span>Total Pendapatan</span><h3>Rp <?php echo number_format($revenue, 0, ',', '.'); ?></h3></div>
                <div class="stat-icon">💰</div>
            </div>
            <div class="stat-card horizontal card-blue">
                <div class="stat-info"><span>Perlu Verifikasi</span><h3><?php echo $pending_payments; ?></h3></div>
                <div class="stat-icon">⚡</div>
            </div>
            <div class="stat-card horizontal card-purple">
                <div class="stat-info"><span>Survey Selesai</span><h3><?php echo $completed_surveys; ?></h3></div>
                <div class="stat-icon">✅</div>
            </div>
        </div>

        <!-- RECENT ORDERS -->
        <div class="table-section">
            <div class="table-header-title">Pesanan Terbaru Masuk</div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Klien</th>
                        <th>Layanan</th>
                        <th>Status Pembayaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recent->num_rows > 0): ?>
                        <?php while($row = $recent->fetch_assoc()): 
                            $st = $row['status_bayar'];
                            $cls = 'status-warning'; $txt = 'Menunggu'; // Default for Pending/Null (was Belum Bayar)
                            
                            if($st == 'Valid' || $st == 'Selesai') { 
                                $cls = 'status-success'; $txt = 'Berhasil'; // (was Lunas)
                            } elseif($st == 'Menunggu') { 
                                $cls = 'status-info'; $txt = 'Verifikasi'; 
                            } elseif($st == 'Invalid') { 
                                $cls = 'status-danger'; $txt = 'Ditolak';
                            }
                        ?>
                        <tr>
                            <td>#<?php echo $row['id_pemesanan']; ?></td>
                            <td style="font-weight:600; color:var(--text-dark);"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td><?php echo $row['jenis_layanan']; ?></td>
                            <td><span class="status-badge <?php echo $cls; ?>"><?php echo $txt; ?></span></td>
                            <td>
                                <a href="pemesanan.php" class="btn btn-sm btn-primary">Detail &rsaquo;</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center; color:#999;">Belum ada pesanan terbaru.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</body>
</html>
