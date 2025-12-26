<?php
require_once '../config.php';

// Check Admin Login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login_admin.php");
    exit();
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';

$success_msg = '';
$error_msg = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'simpan_hasil') {
    $id_pemesanan = $_POST['id_pemesanan'];
    $tanggal_realisasi = $_POST['tanggal_survey'];
    $link_laporan = $_POST['link_drive'];
    $hasil_survey = $_POST['deskripsi_survey'];

    if (empty($id_pemesanan) || empty($tanggal_realisasi) || empty($link_laporan)) {
        $error_msg = "Mohon lengkapi data wajib.";
    } else {
        // Insert into hasil_survey table with 5 fields check
        $stmt = $conn->prepare("INSERT INTO hasil_survey (id_pemesanan, deskripsi, tanggal_survey, dokumentasi_survey) VALUES (?, ?, ?, ?)");
        
        if ($stmt) {
            $stmt->bind_param("isss", $id_pemesanan, $hasil_survey, $tanggal_realisasi, $link_laporan);
            
            if ($stmt->execute()) {
                // Update pemesanan status removed as per schema revert
                // $conn->query("UPDATE pemesanan SET status = 'Selesai' WHERE id_pemesanan = $id_pemesanan");
                $success_msg = "Hasil survey berhasil disimpan!";
            } else {
                $error_msg = "Terjadi kesalahan saat menyimpan: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_msg = "Database Error (Prepare): " . $conn->error;
        }
    }
}

// Pre-select ID logic
$selected_id = $_GET['id_simpan'] ?? '';

// Fetch Orders that are 'Siap Survey'
$sql = "SELECT p.*, k.nama_lengkap, k.no_wa, pay.status as status_bayar 
        FROM pemesanan p 
        JOIN klien k ON p.id_klien = k.id_klien 
        JOIN pembayaran pay ON p.id_pemesanan = pay.id_pemesanan
        LEFT JOIN hasil_survey h ON p.id_pemesanan = h.id_pemesanan
        WHERE pay.status = 'Valid' AND h.id_survey IS NULL
        ORDER BY p.jadwal_survey ASC";
$result = $conn->query($sql);

$orders = [];
$js_orders_data = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $orders[] = $row;
        $js_orders_data[$row['id_pemesanan']] = [
            'alamat' => $row['alamat_kost'],
            'wa' => $row['no_wa'],
            'tanggal' => $row['jadwal_survey']
        ];
    }
}

// Fetch Completed Surveys (History)
$sql_history = "SELECT h.*, p.alamat_kost, k.nama_lengkap 
                FROM hasil_survey h 
                JOIN pemesanan p ON h.id_pemesanan = p.id_pemesanan 
                JOIN klien k ON p.id_klien = k.id_klien 
                ORDER BY h.id_survey DESC LIMIT 10";
$res_history = $conn->query($sql_history);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Hasil Survey - Admin</title>
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
            <li><a href="survey.php" class="active"><span class="icon">📋</span> Hasil Survey</a></li>
            <li><a href="laporan.php"><span class="icon">📄</span> Laporan</a></li>
            <li><a href="../logout.php" class="logout-btn"><span class="icon">🚪</span> Keluar</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <div class="page-title">
                <h1>Hasil Survey</h1>
                <p>Masukan & kelola laporan hasil survey</p>
            </div>
        </div>

        <?php if ($success_msg): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr; gap: 30px;">
            <!-- Form Input -->
            <div class="form-section">
                <h3 class="table-header-title" style="border:none; padding:0; margin-bottom:20px;">📝 Input Hasil Survey</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="simpan_hasil">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Pilih Pemesanan (Siap Survey)</label>
                            <select name="id_pemesanan" id="idPemesanan" required>
                                <option value="">-- Pilih Order --</option>
                                <?php foreach($orders as $o): ?>
                                <option value="<?php echo $o['id_pemesanan']; ?>" <?php echo ($selected_id == $o['id_pemesanan']) ? 'selected' : ''; ?>>
                                    #<?php echo $o['id_pemesanan']; ?> - <?php echo htmlspecialchars(substr($o['alamat_kost'], 0, 20)); ?>... (<?php echo $o['nama_lengkap']; ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <small style="color:#aaa; font-size:11px;">*Hanya menampilkan order yang Valid</small>
                        </div>

                        <div class="form-group">
                            <label>Tanggal Survey</label>
                            <input type="date" name="tanggal_survey" id="tanggalSurvey" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Link Laporan</label>
                        <input type="url" name="link_drive" placeholder="https://..." required>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi & Catatan</label>
                        <textarea name="deskripsi_survey" placeholder="Ringkasan hasil survey..." required style="height:100px;"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">💾 Simpan Hasil Survey</button>
                </form>
            </div>

            <!-- Table History -->
            <div class="table-section">
                <div class="table-header-title">📋 Riwayat Survey Terakhir</div>
                <table>
                    <thead>
                        <tr>
                            <th>ID Survey</th>
                            <th>Tgl Survey</th>
                            <th>Klien</th>
                            <th>Lokasi Kost</th>
                            <th>Laporan</th>
                            <th>Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res_history && $res_history->num_rows > 0): ?>
                            <?php while($h = $res_history->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $h['id_survey']; ?></td>
                                <td><?php echo date('d M Y', strtotime($h['tanggal_survey'])); ?></td>
                                <td style="font-weight:600;"><?php echo htmlspecialchars($h['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars(substr($h['alamat_kost'], 0, 30)); ?>...</td>
                                <td><a href="<?php echo htmlspecialchars($h['dokumentasi_survey']); ?>" target="_blank" class="btn-action-view">🔗 Lihat</a></td>
                                <td><?php echo htmlspecialchars(substr($h['deskripsi'], 0, 50)); ?>...</td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align:center; color:#999;">Belum ada hasil survey tersimpan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        const orderData = <?php echo json_encode($js_orders_data); ?>;
        
        // Auto trigger date logic if pre-selected
        function updateDate(val) {
             if(val && orderData[val]) {
                const rawDate = orderData[val].tanggal; 
                if(rawDate) {
                    const datePart = rawDate.split(' ')[0];
                    document.getElementById('tanggalSurvey').value = datePart;
                }
            }
        }

        const sel = document.getElementById('idPemesanan');
        sel.addEventListener('change', function() { updateDate(this.value); });
        
        // Init if selected
        if(sel.value) { updateDate(sel.value); }
    </script>
</body>
</html>
