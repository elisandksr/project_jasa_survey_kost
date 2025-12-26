<?php
require_once '../config.php';

// Cek login admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login_admin.php");
    exit();
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// Fetch Orders
$sql = "SELECT p.*, k.nama_lengkap, k.no_wa, l.jenis_layanan, 
               pay.status as status_bayar,
               h.id_survey
        FROM pemesanan p 
        JOIN klien k ON p.id_klien = k.id_klien 
        JOIN layanan l ON p.id_layanan = l.id_layanan 
        LEFT JOIN pembayaran pay ON p.id_pemesanan = pay.id_pemesanan
        LEFT JOIN hasil_survey h ON p.id_pemesanan = h.id_pemesanan
        ORDER BY p.id_pemesanan DESC";
$result = $conn->query($sql);

$orders = [];
$js_detail_data = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        
        // DERIVE STATUS LOGIC
        $status_display = 'Menunggu';
        $status_bg = 'status-pending'; // CSS class mapping
        $action_label = 'Verifikasi Pembayaran';
        $action_target = 'pembayaran_admin.php'; 
        
        if ($row['id_survey']) {
            $status_display = 'Selesai';
            $status_bg = 'status-success'; 
            $action_label = 'Lihat Laporan';
            $action_target = 'survey.php'; 
        } elseif ($row['status_bayar'] == 'Valid') {
             $status_display = 'Diproses'; 
             $status_bg = 'status-info'; 
             $action_label = 'Selesai (Input Survey)';
             $action_target = 'survey.php?id_simpan=' . $row['id_pemesanan']; 
        } elseif ($row['status_bayar'] == 'Invalid') {
             $status_display = 'Ditolak';
             $status_bg = 'status-danger';
             $action_label = 'Cek Pembayaran';
             $action_target = 'pembayaran.php'; 
        } elseif ($row['status_bayar'] == 'Menunggu' || $row['status_bayar'] == null) {
            $status_display = 'Menunggu'; 
            $status_bg = 'status-pending';
            $action_label = 'Cek Pembayaran';
            $action_target = 'pembayaran.php';
        }

        $row['status_derived'] = $status_display;
        
        $start_time = strtotime($row['waktu_survey']);
        $end_time = $start_time + (2 * 3600); // +2 hours
        $time_range = date('H.i', $start_time) . ' - ' . date('H.i', $end_time) . ' WIB';

        $orders[] = $row;
        
        $js_detail_data[$row['id_pemesanan']] = [
            'id' => sprintf("%02d", $row['id_pemesanan']), 
            'real_id' => $row['id_pemesanan'],
            'paket' => $row['jenis_layanan'],
            'jarak' => ($row['jarak_dari_kantor'] ?? '-') . 'Km',
            'alamat' => $row['alamat_kost'],
            'wa' => $row['no_wa'],
            'jadwal' => date('d F Y', strtotime($row['jadwal_survey'])),
            'waktu' => $time_range,
            'status' => $status_display, 
            'catatan' => $row['catatan_tambahan'] ?? '-',
            'status_class' => $status_bg,
            'btn_text' => $action_label,
            'btn_url'  => $action_target
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Data Pemesanan</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin_global.css?v=<?php echo time(); ?>">
    <style>
        .search-bar { width: 100%; max-width: 100%; position: relative; } /* Override if needed */
        .search-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #D7C7B5;}
        .search-bar input { padding-left: 45px; }
        
        /* Specific modal layout for this page if using grid */
        .modal-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
    </style>
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
            <li><a href="pemesanan.php" class="active"><span class="icon">📝</span> Data Pemesanan</a></li>
            <li><a href="pembayaran.php"><span class="icon">💳</span> Data Pembayaran</a></li>
            <li><a href="survey.php"><span class="icon">📋</span> Hasil Survey</a></li>
            <li><a href="laporan.php"><span class="icon">📄</span> Laporan</a></li>
            <li><a href="../logout.php" class="logout-btn"><span class="icon">🚪</span> Keluar</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <div class="page-title">
                <h1>Manajemen Data Pemesanan</h1>
                <p>Verifikasi & pantau jadwal survey klien</p>
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
                        <th>ID</th>
                        <th>Jenis Paket</th>
                        <th>Jarak</th>
                        <th>Alamat Kost</th>
                        <th>No WA</th>
                        <th>Jadwal Survey</th>
                        <th>Waktu</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orders as $row): 
                        $sTime = strtotime($row['waktu_survey']);
                        $range = date('H.i', $sTime) . ' - ' . date('H.i', $sTime + 7200) . ' WIB';
                    ?>
                        <tr>
                            <td>#<?php echo $row['id_pemesanan']; ?></td>
                            <td><?php echo $row['jenis_layanan']; ?></td>
                            <td><?php echo ($row['jarak_dari_kantor'] ?? '0') . 'Km'; ?></td>
                            <td><?php echo htmlspecialchars(substr($row['alamat_kost'], 0, 15)); ?>...</td>
                            <td><?php echo $row['no_wa']; ?></td>
                            <td><?php echo date('d M Y', strtotime($row['jadwal_survey'])); ?></td>
                            <td style="white-space:nowrap;"><?php echo $range; ?></td>
                            <td>
                                <span class="status-badge <?php echo $js_detail_data[$row['id_pemesanan']]['status_class']; ?>">
                                    <?php echo $row['status_derived']; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="openModal('<?php echo $row['id_pemesanan']; ?>')">Detail</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal Layout -->
    <div id="modalDetail" class="modal">
        <div class="modal-card" style="width:800px; padding:0; overflow:hidden;">
            <div class="modal-top" style="background: var(--header-brown); padding: 15px 25px;">
                <h3 style="font-size:18px;">Detail Pemesanan</h3>
                <span class="close-btn" onclick="closeModal()">✕</span>
            </div>
            
            <div class="modal-body" style="padding:30px; background:white;">
                <div class="modal-grid-2" style="display:grid; grid-template-columns: 1fr 1fr; gap:40px;">
                    <!-- Left Col -->
                    <div>
                        <div class="field-group" style="margin-bottom:15px;">
                            <span class="field-label" style="display:block; font-size:11px; text-transform:uppercase; color:#999; margin-bottom:5px; letter-spacing:0.5px;">ID PEMESANAN</span>
                            <span class="field-value" style="display:block; font-size:15px; font-weight:700; color:#444;" id="mId">-</span>
                        </div>
                        <div class="field-group" style="margin-bottom:15px;">
                            <span class="field-label" style="display:block; font-size:11px; text-transform:uppercase; color:#999; margin-bottom:5px; letter-spacing:0.5px;">JENIS PAKET</span>
                            <span class="field-value" style="display:block; font-size:15px; font-weight:600; color:#444;" id="mPaket">-</span>
                        </div>
                        <div class="field-group" style="margin-bottom:15px;">
                            <span class="field-label" style="display:block; font-size:11px; text-transform:uppercase; color:#999; margin-bottom:5px; letter-spacing:0.5px;">JARAK</span>
                            <span class="field-value" style="display:block; font-size:15px; font-weight:600; color:#444;" id="mJarak">-</span>
                        </div>
                        <div class="field-group" style="margin-bottom:15px;">
                            <span class="field-label" style="display:block; font-size:11px; text-transform:uppercase; color:#999; margin-bottom:5px; letter-spacing:0.5px;">NO WHATSAPP</span>
                            <span class="field-value" style="display:block; font-size:15px; font-weight:600; color:#444;" id="mWa">-</span>
                        </div>
                        <div class="field-group" style="margin-bottom:15px;">
                            <span class="field-label" style="display:block; font-size:11px; text-transform:uppercase; color:#999; margin-bottom:5px; letter-spacing:0.5px;">JADWAL SURVEY</span>
                            <span class="field-value" style="display:block; font-size:15px; font-weight:600; color:#444;" id="mTanggal">-</span>
                        </div>
                        <div class="field-group" style="margin-bottom:15px;">
                            <span class="field-label" style="display:block; font-size:11px; text-transform:uppercase; color:#999; margin-bottom:5px; letter-spacing:0.5px;">ALAMAT KOST</span>
                            <span class="field-value" style="display:block; font-size:14px; font-weight:600; color:#444; line-height:1.5;" id="mAlamat">-</span>
                        </div>
                    </div>
                    
                    <!-- Right Col -->
                    <div>
                        <div class="field-group" style="margin-bottom:15px;">
                            <span class="field-label" style="display:block; font-size:11px; text-transform:uppercase; color:#999; margin-bottom:5px; letter-spacing:0.5px;">WAKTU SURVEY</span>
                            <span class="field-value" style="display:block; font-size:15px; font-weight:600; color:#444;" id="mWaktu">-</span>
                        </div>
                        <div class="field-group" style="margin-bottom:15px;">
                            <span class="field-label" style="display:block; font-size:11px; text-transform:uppercase; color:#999; margin-bottom:5px; letter-spacing:0.5px;">CATATAN</span>
                            <span class="field-value" style="display:block; font-size:14px; color:#444; font-style:italic;" id="mCatatan">-</span>
                        </div>
                        <div class="field-group" style="margin-bottom:25px;">
                            <span class="field-label" style="display:block; font-size:11px; text-transform:uppercase; color:#999; margin-bottom:8px; letter-spacing:0.5px;">STATUS SAAT INI</span>
                            <div class="status-box" style="background:#E8F5E9; color:#2E7D32; padding:8px 15px; border-radius:6px; display:inline-block; font-weight:700; font-size:13px;" id="mStatusText">
                                -
                            </div>
                        </div>

                        <!-- Action Section styled like 'Update Status' -->
                        <div style="border-top:1px solid #eee; pt:20px; margin-top:20px;">
                            <span class="field-label" style="display:block; font-size:11px; text-transform:uppercase; color:#999; margin:15px 0 8px 0; letter-spacing:0.5px;">TINDAKAN</span>
                            <a id="btnAction" class="btn btn-primary" style="width:100%; text-align:center; padding:12px; font-size:14px; font-weight:600; border-radius:8px;"></a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- No footer needed as action is inside body right col -->
        </div>
    </div>

    <script>
        const details = <?php echo json_encode($js_detail_data); ?>;

        function openModal(id) {
            const d = details[id];
            if(d) {
                document.getElementById('mId').innerText = '#' + d.real_id;
                document.getElementById('mPaket').innerText = d.paket;
                document.getElementById('mJarak').innerText = d.jarak;
                document.getElementById('mWa').innerText = d.wa;
                document.getElementById('mTanggal').innerText = d.jadwal;
                document.getElementById('mAlamat').innerText = d.alamat;
                document.getElementById('mWaktu').innerText = d.waktu; 
                document.getElementById('mCatatan').innerText = d.catatan;
                document.getElementById('mStatusText').innerText = d.status;
                
                const btn = document.getElementById('btnAction');
                btn.innerText = d.btn_text;
                btn.href = d.btn_url;
                
                document.getElementById('modalDetail').classList.add('show');
            }
        }
        function closeModal() {
            document.getElementById('modalDetail').classList.remove('show');
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
