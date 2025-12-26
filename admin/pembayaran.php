<?php
require_once '../config.php';

// Check Admin Login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login_admin.php");
    exit();
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// Handle Actions (Approve/Reject)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id_pembayaran = $_POST['id_pembayaran'];
    $action = $_POST['action'];
    
    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE pembayaran SET status = 'Valid' WHERE id_pembayaran = ?");
        $stmt->bind_param("i", $id_pembayaran);
        $stmt->execute();
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE pembayaran SET status = 'Invalid' WHERE id_pembayaran = ?");
        $stmt->bind_param("i", $id_pembayaran);
        $stmt->execute();
    }
    
    header("Location: pembayaran.php");
    exit();
}

// Fetch Payments with details
$sql = "SELECT pay.*, p.alamat_kost, k.nama_lengkap, l.jenis_layanan, p.id_pemesanan
        FROM pembayaran pay 
        JOIN pemesanan p ON pay.id_pemesanan = p.id_pemesanan 
        JOIN klien k ON p.id_klien = k.id_klien
        JOIN layanan l ON p.id_layanan = l.id_layanan 
        ORDER BY pay.id_pembayaran DESC";
$result = $conn->query($sql);

$payments = [];
$js_detail_data = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        
        $status_key = 'Menunggu';
        $status_bg = 'status-pending';
        
        if ($row['status'] == 'Valid') { 
            $status_key = 'Berhasil'; 
            $status_bg = 'status-success'; 
        }
        elseif ($row['status'] == 'Invalid') { 
            $status_key = 'Ditolak'; 
            $status_bg = 'status-danger'; 
        }
        
        $row['formatted_jumlah'] = 'Rp ' . number_format($row['total_pembayaran'], 0, ',', '.');
        $row['formatted_trx'] = sprintf("#TRX%04d", $row['id_pembayaran']);
        // Match pemesanan_admin format (Simple #ID)
        $row['formatted_ord'] = sprintf("#%02d", $row['id_pemesanan']); 
        
        // Fix Proof Path Logic
        $proof_img = $row['bukti_pembayaran'];
        if (strpos($proof_img, 'uploads/payments/') === false) {
             $row['link_bukti'] = '../uploads/payments/' . $proof_img;
        } else {
             $row['link_bukti'] = '../' . $proof_img; 
        }

        $row['status_label'] = $status_key;
        $row['status_class'] = $status_bg;
        // Pass raw status for JS logic
        $row['raw_status'] = $row['status'];

        $payments[] = $row;
        $js_detail_data[$row['id_pembayaran']] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Data Pembayaran</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin_global.css?v=<?php echo time(); ?>">
