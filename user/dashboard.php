<?php
require_once '../config.php';

// Cek Login User
if (!isset($_SESSION['user_logged_in']) && !isset($_SESSION['isLoggedIn'])) {
    header("Location: ../login.php");
    exit();
}

$id_klien = $_SESSION['user_id'] ?? 0;
$nama_lengkap = $_SESSION['nama_lengkap'] ?? 'User';

// Stat: Total Pesanan
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM pemesanan WHERE id_klien = ?");
$stmt->bind_param("i", $id_klien);
$stmt->execute();
$total_pesanan = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Stat: Menunggu Pembayaran (No Payment Uploaded or Rejected)
$stmt = $conn->prepare("SELECT COUNT(*) as pending FROM pemesanan p 
                        LEFT JOIN pembayaran pay ON p.id_pemesanan = pay.id_pemesanan 
                        WHERE p.id_klien = ? AND (pay.id_pembayaran IS NULL OR pay.status = 'Invalid')");
$stmt->bind_param("i", $id_klien);
$stmt->execute();
$total_pending = $stmt->get_result()->fetch_assoc()['pending'];
$stmt->close();

// Stat: Selesai (Survey Result Exists)
$stmt = $conn->prepare("SELECT COUNT(*) as done FROM pemesanan p 
                        JOIN hasil_survey s ON p.id_pemesanan = s.id_pemesanan 
                        WHERE p.id_klien = ?");
$stmt->bind_param("i", $id_klien);
$stmt->execute();
$total_done = $stmt->get_result()->fetch_assoc()['done'];
$stmt->close();

// Recent Completed Surveys (filtered by existence in hasil_survey)
$stmt = $conn->prepare("SELECT p.*, l.jenis_layanan, l.biaya as base_price, pay.status as status_pembayaran 
                        FROM pemesanan p
                        JOIN layanan l ON p.id_layanan = l.id_layanan
                        JOIN hasil_survey hs ON p.id_pemesanan = hs.id_pemesanan
                        LEFT JOIN pembayaran pay ON p.id_pemesanan = pay.id_pemesanan
                        WHERE p.id_klien = ? 
                        ORDER BY p.id_pemesanan DESC LIMIT 5");
$stmt->bind_param("i", $id_klien);
$stmt->execute();
$recent_orders = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - Survey Kost Solo</title>
    <!-- Google Fonts: Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/user_global.css">
    <style>
        /* Specific overrides for Dashboard Header Row */
        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
        }
        .clock-card {
            background: white;
            padding: 10px 20px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            border: 1px solid #E5D5C5;
            text-align: right;
            min-width: 180px;
        }
        .clock-time { font-size: 20px; font-weight: 700; color: var(--primary); font-family: monospace; }
        .clock-date { font-size: 13px; color: #A0826D; font-weight: 500; }
    </style>
</head>
<body>

    <!-- SIDEBAR BROWN -->
    <aside class="sidebar">
        <div class="logo-container">
            <img src="../logo1.png" alt="Logo" class="logo-img">
            <div class="logo-text">
                <h2>SURVEY KOST</h2>
                <span>SOLO RAYA</span>
            </div>
        </div>

        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="active">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    <span>Dashboard</span>
                </a>
            </li>
            <!-- Requested Order: Informasi Layanan -> Pemesanan -> Pembayaran -> Hasil Survey -->
            <li class="nav-item">
                <a href="layanan.php">
                   <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                   <span>Informasi Layanan</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="pemesanan.php">
                   <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                   <span>Pemesanan</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="pembayaran.php">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                    <span>Pembayaran</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="survey.php">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    <span>Hasil Survey</span>
                </a>
            </li>
            
            <li class="nav-item" style="margin-top: auto;">
                <a href="../logout.php" onclick="return confirm('Keluar dari aplikasi?')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    <span>Keluar</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- CONTENT -->
    <main class="main-wrapper">
        <div class="header-row">
            <div class="page-header" style="margin-bottom:0;">
                <h1>Halo, <?php echo htmlspecialchars($nama_lengkap); ?></h1>
                <p>Selamat datang kembali</p>
            </div>
            
            <!-- Real Time Clock Box -->
            <div class="clock-card">
                <div class="clock-time" id="realtime-clock">00:00:00</div>
                <div class="clock-date" id="realtime-date">Loading date...</div>
            </div>
        </div>

        <div class="stats-grid">
            <!-- Total Pesanan -->
            <div class="stat-box">
                <div>
                    <div class="stat-label">TOTAL PESANAN</div>
                    <div class="stat-value"><?php echo $total_pesanan; ?></div>
                </div>
                <div class="stat-icon" style="background: #FFF3E0; color: #E65100;">
                    📦
                </div>
            </div>

             <!-- Menunggu -->
            <div class="stat-box">
                <div>
                    <div class="stat-label">MENUNGGU PEMBAYARAN</div>
                    <div class="stat-value"><?php echo $total_pending; ?></div>
                </div>
                <div class="stat-icon" style="background: #FFE0B2; color: #F57C00;">
                    ⏳
                </div>
            </div>

            <!-- Selesai -->
            <div class="stat-box">
                <div>
                    <div class="stat-label">SURVEY SELESAI</div>
                    <div class="stat-value"><?php echo $total_done; ?></div>
                </div>
                <div class="stat-icon" style="background: #E8F5E9; color: #2E7D32;">
                    ✅
                </div>
            </div>
        </div>

        <div class="table-card">
            <h3>Riwayat Survey Selesai</h3>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>No Order</th>
                            <th>Tanggal</th>
                            <th>Total Biaya</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent_orders->num_rows > 0): ?>
    <?php while($row = $recent_orders->fetch_assoc()): ?>
        <?php
            // Calculate Logic
            $base = $row['base_price'] ?? 50000; // Fallback if query not updated yet (but I will update it)
            $jarak = floatval($row['jarak_dari_kantor']);
            $fee = ($jarak > 5) ? ($jarak - 5) * 2500 : 0;
            $harga = $base + $fee;
            
            // Status Logic
            $pay_stat = $row['status_pembayaran'];
            
            if ($pay_stat == 'Invalid' || $pay_stat == 'Ditolak') {
                 $s = 'Ditolak';
                 $bg='#FFEBEE'; $col='#D32F2F'; // Red
            } elseif (!$pay_stat) {
                $s = 'Menunggu Pembayaran';
                $bg='#FFF3E0'; $col='#E65100'; // Orange
            } elseif ($pay_stat == 'Menunggu') {
                $s = 'Verifikasi';
                $bg='#E3F2FD'; $col='#1565C0'; // Blue
            } elseif ($pay_stat == 'Valid' || $pay_stat == 'Selesai') {
                $s = 'Selesai';
                $bg='#E8F5E9'; $col='#2E7D32'; // Green
            } else {
                $s = $pay_stat;
                $bg='#eee'; $col='#333';
            }
        ?>
        <tr>
            <td style="font-family: monospace; font-weight: 600;">#<?php echo sprintf("%03d", $row['id_pemesanan']); ?></td>
            <td><?php echo date('d M Y', strtotime($row['jadwal_survey'])); ?></td>
            <td style="color: #8B7355; font-weight: bold;">Rp <?php echo number_format($harga, 0, ',', '.'); ?></td>
            <td>
                <span class="status-badge" style="background: <?php echo $bg; ?>; color: <?php echo $col; ?>"><?php echo $s; ?></span>
            </td>
            <td>
                <?php if ($s == 'Menunggu Pembayaran'): ?>
                    <a href="pembayaran.php?id=<?php echo $row['id_pemesanan']; ?>" style="text-decoration:none; background:#E65100; color:white; padding:5px 10px; border-radius:5px; font-size:12px;">Bayar</a>
                <?php elseif ($s == 'Ditolak'): ?>
                    <a href="pembayaran.php?id=<?php echo $row['id_pemesanan']; ?>" style="text-decoration:none; background:#D32F2F; color:white; padding:5px 10px; border-radius:5px; font-size:12px;">Bayar Ulang</a>
                <?php else: ?>
                    <a href="survey.php" style="text-decoration:none; color:#8B7355; font-weight:600; font-size:13px;">Detail</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
<?php else: ?>
                            <tr><td colspan="5" style="text-align: center; padding: 20px;">Belum ada data.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        function updateClock() {
            const now = new Date();
            
            // Time
            const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }).replace(/\./g, ':');
            document.getElementById('realtime-clock').textContent = timeString;

            // Date
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const dateString = now.toLocaleDateString('id-ID', options);
            document.getElementById('realtime-date').textContent = dateString;
        }

        setInterval(updateClock, 1000);
        updateClock(); // Initial call
    </script>
</body>
</html>
