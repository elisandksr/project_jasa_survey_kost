<?php
require_once '../config.php';

// Check Login
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['isLoggedIn'] !== true) {
    header("Location: ../login.php");
    exit();
}

$id_klien = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Helper to calculate price (Base + Distance)
function calculate_price($jarak, $id_layanan, $conn) {
    if (!$id_layanan) return 50000;
    // Fetch base price
    $stmt = $conn->prepare("SELECT biaya FROM layanan WHERE id_layanan = ?");
    $stmt->bind_param("i", $id_layanan);
    $stmt->execute();
    $res = $stmt->get_result();
    $base = ($row = $res->fetch_assoc()) ? $row['biaya'] : 50000;
    $stmt->close();
    
    $jarak = floatval($jarak);
    $fee = ($jarak > 5) ? ($jarak - 5) * 2500 : 0;
    return $base + $fee;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_bukti') {
    $id_pemesanan = $_POST['id_pemesanan'];
    $jumlah = $_POST['jumlah'];
    
    // File Upload
    if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        $filename = $_FILES['bukti']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = "PAY-" . $id_pemesanan . "-" . time() . "." . $ext;
            $destination = "../uploads/payments/" . $new_filename;
            
            // Create dir if not exists
            if (!file_exists('../uploads/payments/')) {
                mkdir('../uploads/payments/', 0777, true);
            }
            
            if (move_uploaded_file($_FILES['bukti']['tmp_name'], $destination)) {
                // Insert to pembayaran
                $metode = isset($_POST['metode']) && !empty($_POST['metode']) ? $_POST['metode'] : 'Transfer';
                // User requirement: Status immediately 'Berhasil' (Validation checked by admin manually)
                $stmt = $conn->prepare("INSERT INTO pembayaran (id_pemesanan, total_pembayaran, tanggal_pembayaran, bukti_pembayaran, status, metode_pembayaran) VALUES (?, ?, NOW(), ?, 'Berhasil', ?)");
                $stmt->bind_param("idss", $id_pemesanan, $jumlah, $new_filename, $metode);
                
                if ($stmt->execute()) {
                    // Update Status in Pemesanan if needed, or just rely on Pembayaran status
                    $success_msg = "Pembayaran sedang dikonfirmasi";
                } else {
                    $error_msg = "Gagal menyimpan data: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error_msg = "Gagal mengupload file.";
            }
        } else {
            $error_msg = "Format file tidak didukung. Gunakan JPG, PNG, atau PDF.";
        }
    } else {
        $error_msg = "Mohon pilih file bukti pembayaran.";
    }
}

// Fetch Unpaid Orders
// Join with layanan to get info, join with pembayaran to check status
// Condition: No payment record OR payment is Rejected
$sql_unpaid = "SELECT p.*, l.jenis_layanan, pay.status as status_pembayaran 
               FROM pemesanan p 
               JOIN layanan l ON p.id_layanan = l.id_layanan
               LEFT JOIN pembayaran pay ON p.id_pemesanan = pay.id_pemesanan 
               WHERE p.id_klien = ? 
               AND pay.status IS NULL
               ORDER BY p.jadwal_survey DESC";

$stmt_unpaid = $conn->prepare($sql_unpaid);
$stmt_unpaid->bind_param("i", $id_klien);
$stmt_unpaid->execute();
$res_unpaid = $stmt_unpaid->get_result();
$unpaid_orders = [];
while($row = $res_unpaid->fetch_assoc()) {
    $row['harga'] = calculate_price($row['jarak_dari_kantor'], $row['id_layanan'], $conn);
    $unpaid_orders[] = $row;
}
$stmt_unpaid->close();

// Fetch Payment History
$sql_history = "SELECT p.*, pay.status as status_pembayaran, pay.tanggal_pembayaran, pay.total_pembayaran, l.jenis_layanan 
                FROM pemesanan p 
                JOIN layanan l ON p.id_layanan = l.id_layanan
                JOIN pembayaran pay ON p.id_pemesanan = pay.id_pemesanan 
                WHERE p.id_klien = ? 
                ORDER BY pay.tanggal_pembayaran DESC";