</head>
<body>

    <!-- Sidebar -->
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
            <li><a href="pembayaran.php" class="active"><span class="icon">💳</span> Data Pembayaran</a></li>
            <li><a href="survey.php"><span class="icon">📋</span> Hasil Survey</a></li>
            <li><a href="laporan.php"><span class="icon">📄</span> Laporan</a></li>
            <li><a href="../logout.php" class="logout-btn"><span class="icon">🚪</span> Keluar</a></li>
        </ul>
    </aside>

    <!-- Main -->
    <main class="main-content">
        <div class="page-header">
            <div class="page-title">
                <h1>Manajemen Data Pembayaran</h1>
                <p>Validasi bukti transfer dari klien</p>
            </div>
        </div>

        <div class="search-container-full">
            <input type="text" placeholder="Cari ID, Nama..." id="searchInput">
            <span class="search-icon">🔍</span>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID Pembayaran</th>
                        <th>ID Pemesanan</th>
                        <th>Klien</th>
                        <th>Jumlah</th>
                        <th>Metode</th>
                        <th>Tanggal</th>
                        <th>Bukti</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($payments) > 0): ?>
                        <?php foreach($payments as $row): ?>
                        <tr>
                            <td style="font-weight:700;">#<?php echo $row['id_pembayaran']; ?></td>
                            <td style="font-weight:700;">#<?php echo $row['id_pemesanan']; ?></td>
                            <td style="font-weight:600;"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td><?php echo $row['formatted_jumlah']; ?></td>
                            <td><span style="background:#F5F5F5; padding:4px 8px; border-radius:6px; font-weight:600; font-size:12px; color:#5D4037;"><?php echo strtoupper($row['metode_pembayaran'] ?? 'Transfer'); ?></span></td>
                            <td><?php echo date('d M Y', strtotime($row['tanggal_pembayaran'])); ?></td>
                            <td><a href="<?php echo $row['link_bukti']; ?>" target="_blank" class="btn-action-view">Lihat</a></td>
                            <td>
                                <span class="status-badge <?php echo $row['status_class']; ?>">
                                    <?php echo $row['status_label']; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="openModal(<?php echo $row['id_pembayaran']; ?>)">Detail</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="9" style="text-align:center; color:#999;">Belum ada data.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal Detail Action -->
    <div id="verifModal" class="modal">
        <div class="modal-card" style="width:500px; padding:0; overflow:hidden;">
            <div class="modal-top" style="background: var(--header-brown); padding: 15px 25px;">
                <h3 style="font-size:18px;">Verifikasi Pembayaran</h3>
                <span class="close-btn" onclick="closeModal()">✕</span>
            </div>
            
            <div class="modal-body" style="padding:25px; background:white;">
                
                <div style="margin-bottom:12px;">
                    <label style="font-size:11px; color:#999; text-transform:uppercase; display:block; margin-bottom:4px;">ID Pembayaran</label>
                    <div style="font-weight:700; color:#333;" id="dId">-</div>
                </div>

                <div style="margin-bottom:12px;">
                    <label style="font-size:11px; color:#999; text-transform:uppercase; display:block; margin-bottom:4px;">ID Pesanan</label>
                    <div style="font-weight:700; color:#333;" id="dOrd">-</div>
                </div>

                <div style="margin-bottom:12px;">
                    <label style="font-size:11px; color:#999; text-transform:uppercase; display:block; margin-bottom:4px;">Nama Klien</label>
                    <div style="font-weight:700; color:#333; font-size:16px;" id="dKlien">-</div>
                </div>

                <div style="margin-bottom:12px;">
                    <label style="font-size:11px; color:#999; text-transform:uppercase; display:block; margin-bottom:6px;">Jumlah Pembayaran</label>
                    <div style="border:1px solid #D7C7B5; border-radius:8px; padding:10px 15px; font-size:18px; font-weight:800; color:var(--primary);" id="dJumlah">
                        Rp -
                    </div>
                </div>
                
                <div style="margin-bottom:12px;">
                    <label style="font-size:11px; color:#999; text-transform:uppercase; display:block; margin-bottom:4px;">Metode Pembayaran</label>
                    <div style="color:#555; font-weight:600;" id="dMetode">-</div>
                </div>

                <div style="margin-bottom:15px; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <label style="font-size:11px; color:#999; text-transform:uppercase; display:block; margin-bottom:4px;">Tanggal</label>
                        <div style="color:#555;" id="dTanggal">-</div>
                    </div>
                    <div>
                         <a href="#" target="_blank" id="dBukti" style="color:var(--primary); font-weight:600; font-size:13px; text-decoration:underline;">Lihat Bukti Foto</a>
                    </div>
                </div>
                
                <div style="margin-bottom:20px;">
                    <label style="font-size:11px; color:#999; text-transform:uppercase; display:block; margin-bottom:6px;">Status Saat Ini</label>
                    <div style="background:#FFF8E1; color:#F57F17; padding:8px 15px; border-radius:8px; font-weight:700; font-size:13px;" id="dStatus">
                        -
                    </div>
                </div>

                <!-- Footer Actions Inside Body for cleaner cut -->
                <div id="modalActionFooter" style="display:flex; gap:10px; margin-top:10px;">
                     <form method="POST" id="actForm" style="display:flex; width:100%; gap:10px;">
                        <input type="hidden" name="id_pembayaran" id="inputId">
                        <button type="submit" name="action" value="reject" class="btn btn-action-delete" style="flex:1; padding:12px; font-size:14px; font-weight:bold; border-radius:8px;">Tolak</button>
                        <button type="submit" name="action" value="approve" class="btn btn-primary" style="flex:1; padding:12px; font-size:14px; font-weight:bold; border-radius:8px; background:#4CAF50; border:none;">Setuju</button> <!-- Green for Setuju -->
                    </form>
                </div>
                <div id="modalDoneFooter" style="display:none; justify-content:center; color:#888; font-style:italic; margin-top:20px;">
                    Pembayaran ini telah diproses (Final).
                </div>
            </div>
        </div>
    </div>

    <script>
        const dataMap = <?php echo json_encode($js_detail_data); ?>;

        function openModal(id) {
            const d = dataMap[id];
            if(d) {
                document.getElementById('inputId').value = d.id_pembayaran;
                document.getElementById('dId').innerText = '#' + d.id_pembayaran;
                document.getElementById('dOrd').innerText = '#' + d.id_pemesanan;
                document.getElementById('dKlien').innerText = d.nama_lengkap;
                document.getElementById('dJumlah').innerText = d.formatted_jumlah;
                document.getElementById('dMetode').innerText = (d.metode_pembayaran || 'Transfer').toUpperCase();
                document.getElementById('dTanggal').innerText = d.tanggal_pembayaran;
                document.getElementById('dStatus').innerText = d.status_label;
                document.getElementById('dBukti').href = d.link_bukti;
                
                // ACTION LOGIC: If 'Menunggu', show actions. Else hide.
                const footerAction = document.getElementById('modalActionFooter');
                const footerDone = document.getElementById('modalDoneFooter');
                
                // Assuming raw_status is what determines logic: 'Pending' in DB might mean 'Menunggu' ?
                // The PHP code does: if status == 'Menunggu' (implicit default) or logic?
                // Actually my PHP logic above says: default 'Menunggu'.
                // DB ENUM says: 'Pending', 'Valid', 'Invalid'.
                // But p.status or pay.status?
                // The DB schema says ENUM('Pending','Valid','Invalid').
                // PHP code: if 'Valid' -> Berhasil. if 'Invalid' -> Ditolak. Else -> Menunggu.
                
                // So if raw_status is 'Valid' or 'Invalid', hide actions.
                if (d.raw_status === 'Valid' || d.raw_status === 'Invalid' || d.raw_status === 'Selesai') {
                    footerAction.style.display = 'none';
                    footerDone.style.display = 'flex';
                } else {
                    footerAction.style.display = 'flex';
                    footerDone.style.display = 'none';
                }

                document.getElementById('verifModal').classList.add('show');
            }
        }
        function closeModal() {
            document.getElementById('verifModal').classList.remove('show');
        }
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let val = this.value.toLowerCase();
            let rows = document.querySelectorAll('tbody tr');
            rows.forEach(r => {
                let txt = r.innerText.toLowerCase();
                r.style.display = txt.includes(val) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
