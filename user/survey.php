<?php
require_once '../config.php';

// Check Login
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['isLoggedIn'] !== true) {
    header("Location: ../login.php");
    exit();
}

$id_klien = $_SESSION['user_id'];

// Fetch Completed Surveys
// Join pemesanan and hasil_survey tables
$sql = "SELECT p.id_pemesanan, p.alamat_kost, s.id_survey, s.tanggal_survey, s.deskripsi, s.dokumentasi_survey
        FROM pemesanan p
        JOIN hasil_survey s ON p.id_pemesanan = s.id_pemesanan
        WHERE p.id_klien = ? 
        ORDER BY s.tanggal_survey DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_klien);
$stmt->execute();
$result = $stmt->get_result();
$surveys = [];
while($row = $result->fetch_assoc()) {
    $surveys[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Survey - Survey Kost Solo</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/user_global.css?v=<?php echo time(); ?>">
    <style>
        /* Styles for Detail View */
        .survey-container { width: 100%; margin: 0 auto 50px; }
        .success-box { background: white; border: 2px solid #C8E6C9; border-radius: 16px; padding: 20px; margin-bottom: 20px; display: flex; gap: 15px; align-items: flex-start; }
        .check-icon { width: 25px; height: 25px; background: #66BB6A; border-radius: 50%; color: white; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
        .section-box { background: white; border: 2px solid #F3EBE3; border-radius: 16px; padding: 20px; margin-bottom: 20px; }
        .box-title { color: var(--primary-dark); font-weight: 700; font-size: 16px; border-bottom: 1px solid #E5D5C5; padding-bottom: 12px; margin-bottom: 15px; }
        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .detail-item { background: #FFFCF9; padding: 12px; border-radius: 10px; }
        .detail-label { font-size: 11px; text-transform: uppercase; color: #A1887F; font-weight: 700; margin-bottom: 3px; }
        .detail-value { font-size: 14px; color: #3E2723; font-weight: 600; }
        .doc-box { background: #FFF8E1; border: 1px solid #FFE0B2; border-radius: 12px; padding: 15px; display: flex; align-items: center; justify-content: space-between; }
        .doc-icon { font-size: 22px; margin-right: 12px; color: #FFA000; }
        .doc-text h4 { font-size: 15px; color: #5D4037; margin-bottom: 2px; }
        .doc-text p { font-size: 12px; color: #8D6E63; }
        .btn-open { background: #8D6E63; color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 13px; }
        .btn-open:hover { background: #6D4C41; }
        .btn-repeat { display: block; width: 100%; background: #8B7355; color: white; padding: 12px; text-align: center; border-radius: 12px; font-weight: 700; text-decoration: none; font-size: 15px; transition: 0.3s; }
        .btn-repeat:hover { background: #6B563D; }

        @media (max-width: 600px) {
            .detail-grid { grid-template-columns: 1fr; }
            .doc-box { flex-direction: column; gap: 15px; text-align: center; }
        }
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
            <li class="nav-item"><a href="dashboard.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg><span>Dashboard</span></a></li>
            <li class="nav-item"><a href="layanan.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg><span>Informasi Layanan</span></a></li>
            <li class="nav-item"><a href="pemesanan.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg><span>Pemesanan</span></a></li>
            <li class="nav-item"><a href="pembayaran.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg><span>Pembayaran</span></a></li>
            <li class="nav-item"><a href="survey.php" class="active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg><span>Hasil Survey</span></a></li>
            <li class="nav-item" style="margin-top:auto;"><a href="../logout.php" onclick="return confirm('Keluar?')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg><span>Keluar</span></a></li>
        </ul>
    </aside>

    <main class="main-wrapper">
        <?php if (isset($_GET['id'])): 
            $id_view = intval($_GET['id']);
            // Fetch Specific
            $stmt_det = $conn->prepare("SELECT p.id_pemesanan, p.alamat_kost, s.id_survey, s.tanggal_survey, s.deskripsi, s.dokumentasi_survey
                                        FROM pemesanan p
                                        JOIN hasil_survey s ON p.id_pemesanan = s.id_pemesanan
                                        WHERE p.id_klien = ? AND p.id_pemesanan = ?");
            $stmt_det->bind_param("ii", $id_klien, $id_view);
            $stmt_det->execute();
            $d_surv = $stmt_det->get_result()->fetch_assoc();
            $stmt_det->close();
        ?>
            <?php if ($d_surv): ?>
                <div class="page-header">
                    <h1 style="margin-top:10px;">Detail Hasil Survey</h1>
                </div>

                <div class="survey-container">
                    <!-- 1. Success Status -->
                    <div class="success-box">
                        <div class="check-icon">✓</div>
                        <div>
                            <h3 style="color:#2E7D32; margin-bottom:5px; font-size:16px;">Survey Selesai</h3>
                            <p style="color:#555; font-size:14px; line-height:1.4;">
                                Survey telah selesai dilakukan pada tanggal <?php echo date('d F Y', strtotime($d_surv['tanggal_survey'])); ?>. 
                                Dokumentasi lengkap dapat diakses melalui link Google Drive di bawah.
                            </p>
                        </div>
                    </div>

                    <!-- 2. Detail Survey -->
                    <div class="section-box">
                        <h4 class="box-title">Detail Survey</h4>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <div class="detail-label">ID SURVEY</div>
                                <div class="detail-value">#<?php echo sprintf("%03d", $d_surv['id_survey']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">ALAMAT KOST</div>
                                <div class="detail-value"><?php echo htmlspecialchars($d_surv['alamat_kost']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">PAKET</div>
                                <div class="detail-value">Regular (Survey)</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">TANGGAL SURVEY</div>
                                <div class="detail-value"><?php echo date('d F Y', strtotime($d_surv['tanggal_survey'])); ?></div>
                            </div>
                        </div>
                        
                        <!-- ADDED: Deskripsi Section -->
                        <div style="margin-top:15px; background:#FFFCF9; padding:15px; border-radius:10px;">
                            <div class="detail-label">CATATAN / DESKRIPSI</div>
                            <div class="detail-value" style="font-weight:400; line-height:1.5; white-space:pre-wrap;"><?php echo htmlspecialchars($d_surv['deskripsi']); ?></div>
                        </div>
                    </div>

                    <!-- 3. Dokumentasi -->
                    <div class="section-box">
                        <h4 class="box-title">Dokumentasi Survey</h4>
                        <div class="doc-box">
                            <div style="display:flex; align-items:center;">
                                <div class="doc-icon">📂</div>
                                <div class="doc-text">
                                    <h4>Link Drive Hasil Survey</h4>
                                    <p>Foto & Video</p>
                                </div>
                            </div>
                            <a href="<?php echo htmlspecialchars($d_surv['dokumentasi_survey']); ?>" target="_blank" class="btn-open">Buka</a>
                        </div>
                    </div>

                    <a href="pemesanan.php" class="btn-repeat">Pesan Survey Lagi</a>
                </div>

            <?php else: ?>
                <p>Data tidak ditemukan.</p>
                <a href="survey.php">Kembali</a>
            <?php endif; ?>

        <?php else: ?>
            <!-- LIST / HISTORY VIEW -->
            <div class="page-header">
                <h1>Riwayat Hasil Survey</h1>
                <p>Daftar survey yang telah diselesaikan</p>
            </div>

            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>ID Survey</th>
                            <th>Tanggal Survey</th>
                            <th>Alamat Kost</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($surveys) > 0): ?>
                            <?php foreach($surveys as $survey): ?>
                            <tr>
                                <td style="font-family:monospace; font-weight:600;">#<?php echo sprintf("%03d", $survey['id_survey']); ?></td>
                                <td><?php echo date('d M Y', strtotime($survey['tanggal_survey'])); ?></td>
                                <td><?php echo htmlspecialchars($survey['alamat_kost']); ?></td>
                                <td>
                                    <a href="survey.php?id=<?php echo $survey['id_pemesanan']; ?>" 
                                       style="background:var(--sidebar-gradient); color:white; padding:6px 12px; border-radius:8px; text-decoration:none; font-size:13px; font-weight:600;">
                                       Lihat Detail
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align:center; padding:20px;">Belum ada riwayat survey.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if(count($surveys) == 0): ?>
            <div style="text-align:center; margin-top:20px;">
                <a href="pemesanan.php" class="btn-primary">Buat Pesanan Baru</a>
            </div>
            <?php endif; ?>

        <?php endif; ?>
    </main>

</body>
</html>