$stmt_hist = $conn->prepare($sql_history);
$stmt_hist->bind_param("i", $id_klien);
$stmt_hist->execute();
$res_hist = $stmt_hist->get_result();
$history_orders = [];
while($row = $res_hist->fetch_assoc()) {
    $total = $row['total_pembayaran'];
    if(!$total) $total = calculate_price($row['jarak_dari_kantor'], $row['id_layanan'], $conn);
    $row['harga'] = $total;
    $row['status'] = $row['status_pembayaran'];
    $history_orders[] = $row;
}
$stmt_hist->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Survey Kost Solo</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/user_global.css?v=<?php echo time(); ?>">
    <style>
        .btn-pay { 
            background: var(--sidebar-gradient); color: white; 
            padding: 12px 24px; border-radius: 12px; 
            text-decoration: none; font-weight: 700; font-size: 14px; 
            border: none; cursor: pointer; transition: 0.3s; 
            box-shadow: 0 4px 15px rgba(176, 137, 104, 0.2);
            display: inline-block;
        }
        .btn-pay:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(176, 137, 104, 0.3); }

        /* MODAL */
        .modal { display: none; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(93, 64, 55, 0.6); align-items: center; justify-content: center; backdrop-filter: blur(5px); }
        .modal.show { display: flex; }
        .modal-content { 
            background-color: #fff; padding: 40px; border-radius: 24px; 
            width: 100%; max-width: 550px; position: relative; 
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
            animation: slideUp 0.3s ease; 
        }
        @keyframes slideUp { from { transform: translateY(50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .close { position: absolute; right: 25px; top: 20px; font-size: 28px; font-weight: bold; color: #D7CCC8; cursor: pointer; transition: 0.3s; }
        .close:hover { color: var(--primary-dark); }
        
        .bank-info { 
            background: #FFFCF9; padding: 25px; border-radius: 16px; 
            margin-bottom: 25px; border: 2px dashed #E6CCB2; 
        }
        .bank-info h4 { margin-bottom: 15px; color: var(--primary-dark); font-weight: 700; }
        .bank-item { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 15px; color: #5D5049; }
        
        input[type="file"] {
            background: #FFFCF9; padding: 15px; border-radius: 12px; border: 2px dashed #E6CCB2; width: 100%;
            cursor: pointer; transition: 0.3s;
        }
        input[type="file"]:hover { background: white; border-color: var(--primary); }

        /* Payment UI Clean Receipt */
        .method-box { cursor: pointer; }
        .method-box input { display: none; }
        .method-box .m-inner {
            background: white; border: 1px solid #eee; border-radius: 12px;
            padding: 12px; text-align: center; transition: 0.2s;
            height: 100%; display: flex; flex-direction: column; justify-content: center;
        }
        .method-box input:checked + .m-inner {
            border-color: var(--primary); background: #FFFBF5; box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .method-box .m-name { font-weight: 700; font-size: 14px; margin-bottom: 3px; }
        .method-box .m-num { font-size: 11px; color: #999; }

        /* Custom File Input */
        .custom-file-input { display: none; }
        
    </style>
</head>
<body>
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
            <li class="nav-item"><a href="pembayaran.php" class="active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg><span>Pembayaran</span></a></li>
            <li class="nav-item"><a href="survey.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg><span>Hasil Survey</span></a></li>
            <li class="nav-item" style="margin-top:auto;"><a href="../logout.php" onclick="return confirm('Keluar?')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg><span>Keluar</span></a></li>
        </ul>
    </aside>

    <main class="main-wrapper">
        <div class="layout-compact">
            <div class="page-header">
                <h1>Pembayaran</h1>
            <p>Kelola tagihan dan riwayat pembayaran</p>
        </div>

        <?php if ($success_msg): ?>
            <div style="background:#E8F5E9; color:#1B5E20; padding:15px; border-radius:10px; margin-bottom:20px;"><?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div style="background:#FFEBEE; color:#C62828; padding:15px; border-radius:10px; margin-bottom:20px;"><?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>

        <!-- Tagihan -->
        <h3 style="margin-bottom:20px; color:#5D4037;">Tagihan Belum Dibayar</h3>
        <div class="card-list" style="margin-bottom: 40px;">
            <?php if (count($unpaid_orders) > 0): ?>
                <?php foreach($unpaid_orders as $order): ?>
                <div class="card">
                    <div class="card-info">
                        <h3>Order #<?php echo $order['id_pemesanan']; ?></h3>
                        <p>Lokasi: <?php echo htmlspecialchars(substr($order['alamat_kost'], 0, 30)) . '...'; ?></p>
                        <p>Tanggal: <?php echo date('d M Y', strtotime($order['jadwal_survey'])); ?></p>
                        <?php if($order['status_pembayaran'] == 'Invalid' || $order['status_pembayaran'] == 'Ditolak'): ?>
                            <p style="color:#D32F2F; font-weight:600; font-size:13px; margin-top:4px;">⚠ Pembayaran Gagal (Ditolak Admin)</p>
                        <?php endif; ?>
                    </div>
                    <div style="text-align:right;">
                        <?php 
                            // Prepare data for modal
                            // Use updated columns: catatan_tambahan, waktu_survey
                            $catatan = $order['catatan_tambahan'] ?? '';
                            // Waktu is stored as string "09:00 - 11:00", so display as is.
                            $waktu   = $order['waktu_survey'] ?? ''; 

                            $modalData = [
                                'paket' => $order['jenis_layanan'], // from JOIN
                                'alamat' => $order['alamat_kost'],
                                'jarak' => $order['jarak_dari_kantor'] . ' km',
                                'jadwal' => date('d M Y', strtotime($order['jadwal_survey'])) . ($waktu ? ', ' . $waktu : ''),
                                'wa' => $order['no_wa_klien'] ?? '-',
                                'harga' => $order['harga'],
                                'id' => $order['id_pemesanan']
                            ];
                        ?>
                        <div class="card-price">Rp <?php echo number_format($order['harga'], 0, ',', '.'); ?></div>
                        <button class="btn-pay" onclick='openModal(<?php echo json_encode($modalData); ?>)' style="margin-top:10px; display:inline-block;">Bayar Sekarang</button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="background:white; padding:20px; border-radius:12px; color:#888; text-align:center;">Tidak ada tagihan belum dibayar.</div>
            <?php endif; ?>
        </div>

        <!-- Riwayat -->
        <h3 style="margin-bottom:20px; color:#5D4037;">Riwayat Pembayaran</h3>
        <div class="card-list">
            <?php if (count($history_orders) > 0): ?>
                <?php foreach($history_orders as $hist): ?>
                <div class="card">
                    <div class="card-info">
                        <h3>Order #<?php echo $hist['id_pemesanan']; ?></h3>
                        <p>Lokasi: <?php echo htmlspecialchars(substr($hist['alamat_kost'], 0, 30)) . '...'; ?></p>
                        <p>Status Order: <span style="color:var(--primary); font-weight:600;"><?php echo ($hist['status'] == 'Invalid' ? 'Ditolak' : ($hist['status'] == 'Valid' ? 'Berhasil' : 'Verifikasi')); ?></span></p>
                    </div>
                    <div style="text-align:right;">
                        <div class="card-price" style="color:#555; font-size:18px;">Rp <?php echo number_format($hist['harga'], 0, ',', '.'); ?></div>
                        <span class="status-badge <?php 
                            $status_final = 'status-pending';
                            $text_final = 'Diproses';

                            // Logic: 
                            // 1. Status is now 'Berhasil' automatically upon upload.
                            // 2. Admin verification is internal.
                            
                            $s_pay = $hist['status_pembayaran'] ?? '';
                            
                            // If status is Valid OR Berhasil OR Selesai -> Success
                            if ($s_pay == 'Valid' || $s_pay == 'Selesai' || $s_pay == 'Berhasil') {
                                $status_final = 'status-success';
                                $text_final = 'Berhasil';
                            } elseif ($s_pay == 'Menunggu') {
                                $status_final = 'status-verify';
                                $text_final = 'Verifikasi';
                            } elseif ($s_pay == 'Invalid') {
                                $status_final = 'status-danger';
                                $text_final = 'Ditolak';
                            }
                        ?> <?php echo $status_final; ?>">
                            <?php echo $text_final; ?>
                        </span>
                        
                        <?php if ($text_final == 'Ditolak' || $text_final == 'Gagal'): ?>
                            <div style="margin-top:10px;">
                                <?php 
                                    // Re-use calc logic or just pass total
                                    $modalDataHist = [
                                        'paket' => $hist['jenis_layanan'],
                                        'alamat' => $hist['alamat_kost'],
                                        'jarak' => ($hist['jarak_dari_kantor'] ?? 0) . ' km',
                                        // Use current time or order time? Order time.
                                        'jadwal' => date('d M Y', strtotime($hist['jadwal_survey'])), 
                                        'wa' => $hist['no_wa_klien'] ?? '-',
                                        'harga' => $hist['harga'],
                                        'id' => $hist['id_pemesanan']
                                    ];
                                ?>
                                <button class="btn-pay" onclick='openModal(<?php echo json_encode($modalDataHist); ?>)' style="padding:8px 16px; font-size:12px; background:#D32F2F;">Bayar Ulang</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="background:white; padding:20px; border-radius:12px; color:#888; text-align:center;">Belum ada riwayat pembayaran.</div>
            <?php endif; ?>
        </div>
        </div>
    </main>

    <!-- Modal Payment -->
    <div id="payModal" class="modal">
        <div class="modal-content" style="max-width: 750px; padding: 0; border-radius: 20px; overflow: hidden; font-family: 'Outfit', sans-serif; background: #FDFBF7;">
            
            <!-- Header (Pure Title) -->
            <div style="background: var(--primary); padding: 20px; color: white; text-align: center; position: relative;">
                 <span class="close" onclick="closeModal()" style="color:white; opacity:0.8; font-size:24px; top:15px; right:20px; position:absolute; cursor:pointer;">&times;</span>
                 <h2 style="font-size:18px; font-weight:700; margin:0; letter-spacing: 0.5px;">KONFIRMASI PEMBAYARAN</h2>
                 <div style="position: absolute; bottom: -10px; left: 0; width: 100%; height: 20px; background: #FDFBF7; border-radius: 20px 20px 0 0;"></div>
            </div>
            
            <div style="display: flex; flex-direction: row; padding: 10px 30px 30px 30px; gap: 40px; text-align: left;">
                
                <!-- Left Column: Details (Receipt Style) -->
                <div style="flex: 1; border-right: 1px dashed #E0E0E0; padding-right: 30px;">
                    <h4 style="font-size: 13px; color: #888; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom:10px;">Rincian Pesanan</h4>
                    
                    <div style="margin-bottom: 20px;">
                        <input type="hidden" id="sumId">
                        
                        <div style="margin-bottom: 12px;">
                            <span style="display:block; font-size: 12px; color: #999;">Paket Survey</span>
                            <span style="font-weight: 600; color: #333; font-size: 15px;" id="sumPaket">-</span>
                        </div>
                        <div style="margin-bottom: 12px;">
                            <span style="display:block; font-size: 12px; color: #999;">Waktu Survey</span>
                            <span style="font-weight: 600; color: #333; font-size: 15px;" id="sumJadwal">-</span>
                        </div>
                        <div style="margin-bottom: 12px;">
                            <span style="display:block; font-size: 12px; color: #999;">Lokasi</span>
                            <span style="font-weight: 600; color: #333; font-size: 14px;" id="sumAlamat">-</span>
                        </div>
                         <div style="margin-bottom: 12px;">
                            <span style="display:block; font-size: 12px; color: #999;">Jarak</span>
                            <span style="font-weight: 600; color: #333; font-size: 14px;" id="sumJarak">-</span>
                        </div>
                        <div style="margin-bottom: 12px;">
                            <span style="display:block; font-size: 12px; color: #999;">Kontak</span>
                            <span style="font-weight: 600; color: #333; font-size: 14px;" id="sumWa">-</span>
                        </div>
                    </div>

                    <!-- Total at Bottom of Left Col -->
                    <div style="background: #FFF8E1; padding: 15px; border-radius: 12px; border: 1px solid #FFECB3;">
                        <span style="display:block; font-size: 12px; color: #8D6E63; margin-bottom: 4px;">Total Tagihan</span>
                        <span style="font-size: 24px; font-weight: 800; color: #E65100;">Rp <span id="sumTotal">0</span></span>
                    </div>
                </div>

                <!-- Right Column: Methods & Upload -->
                <div style="flex: 1.2;">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="upload_bukti">
                        <input type="hidden" name="id_pemesanan" id="inputId">
                        <input type="hidden" name="jumlah" id="inputJumlah">

                        <h4 style="font-size: 13px; color: #888; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; ">Metode Pembayaran</h4>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 25px;">
                            <!-- DANA -->
                            <label class="method-box">
                                <input type="radio" name="metode" value="dana" checked>
                                <div class="m-inner">
                                    <div class="m-name" style="color:#108EE9;">DANA</div>
                                    <div class="m-num">0812-3456-7890</div>
                                </div>
                            </label>
                             <!-- GoPay -->
                            <label class="method-box">
                                <input type="radio" name="metode" value="gopay">
                                <div class="m-inner">
                                    <div class="m-name" style="color:#00AED6;">GoPay</div>
                                    <div class="m-num">0812-3456-7890</div>
                                </div>
                            </label>
                             <!-- OVO -->
                            <label class="method-box">
                                <input type="radio" name="metode" value="ovo">
                                <div class="m-inner">
                                    <div class="m-name" style="color:#4C3398;">OVO</div>
                                    <div class="m-num">0812-3456-7890</div>
                                </div>
                            </label>
                             <!-- BRI -->
                            <label class="method-box">
                                <input type="radio" name="metode" value="bank">
                                <div class="m-inner">
                                    <div class="m-name" style="color:#00509F;">Bank BRI</div>
                                    <div class="m-num">1234-5678-9000</div>
                                </div>
                            </label>
                        </div>

                        <h4 style="font-size: 13px; color: #888; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px;">Upload Bukti</h4>
                        <div style="background: white; border: 1px solid #ddd; border-radius: 10px; padding: 5px; display: flex; align-items: center; margin-bottom: 25px;">
                            <input type="file" name="bukti" required id="fileInput" class="custom-file-input">
                            <label for="fileInput" style="flex: 1; padding: 12px; font-size: 13px; color: #555; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                                <span id="fileName">Klik untuk upload file...</span>
                                <span style="font-size: 18px;">📷</span>
                            </label>
                        </div>

                        <button type="submit" class="btn-pay" style="width:100%; padding:15px; font-weight: 600; font-size:15px; border-radius:12px; box-shadow: 0 4px 15px rgba(139, 115, 85, 0.3);">KONFIRMASI PEMBAYARAN</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('payModal');
        const inputId = document.getElementById('inputId');
        const inputJumlah = document.getElementById('inputJumlah');
        
        // Summary elements
        // Summary elements
        const sumPaket = document.getElementById('sumPaket');
        const sumAlamat = document.getElementById('sumAlamat');
        const sumJadwal = document.getElementById('sumJadwal');
        const sumWa = document.getElementById('sumWa');
        const sumTotal = document.getElementById('sumTotal');

        function openModal(data) {
            // Data is passed as object
            inputId.value = data.id;
            inputJumlah.value = data.harga;
            
            // Populate Summary
            sumPaket.textContent = data.paket;
            sumAlamat.textContent = data.alamat.substring(0, 50) + (data.alamat.length>50 ? '...' : ''); 
            // Display date and time exactly as sent from PHP
            sumJadwal.textContent = data.jadwal;
            
            sumJarak.textContent = data.jarak;
            sumWa.textContent = data.wa;
            sumTotal.textContent = parseInt(data.harga).toLocaleString('id-ID');

            modal.classList.add('show');
        }

        function closeModal() {
            modal.classList.remove('show');
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        }

        // Custom File Input
        const fileInput = document.getElementById('fileInput');
        const fileName = document.getElementById('fileName');
        fileInput.addEventListener('change', function() {
            if(this.files && this.files.length > 0) {
                fileName.textContent = this.files[0].name;
                fileName.style.color = "#2E7D32";
                fileName.style.fontWeight = "600";
            }
        });
    </script>
</body>
</html>
