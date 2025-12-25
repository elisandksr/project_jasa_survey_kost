<?php
require_once '../config.php';
// Public page, but usually accessed after login or via dashboard.
// If user is logged in, show sidebar. If not, maybe redirect or show a simplified header?
// User asked to match dashboard, implying logged in state.
// Let's assume logged in for sidebar consistency, or check session.
$nama_lengkap = $_SESSION['nama_lengkap'] ?? 'Guest';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informasi Layanan - Survey Kost Solo</title>
    <!-- Google Fonts: Outfit -->
    <!-- Google Fonts: Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/user_global.css?v=<?php echo time(); ?>">
    <style>
        /* Compact Grid Container for this page */
        .info-grid {
            /* max-width removed to match Pemesanan page (inherit global width) */
            margin: 0 auto 30px;
            gap: 30px; /* Increased gap "seperti diawal" */
            display: grid; 
            grid-template-columns: 1fr 1fr; /* Maintain 2x2 grid */
        }
        
        @media (max-width: 768px) {
            .info-grid { grid-template-columns: 1fr; }
        }

        /* CONTENT CARDS */
        .content-card {
            background: white;
            border-radius: var(--radius-xl);
            padding: 25px; 
            box-shadow: var(--shadow-sm);
            margin-bottom: 0;
            border: 1px solid rgba(255,255,255,1);
            position: relative; overflow: hidden;
            width: 100%;
        }

        .content-card h2 {
            font-size: 18px; /* Slightly restored size */
            color: var(--primary-dark);
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #F3EBE3;
        }

        .info-text { color: #6D635B; line-height: 1.6; font-size: 14px; } 

        /* PACKAGES */
        .package-grid { display: grid; gap: 20px; grid-template-columns: 1fr 1fr; } /* Side-by-side packages */
        .package-item {
            border: 1px solid #F3EBE3;
            border-radius: 12px;
            padding: 15px;
            background: #FFFCF9;
        }
        
        /* DISTANCE FEE */
        .distance-box { display: flex; gap: 15px; align-items: start; }
        .icon-circle {
            width: 45px; height: 45px; background: #FFF3E0; color: var(--accent);
            border-radius: 15px; display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; font-size: 22px;
        }

        /* TERMS */
        .term-item { display: flex; gap: 10px; margin-bottom: 8px; color: #5D5049; font-size: 13.5px; }
        .term-bullet { color: var(--primary); font-weight: bold; font-size: 16px; line-height: 1; }

        /* CTA */
        .btn-order-big {
            display: block; width: 100%; max-width: 220px; margin: 25px auto 0;
            padding: 12px;
            background: var(--sidebar-gradient);
            color: white; text-align: center; font-weight: 700; font-size: 14px;
            border-radius: 12px; text-decoration: none;
            box-shadow: 0 5px 15px rgba(176, 137, 104, 0.25);
            transition: 0.3s;
        }
        .btn-order-big:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(176, 137, 104, 0.35); }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
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
                <a href="dashboard.php">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="layanan.php" class="active">
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
                <a href="../logout.php" onclick="return confirm('Keluar?')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    <span>Keluar</span>
                </a>
            </li>
        </ul>
    </aside>

    <main class="main-wrapper">
        <div class="layout-compact">
            <div class="page-header">
                <h1>Informasi Layanan</h1>
                <p>Pahami layanan kami sebelum memesan</p>
            </div>

        <div class="info-grid">
            <!-- 1. Layanan -->
            <div class="content-card">
                <h2>Layanan Survey Kost</h2>
                <p class="info-text">Kami menyediakan jasa survey kost profesional dengan dokumentasi lengkap berupa foto, video, dan deskripsi detail kondisi kost yang akan membantu Anda dalam mengambil keputusan tanpa harus datang langsung ke lokasi.</p>
            </div>

            <!-- 2. Paket -->
            <div class="content-card">
                <h2>Pilihan Paket</h2>
                <div class="package-grid">
                    <?php
                    $res_layanan = $conn->query("SELECT * FROM layanan ORDER BY biaya ASC");
                    if ($res_layanan && $res_layanan->num_rows > 0):
                        while($lay = $res_layanan->fetch_assoc()):
                    ?>
                    <div class="package-item" style="padding:10px 15px; border-radius:10px;">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <div style="font-weight:700; color:var(--primary-dark); font-size:14px;"><?php echo htmlspecialchars($lay['jenis_layanan']); ?></div>
                            <div style="font-weight:800; color:var(--accent); font-size:15px;">Rp <?php echo number_format($lay['biaya'], 0, ',', '.'); ?></div>
                        </div>
                        <div style="font-size:11px; color:#555; text-align:left; margin-top:8px; font-weight: 500;">
                             <?php echo htmlspecialchars($lay['keterangan']); ?>
                        </div>
                        <div style="font-size:11px; color:#888; text-align:left; margin-top:4px;">
                             <span style="color: var(--primary);">•</span> <?php echo htmlspecialchars($lay['ketentuan']); ?>
                        </div>
                    </div>
                    <?php 
                        endwhile; 
                    else:
                    ?>
                        <p>Layanan belum tersedia.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 3. Biaya -->
            <div class="content-card">
                <h2>Biaya Jarak Tempuh</h2>
                <div class="distance-box" style="gap: 15px;">
                    <div class="icon-circle" style="width:40px; height:40px; font-size:20px;">📍</div>
                    <div>
                        <p class="info-text" style="margin-bottom: 5px;"><strong>Gratis</strong> hingga 5 km dari kantor kami.</p>
                        <p class="info-text">Lebih dari 5 km dikenakan biaya tambahan <strong>Rp 2.500/km</strong>.</p>
                    </div>
                </div>
            </div>

            <!-- 4. Ketentuan -->
            <div class="content-card">
                <h2>Ketentuan Survey</h2>
                <div class="term-item" style="margin-bottom:8px;">
                    <span class="term-bullet" style="font-size:14px;">•</span>
                    <span>Survey dilakukan setelah pembayaran terverifikasi.</span>
                </div>
                <div class="term-item" style="margin-bottom:8px;">
                    <span class="term-bullet" style="font-size:14px;">•</span>
                    <span>Hasil survey dikirim via (Link GDrive).</span>
                </div>
                <div class="term-item" style="margin-bottom:8px;">
                    <span class="term-bullet" style="font-size:14px;">•</span>
                    <span>Dokumentasi mencakup kondisi kamar, WC, Dapur & lingkungan sekitar.</span>
                </div>
                <div class="term-item" style="margin-bottom:8px;">
                    <span class="term-bullet" style="font-size:14px;">•</span>
                    <span>Pembayaran via Transfer Bank dan E-wallet.</span>
                </div>
            </div>
        </div>

        <a href="pemesanan.php" class="btn-order-big">Pesan Survey Sekarang</a>
        </div>
    </main>

</body>
</html>
