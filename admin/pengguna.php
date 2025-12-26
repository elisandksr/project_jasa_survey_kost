<?php
require_once '../config.php';

// Cek login admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login_admin.php");
    exit();
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// Handle Delete Action
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM klien WHERE id_klien = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: pengguna.php");
    exit();
}

// Fetch Users
$sql = "SELECT * FROM klien ORDER BY id_klien DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Data Klien - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin_global.css?v=<?php echo time(); ?>">
    <style>
        .search-bar { width: 100%; max-width: 100%; position: relative; } /* Override if needed or rely on global */
        .search-icon { position: absolute; right: 20px; top: 50%; transform: translateY(-50%); color: #D7C7B5;}
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
            <li><a href="pengguna.php" class="active"><span class="icon">👥</span> Data Klien</a></li>
            <li><a href="pemesanan.php"><span class="icon">📝</span> Data Pemesanan</a></li>
            <li><a href="pembayaran.php"><span class="icon">💳</span> Data Pembayaran</a></li>
            <li><a href="survey.php"><span class="icon">📋</span> Hasil Survey</a></li>
            <li><a href="laporan.php"><span class="icon">📄</span> Laporan</a></li>
            <li><a href="../logout.php" class="logout-btn"><span class="icon">🚪</span> Keluar</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <div class="page-title">
                <h1>Manajemen Data Klien</h1>
                <p>Kelola data pendaftar dan pengguna</p>
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
                        <th width="80">ID</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>No WhatsApp</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $row['id_klien']; ?></td>
                            <td style="font-weight:600;"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['no_wa']); ?></td>
                            <td>
                                <a href="?delete_id=<?php echo $row['id_klien']; ?>" class="btn btn-sm btn-action-delete" onclick="return confirm('Hapus data klien ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center; color:#999;">Belum ada data klien terdaftar.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
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
