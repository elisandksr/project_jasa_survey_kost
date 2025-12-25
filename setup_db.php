<?php
require_once 'config.php';

function checkAndAddColumn($conn, $table, $column, $definition) {
    $check = $conn->query("SHOW COLUMNS FROM $table LIKE '$column'");
    if ($check && $check->num_rows == 0) {
        $sql = "ALTER TABLE $table ADD COLUMN $column $definition";
        if ($conn->query($sql) === TRUE) {
            echo "Added column '$column' to table '$table'.<br>";
        } else {
            echo "Error adding column '$column' to '$table': " . $conn->error . "<br>";
        }
    }
}

function checkAndModifyColumn($conn, $table, $column, $definition) {
    $sql = "ALTER TABLE $table MODIFY COLUMN $column $definition";
    if ($conn->query($sql) === TRUE) {
        // echo "Modified column '$column' in table '$table'.<br>";
    } else {
        echo "Error modifying column '$column' in '$table': " . $conn->error . "<br>";
    }
}

echo "<h2>Migrating Database Schema...</h2>";

// 1. Create Tables if not exist
$tables_sql = [
    "CREATE TABLE IF NOT EXISTS admin (
        id_admin INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS klien (
        id_klien INT AUTO_INCREMENT PRIMARY KEY,
        nama_lengkap VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        no_wa VARCHAR(20) NOT NULL,
        password VARCHAR(255) NOT NULL,
        alamat TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS pemesanan (
        id_pemesanan INT AUTO_INCREMENT PRIMARY KEY,
        id_klien INT NOT NULL,
        tanggal_pemesanan DATE NOT NULL,
        lokasi_survey TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS pembayaran (
        id_pembayaran INT AUTO_INCREMENT PRIMARY KEY,
        id_pemesanan INT NOT NULL,
        tanggal_pembayaran DATE NOT NULL,
        jumlah DECIMAL(10, 2) NOT NULL,
        bukti_pembayaran VARCHAR(255) NOT NULL,
        status ENUM('Pending', 'Valid', 'Invalid') DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS hasil_survey (
        id_survey INT AUTO_INCREMENT PRIMARY KEY,
        id_pemesanan INT NOT NULL,
        deskripsi TEXT,
        tanggal_survey DATE,
        dokumentasi_survey VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS layanan (
        id_layanan INT AUTO_INCREMENT PRIMARY KEY,
        jenis_layanan VARCHAR(50) NOT NULL,
        biaya DECIMAL(10, 2) NOT NULL,
        keterangan TEXT,
        ketentuan TEXT
    )"
];

foreach ($tables_sql as $sql) {
    if (!$conn->query($sql)) {
        echo "Error creating table: " . $conn->error . "<br>";
    }
}

// 2. Update Schemas (Add missing columns)

// Table: admin
checkAndAddColumn($conn, 'admin', 'nama_lengkap', 'VARCHAR(100) NOT NULL DEFAULT "Admin"');
checkAndModifyColumn($conn, 'admin', 'password', 'VARCHAR(255) NOT NULL');

// Table: klien
checkAndModifyColumn($conn, 'klien', 'password', 'VARCHAR(255) NOT NULL');

// Table: pemesanan
checkAndAddColumn($conn, 'pemesanan', 'kategori_kost', 'VARCHAR(50) NOT NULL DEFAULT "Umum"');
checkAndAddColumn($conn, 'pemesanan', 'budget_range', 'VARCHAR(50) DEFAULT "-"');
checkAndAddColumn($conn, 'pemesanan', 'fasilitas_request', 'TEXT');
// checkAndAddColumn($conn, 'pemesanan', 'status', "ENUM('Menunggu Pembayaran', 'Verifikasi Pembayaran', 'Dijadwalkan', 'Selesai', 'Dibatalkan') DEFAULT 'Menunggu Pembayaran'");
checkAndAddColumn($conn, 'pemesanan', 'harga', 'DECIMAL(10, 2) DEFAULT 0');

// Table: pembayaran
// (Already complete in Create)

// Table: hasil_survey
// Table: hasil_survey
$conn->query("DROP TABLE IF EXISTS hasil_survey");
$conn->query("CREATE TABLE IF NOT EXISTS hasil_survey (
    id_survey INT AUTO_INCREMENT PRIMARY KEY,
    id_pemesanan INT NOT NULL,
    deskripsi TEXT,
    tanggal_survey DATE,
    dokumentasi_survey VARCHAR(255),
    FOREIGN KEY (id_pemesanan) REFERENCES pemesanan(id_pemesanan) ON DELETE CASCADE
)");

// 3. Insert Default Admin
$sql_admin = "INSERT IGNORE INTO admin (username, password, nama_lengkap, email) VALUES 
              ('admin', 'admin123', 'Administrator', 'admin@survey.com')";
if ($conn->query($sql_admin) === TRUE) {
    echo "Default admin check/insert done.<br>";
} else {
    echo "Error inserting admin: " . $conn->error . "<br>";
}

// 3.5. Insert/Reset Layanan Data (Packages)
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("TRUNCATE TABLE layanan"); 
$conn->query("SET FOREIGN_KEY_CHECKS = 1");
$stmt_lay = $conn->prepare("INSERT INTO layanan (jenis_layanan, biaya, keterangan, ketentuan) VALUES (?, ?, ?, ?)");
if ($stmt_lay) {
    // Paket Regular
    $type1 = "Paket Regular";
    $cost1 = 50000;
    $desc1 = "Foto + Video + Deskripsi Lengkap";
    $term1 = "2 - 4 Hari Pengerjaan";
    $stmt_lay->bind_param("sdss", $type1, $cost1, $desc1, $term1);
    $stmt_lay->execute();

    // Paket Express
    $type2 = "Paket Express";
    $cost2 = 75000; 
    $desc2 = "Foto + Video + Deskripsi Lengkap";
    $term2 = "1 Hari Selesai";
    $stmt_lay->bind_param("sdss", $type2, $cost2, $desc2, $term2);
    $stmt_lay->execute();
    echo "Layanan packages (Regular & Express) inserted.<br>";
    $stmt_lay->close();
} else {
    echo "Error preparing layanan insert: " . $conn->error . "<br>";
}

// 4. Data Migrations & Fixes (From fix_db.php)
$migrations = [
    "UPDATE pemesanan SET status = 'Menunggu Pembayaran' WHERE status IS NULL"
];

foreach ($migrations as $sql) {
    $conn->query($sql);
}
echo "Data migration fixes applied.<br>";

echo "<h3>Migration Completed!</h3>";
?>
