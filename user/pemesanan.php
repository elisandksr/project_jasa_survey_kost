<?php
require_once '../config.php';

// Cek login
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['isLoggedIn'] !== true) {
    header("Location: ../login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_klien = $_SESSION['user_id'];
    $paket = $_POST['paket'] ?? '';
    $alamat_kost = $_POST['alamat'] ?? '';
    // Jarak coming as text/number
    $jarak = floatval($_POST['jarak'] ?? 0);
    $whatsapp = $_POST['whatsapp'] ?? '';
    $tanggal_survey = $_POST['tanggal'] ?? '';
    $waktu = $_POST['waktu'] ?? '';
    $catatan = $_POST['catatan'] ?? '';

    // Defaults for missing fields required by DB
    $kategori_kost = 'Umum'; 
    $budget_range = '-';

    // Calculate Price
    $base_price = ($paket === 'express') ? 75000 : 50000;
    $distance_fee = 0;
    if ($jarak > 5) {
        $distance_fee = ($jarak - 5) * 2500;
    }
    $total_harga = $base_price + $distance_fee;

    // Combine extra info into fasilitas_request since DB schema is limited
    // We treat "fasilitas_request" as a general notes/details field here
    $extra_info = "Paket: $paket\nJarak: $jarak km\nWhatsApp: $whatsapp\nWaktu: $waktu\nCatatan: $catatan";

    // Validate
    // Default ID Layanan (Must exist in DB)
    $id_layanan = 1; 

    if (empty($alamat_kost) || empty($tanggal_survey) || empty($whatsapp)) {
        $error = "Mohon lengkapi semua data wajib.";
    } else {
        // Lookup correct ID Layanan
        $selected_paket_name = ($paket === 'express') ? 'Express' : 'Reguler';
        $res_lay = $conn->query("SELECT id_layanan FROM layanan WHERE jenis_layanan LIKE '%$selected_paket_name%' LIMIT 1");
        $id_layanan = ($row_lay = $res_lay->fetch_assoc()) ? $row_lay['id_layanan'] : 1;
        
        $final_catatan = "Paket: " . ucfirst($paket) . "\n" . $catatan;

        // Insert using ONLY valid columns
        $stmt = $conn->prepare("INSERT INTO pemesanan (id_klien, id_layanan, jadwal_survey, alamat_kost, no_wa_klien, waktu_survey, jarak_dari_kantor, catatan_tambahan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissssds", $id_klien, $id_layanan, $tanggal_survey, $alamat_kost, $whatsapp, $waktu, $jarak, $final_catatan);

        if ($stmt->execute()) {
            $last_id = $conn->insert_id;
            $last_id = $conn->insert_id;
            header("Location: pembayaran.php?id=" . $last_id);
            exit();
        } else {
            $error = "Terjadi kesalahan: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemesanan - Survey Kost Solo</title>
    <!-- Google Fonts: Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/user_global.css?v=<?php echo time(); ?>_2">
    <style>
        /* Specific page styles not in global */
        /* Specific page styles */
        .paket-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 15px; }
        .paket-option input { display: none; }
        .paket-box {
            border: 1px solid #F3EBE3; border-radius: 12px; padding: 12px; /* Very compact */
            text-align: center; cursor: pointer; transition: 0.2s; background: #FFFCF9;
            height: 100%; display: flex; flex-direction: column; justify-content: center;
        }
        .paket-box:hover { background: white; border-color: var(--primary-light); transform: translateY(-2px); }
        .paket-option input:checked + .paket-box {
            background: var(--sidebar-gradient); border-color: transparent; color: white;
            box-shadow: 0 4px 10px rgba(176, 137, 104, 0.2); transform: translateY(-2px);
        }
        .paket-box h3 { font-size: 15px; margin-bottom: 4px; font-weight: 700; }
        .paket-box .price { font-size: 18px; font-weight: 800; margin-bottom: 2px; }
        .paket-option input:checked + .paket-box .price { color: #FFE0B2; }
        .paket-option input:checked + .paket-box h3 { color: white; }
        .paket-box .duration { font-size: 12px; opacity: 0.8; }
        
        /* Ensure form card is constrained */
        .form-card { margin: 0 auto; }

        /* Force consistent height for inputs and selects */
        .form-group input, .form-group select {
            height: 45px;
            line-height: normal; /* Reset line-height */
        }
        
        @media (max-width: 768px) {
            .paket-grid { grid-template-columns: 1fr; }
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
            <li class="nav-item">
                <a href="dashboard.php">
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
                <a href="pemesanan.php" class="active">
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
                <h1>Formulir Pemesanan</h1>
                <p>Isi data survey dengan lengkap</p>
            </div>

            <?php if ($error): ?>
                <div style="background:#FFEBEE; color:#C62828; padding:15px; border-radius:10px; margin-bottom:20px;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form id="surveyForm" method="POST" action="">
                <div class="form-card">
                    
                    <h3 class="section-title">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 8px; color: #8B7355;">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        1. Pilih Paket Kost
                    </h3>
                    <div class="paket-grid">
                        <label class="paket-option">
                            <input type="radio" name="paket" value="regular" checked>
                            <div class="paket-box">
                                <h3>Regular</h3>
                                <div class="price">Rp 50.000</div>
                                <div class="duration">2-4 Hari</div>
                            </div>
                        </label>
                        <label class="paket-option">
                            <input type="radio" name="paket" value="express">
                            <div class="paket-box">
                                <h3>Express</h3>
                                <div class="price">Rp 75.000</div>
                                <div class="duration">1 Hari Selesai</div>
                            </div>
                        </label>
                    </div>

                    <h3 class="section-title" style="margin-top: 30px;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 8px; color: #8B7355;">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        2. Lokasi & Jadwal Survey
                    </h3>
                    
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>Alamat Kost / Shareloc <span class="required">*</span></label>
                            <input type="text" name="alamat" required placeholder="Link Maps / Alamat Lengkap">
                        </div>

                        <div class="form-group">
                            <label>Jarak dari Kantor (Km) <span class="required">*</span></label>
                            <input type="number" id="jarak" name="jarak" step="0.1" placeholder="Estimasi jarak..." required>
                            <small style="color:#888; display:block; margin-top:5px;">Gratis < 5km.</small>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>Nomor WhatsApp <span class="required">*</span></label>
                            <input type="tel" name="whatsapp" placeholder="08xxxxxxxxxx" required>
                        </div>

                        <div class="form-group">
                            <label>Tanggal Survey <span class="required">*</span></label>
                            <input type="date" id="tanggal_survey" name="tanggal" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>Waktu Survey</label>
                            <select name="waktu">
                                <option value="09:00 - 11:00">Pagi (09:00 - 11:00)</option>
                                <option value="13:00 - 15:00">Siang (13:00 - 15:00)</option>
                                <option value="15:00 - 17:00">Sore (15:00 - 17:00)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Catatan (Opsional)</label>
                            <input type="text" name="catatan" placeholder="Detail khusus...">
                        </div>
                    </div>


                <div class="price-summary" style="display:none;"></div> <!-- Hidden/Removed -->

                <div style="text-align: center; margin-top: 20px;">
                    <button type="submit" class="btn-submit" style="width: auto; padding-left: 50px; padding-right: 50px;">Lanjut Pembayaran →</button>
                </div>
            </div>
            </form>
        </div>
    </main>

    <script>
        // Set min date to tomorrow
        const dateInput = document.getElementById('tanggal_survey');
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        dateInput.min = tomorrow.toISOString().split('T')[0];

        // Format Rupiah (util)
        const formatRupiah = (money) => {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(money);
        }

        // --- 1. PRICE CALCULATION LOGIC ---
        const jarakInput = document.getElementById('jarak');
        const paketInputs = document.querySelectorAll('input[name="paket"]'); // Radio buttons
        const estPriceSpan = document.getElementById('estPrice');

        function updatePrice() {
            // Check if radio exists and is checked, otherwise default
            const checkedRadio = document.querySelector('input[name="paket"]:checked');
            let basePrice = 50000; // Default Reguler
            if(checkedRadio && checkedRadio.value === 'express') basePrice = 75000;
            
            let jarak = parseFloat(jarakInput.value) || 0;
            let distanceFee = 0;
            
            if (jarak > 5) {
                distanceFee = (jarak - 5) * 2500;
            }
            
            let total = basePrice + distanceFee;
            estPriceSpan.textContent = total.toLocaleString('id-ID');
            
            // Trigger estimation update when price updates (package change)
            updateEstimation();
        }

        jarakInput.addEventListener('input', updatePrice);
        paketInputs.forEach(input => input.addEventListener('change', updatePrice));

        // --- 2. DATE ESTIMATION LOGIC ---
        // Create Estimate Div
        const estimateDiv = document.createElement('div');
        estimateDiv.style.marginTop = '10px';
        estimateDiv.style.padding = '15px';
        estimateDiv.style.borderRadius = '12px';
        estimateDiv.style.fontSize = '14px';
        estimateDiv.style.fontWeight = '600';
        estimateDiv.style.display = 'none'; 
        estimateDiv.style.border = '1px solid #ddd';
        
        // Insert after date input container
        const formGroupDate = dateInput.closest('.form-group');
        formGroupDate.appendChild(estimateDiv);

        function updateEstimation() {
            const dateVal = dateInput.value;
            // Get package value again
            const checkedRadio = document.querySelector('input[name="paket"]:checked');
            const paketVal = checkedRadio ? checkedRadio.value : '';

            if (dateVal && paketVal) {
                const dateObj = new Date(dateVal);
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                
                let text = '';
                
                if (paketVal === 'express') { // Value is lowercase 'express' based on reading file context usually, or '1'
                    // Check logic based on user file content 
                    // Previously I saw logic for 'ID' but looking at updatePrice it uses 'express' string value
                    
                    // Express = Same Day
                    const resDate = new Date(dateObj); 
                    text = `⚡ Paket Express dipilih: Hasil survey akan selesai pada tanggal yang sama (${resDate.toLocaleDateString('id-ID', options)}).`;
                    estimateDiv.style.background = '#FFF3E0'; 
                    estimateDiv.style.color = '#E65100';
                    estimateDiv.style.borderColor = '#FFE0B2';
                } else {
                    // Regular = +2 to +4 days
                    const minDate = new Date(dateObj); minDate.setDate(minDate.getDate() + 2);
                    const maxDate = new Date(dateObj); maxDate.setDate(maxDate.getDate() + 4);
                    
                    text = `📅 Paket Reguler dipilih: Hasil survey estimasi selesai antara ${minDate.toLocaleDateString('id-ID', options)} s.d ${maxDate.toLocaleDateString('id-ID', options)}.`;
                    estimateDiv.style.background = '#E8F5E9'; 
                    estimateDiv.style.color = '#2E7D32';
                    estimateDiv.style.borderColor = '#C8E6C9';
                }
                
                estimateDiv.innerHTML = text;
                estimateDiv.style.display = 'block';
            } else {
                estimateDiv.style.display = 'none';
            }
        }

        dateInput.addEventListener('change', updateEstimation);
        
        // --- INIT ---
        // Auto Select Paket from URL if present
        const params = new URLSearchParams(window.location.search);
        const paketParam = params.get('paket'); // e.g. ?paket=express
        if(paketParam) {
            const radio = document.querySelector(`input[name="paket"][value="${paketParam}"]`);
            if(radio) {
                radio.checked = true;
                updatePrice();
            }
        } else {
             updatePrice(); // Just run once
        }
    </script>
</body>
</html>
