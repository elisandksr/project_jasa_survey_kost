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

        <!-- NEW: Detailed Reports Section -->
        
        <?php
        // 1. Laporan Data Klien (Limit 50)
        $clients = [];
        if ($res_list_klien = $conn->query("SELECT id_klien as id, nama_lengkap, email, no_wa as no_hp FROM klien ORDER BY id_klien DESC LIMIT 50")) {
            while($row = $res_list_klien->fetch_assoc()) {
                $clients[] = $row;
            }
        }

        // 2. Laporan Riwayat Pemesanan
        $orders = [];
        $sql_orders = "SELECT p.id_pemesanan as id, 
                              p.jadwal_survey, 
                              k.nama_lengkap,
                              pay.status as status_bayar,
                              h.id_survey
                       FROM pemesanan p 
                       LEFT JOIN klien k ON p.id_klien = k.id_klien 
                       LEFT JOIN pembayaran pay ON p.id_pemesanan = pay.id_pemesanan
                       LEFT JOIN hasil_survey h ON p.id_pemesanan = h.id_pemesanan
                       ORDER BY p.id_pemesanan DESC LIMIT 50";
                       
        if ($res_list_order = $conn->query($sql_orders)) {
            while($row = $res_list_order->fetch_assoc()) {
                // Derive Status
                $status_txt = 'Menunggu';
                $status_cls = 'status-pending';
                
                if ($row['id_survey']) {
                    $status_txt = 'Selesai';
                    $status_cls = 'status-success';
                } elseif ($row['status_bayar'] == 'Valid') {
                    $status_txt = 'Diproses';
                    $status_cls = 'status-info';
                } elseif ($row['status_bayar'] == 'Invalid') {
                    $status_txt = 'Ditolak';
                    $status_cls = 'status-danger';
                }
                
                $row['status_derived'] = $status_txt;
                $row['status_class'] = $status_cls;
                $orders[] = $row;
            }
        }

        // 3. Laporan Pendapatan Detail
        $payments = [];
        $sql_revenue = "SELECT pay.id_pembayaran as id, pay.total_pembayaran, pay.tanggal_pembayaran as tanggal_bayar, pay.metode_pembayaran, k.nama_lengkap 
                       FROM pembayaran pay 
                       JOIN pemesanan p ON pay.id_pemesanan = p.id_pemesanan 
                       JOIN klien k ON p.id_klien = k.id_klien 
                       WHERE pay.status = 'Valid' 
                       ORDER BY pay.tanggal_pembayaran DESC LIMIT 50";
                       
        if ($res_list_pay = $conn->query($sql_revenue)) {
            while($row = $res_list_pay->fetch_assoc()) {
                $payments[] = $row;
            }
        }
        ?>

        <style>
            /* Tabs Styling */
            .tabs-nav {
                display: flex;
                gap: 15px;
                margin-bottom: 25px;
                border-bottom: 2px solid #F0E6D2;
                padding-bottom: 0;
                overflow-x: auto; /* Allow scroll on small screens */
                scrollbar-width: none; /* Firefox */
                -ms-overflow-style: none; /* IE 10+ */
            }
            .tabs-nav::-webkit-scrollbar {
                display: none; /* Chrome/Safari/Webkit */
            }

            .tab-btn {
                background: none;
                border: none;
                padding: 10px 20px;
                font-family: 'Outfit', sans-serif;
                font-size: 15px;
                font-weight: 600;
                color: #8B7355;
                cursor: pointer;
                position: relative;
                transition: 0.2s;
                white-space: nowrap;
            }

            .tab-btn:hover {
                color: #5D4037;
            }

            .tab-btn.active {
                color: #5D4037;
            }

            .tab-btn.active::after {
                content: '';
                position: absolute;
                bottom: -2px;
                left: 0;
                right: 0;
                height: 3px;
                background: #8B7355;
                border-radius: 3px 3px 0 0;
            }

            .tab-content-wrapper {
                min-height: 400px;
            }

            .tab-content {
                display: none;
                animation: fadeIn 0.3s ease-out;
            }

            .tab-content.active {
                display: block;
            }

            /* Table Scroll Styling: Hide scrollbar but keep functionality */
            .table-responsive {
                overflow-x: auto;
                scrollbar-width: none;
                -ms-overflow-style: none;
            }
            .table-responsive::-webkit-scrollbar {
                display: none;
            }

            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(5px); }
                to { opacity: 1; transform: translateY(0); }
            }

            /* Compact Stats Grid Overrides - Scaled Down Original */
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(5, 1fr) !important; /* Force 5 columns */
                gap: 15px !important;
                margin-bottom: 25px !important;
            }

            .stat-card {
                background: white;
                padding: 15px !important; /* Reduced from 25px */
                border-radius: 15px !important; /* Slightly smaller radius */
                box-shadow: 0 5px 15px rgba(139, 115, 85, 0.08);
                display: flex;
                flex-direction: column !important; /* Keep original vertical stack */
                justify-content: center;
                min-height: auto !important;
                gap: 0 !important; /* Let margins handle spacing */
            }

            .stat-icon {
                width: 45px !important; /* Reduced from 60px */
                height: 45px !important;
                font-size: 20px !important; /* Reduced from 28px */
                border-radius: 12px !important;
                margin-bottom: 10px !important; /* Reduced from 15px */
            }

            .stat-info span {
                font-size: 11px !important; /* Reduced from 13px */
                margin-bottom: 2px !important;
                font-weight: 700;
                text-transform: uppercase;
                color: #A0826D;
            }

            .stat-info h3 {
                font-size: 22px !important; /* Reduced from 28px */
                margin: 0 !important;
                line-height: 1.2;
                color: #5D4037;
            }

            .stat-desc {
                display: block !important;
                font-size: 11px !important; /* Reduced from 13px */
                margin-top: 8px !important; /* Reduced from 10px */
                color: #A0826D;
                line-height: 1.3;
            }

            /* Responsive adjustment */
            @media (max-width: 1200px) {
                .stats-grid {
                    grid-template-columns: repeat(3, 1fr) !important;
                }
            }
            @media (max-width: 768px) {
                .stats-grid {
                    grid-template-columns: repeat(2, 1fr) !important;
                }
            }
            @media (max-width: 480px) {
                .stats-grid {
                    grid-template-columns: 1fr !important;
                }
            }
        </style>

        <!-- Tabs Navigation -->
        <div class="tabs-nav">
            <button class="tab-btn active" onclick="switchTab('data-klien')">Data Klien</button>
            <button class="tab-btn" onclick="switchTab('riwayat-order')">Riwayat Pemesanan</button>
            <button class="tab-btn" onclick="switchTab('pendapatan')">Laporan Pendapatan</button>
        </div>

        <div class="tab-content-wrapper">
            <!-- 1. Laporan Klien -->
            <div id="data-klien" class="tab-content active">
                <div class="table-section" id="print-klien">
                    <div class="table-header-title" style="display: flex; justify-content: space-between; align-items: center;">
                        <span>Laporan Data Klien Terbaru</span>
                        <button onclick="printSection('print-klien')" class="btn btn-sm btn-action-view">🖨️ Cetak Laporan</button>
                    </div>
                     <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Lengkap</th>
                                    <th>Email</th>
                                    <th>No HP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($clients)): ?>
                                    <tr><td colspan="4" style="text-align:center;">Belum ada data klien.</td></tr>
                                <?php else: ?>
                                    <?php foreach($clients as $c): ?>
                                    <tr>
                                        <td>#<?php echo $c['id']; ?></td>
                                        <td><?php echo htmlspecialchars($c['nama_lengkap']); ?></td>
                                        <td><?php echo htmlspecialchars($c['email']); ?></td>
                                        <td><?php echo htmlspecialchars($c['no_hp']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 2. Laporan Pemesanan -->
            <div id="riwayat-order" class="tab-content">
                <div class="table-section" id="print-order">
                    <div class="table-header-title" style="display: flex; justify-content: space-between; align-items: center;">
                        <span>Laporan Riwayat Pemesanan</span>
                        <button onclick="printSection('print-order')" class="btn btn-sm btn-action-view">🖨️ Cetak Laporan</button>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID Order</th>
                                    <th>Nama Klien</th>
                                    <th>Jadwal Survey</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($orders)): ?>
                                    <tr><td colspan="4" style="text-align:center;">Belum ada data pemesanan.</td></tr>
                                <?php else: ?>
                                    <?php foreach($orders as $o): ?>
                                    <tr>
                                        <td>#<?php echo $o['id']; ?></td>
                                        <td><?php echo htmlspecialchars($o['nama_lengkap'] ?? 'User Deleted'); ?></td>
                                        <td><?php echo date('d M Y', strtotime($o['jadwal_survey'])); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $o['status_class']; ?>">
                                                <?php echo htmlspecialchars($o['status_derived']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 3. Laporan Pendapatan -->
            <div id="pendapatan" class="tab-content">
                <div class="table-section" id="print-revenue">
                    <div class="table-header-title" style="display: flex; justify-content: space-between; align-items: center;">
                        <span>Laporan Pendapatan </span>
                        <button onclick="printSection('print-revenue')" class="btn btn-sm btn-action-view">🖨️ Cetak Laporan</button>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID Bayar</th>
                                    <th>Nama Klien</th>
                                    <th>Tanggal Bayar</th>
                                    <th>Metode</th>
                                    <th>Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total_visible = 0;
                                if(empty($payments)): ?>
                                    <tr><td colspan="5" style="text-align:center;">Belum ada data pembayaran valid.</td></tr>
                                <?php else: ?>
                                    <?php foreach($payments as $p): 
                                        $total_visible += $p['total_pembayaran'];
                                    ?>
                                    <tr>
                                        <td>#<?php echo $p['id']; ?></td>
                                        <td><?php echo htmlspecialchars($p['nama_lengkap'] ?? 'User Deleted'); ?></td>
                                        <td><?php echo date('d M Y', strtotime($p['tanggal_bayar'])); ?></td>
                                        <td><?php echo htmlspecialchars($p['metode_pembayaran']); ?></td>
                                        <td style="font-weight:bold; color: #2E7D32;">
                                            Rp <?php echo number_format($p['total_pembayaran'], 0, ',', '.'); ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr style="background-color: #F0F4C3; font-weight: 800; border-top: 2px solid #8B7355;">
                                    <td colspan="4" style="text-align: right; padding-right: 20px;">TOTAL PENDAPATAN KESELURUHAN:</td>
                                    <td style="color: #1B5E20;">Rp <?php echo number_format($total_revenue ?? 0, 0, ',', '.'); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ensure first tab is active on load
            switchTab('data-klien');
        });

        function switchTab(tabId) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            // Deactivate all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Show selected tab
            var selectedTab = document.getElementById(tabId);
            if(selectedTab) {
                selectedTab.classList.add('active');
            }
            
            // Activate button
            const buttons = document.querySelectorAll('.tab-btn');
            buttons.forEach(btn => {
                if(btn.getAttribute('onclick') && btn.getAttribute('onclick').includes(tabId)) {
                    btn.classList.add('active');
                }
            });
        }

        function printSection(divId) {
            var contentDiv = document.getElementById(divId);
            if (!contentDiv) return;

            var printContents = contentDiv.innerHTML;
            // Remove print button reference
            printContents = printContents.replace(/<button[^>]*>.*?<\/button>/gi, '');
            
            var printWindow = window.open('', '', 'height=700,width=1000');
            printWindow.document.write('<html><head><title>Cetak Laporan</title>');
            printWindow.document.write('<style>');
            printWindow.document.write('body { font-family: "Courier New", Courier, monospace; padding: 20px; color: #000; }');
            printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 20px; border: 1px solid #000; }');
            printWindow.document.write('th, td { border: 1px solid #000; padding: 8px 12px; text-align: left; font-size: 11pt; }');
            printWindow.document.write('th { background-color: #f0f0f0; font-weight: bold; text-transform: uppercase; }');
            printWindow.document.write('.table-header-title { font-size: 16pt; font-weight: bold; margin-bottom: 20px; text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; }');
            printWindow.document.write('.status-badge { font-weight: bold; }');
            printWindow.document.write('</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(printContents);
            printWindow.document.write('<div style="margin-top:30px; text-align:right; font-size:10pt;">Dicetak pada: ' + new Date().toLocaleString() + '</div>');
            printWindow.document.write('</body></html>');
            
            printWindow.document.close();
            printWindow.focus();
            // Delay print to allow styles to load
            setTimeout(function() {
                printWindow.print();
                printWindow.close();
            }, 500);
        }
        </script>

</body>
</html>
