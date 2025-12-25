-- Database: jasa_survey

CREATE DATABASE IF NOT EXISTS jasa_survey1;
USE jasa_survey1;

-- Table: admin
CREATE TABLE IF NOT EXISTS admin (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: klien (Users)
CREATE TABLE IF NOT EXISTS klien (
    id_klien INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    no_wa VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    alamat TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: pemesanan (Orders)
CREATE TABLE IF NOT EXISTS pemesanan (
    id_pemesanan INT AUTO_INCREMENT PRIMARY KEY,
    id_klien INT NOT NULL,
    tanggal_pemesanan DATE NOT NULL,
    lokasi_survey TEXT NOT NULL,
    kategori_kost VARCHAR(50) NOT NULL, -- e.g., Putra, Putri, Campur
    budget_range VARCHAR(50),
    fasilitas_request TEXT,
    status ENUM('Menunggu Pembayaran', 'Verifikasi Pembayaran', 'Dijadwalkan', 'Selesai', 'Dibatalkan') DEFAULT 'Menunggu Pembayaran',
    harga DECIMAL(10, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_klien) REFERENCES klien(id_klien) ON DELETE CASCADE
);

-- Table: pembayaran (Payments)
CREATE TABLE IF NOT EXISTS pembayaran (
    id_pembayaran INT AUTO_INCREMENT PRIMARY KEY,
    id_pemesanan INT NOT NULL,
    tanggal_pembayaran DATE NOT NULL,
    jumlah DECIMAL(10, 2) NOT NULL,
    bukti_pembayaran VARCHAR(255) NOT NULL, -- Path to image
    status ENUM('Pending', 'Valid', 'Invalid') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pemesanan) REFERENCES pemesanan(id_pemesanan) ON DELETE CASCADE
);

    FOREIGN KEY (id_pemesanan) REFERENCES pemesanan(id_pemesanan) ON DELETE CASCADE
);

-- Table: layanan
CREATE TABLE IF NOT EXISTS layanan (
    id_layanan INT AUTO_INCREMENT PRIMARY KEY,
    jenis_layanan VARCHAR(50) NOT NULL,
    biaya DECIMAL(10, 2) NOT NULL,
    keterangan TEXT,
    ketentuan TEXT
);

INSERT INTO layanan (jenis_layanan, biaya, keterangan, ketentuan) VALUES 
('Paket Regular', 15000, 
 'Kami menyediakan jasa survey kost profesional dengan dokumentasi lengkap berupa foto, video, dan deskripsi detail kondisi kost yang akan membantu Anda dalam mengambil keputusan tanpa harus datang langsung ke lokasi.',
 '• Survey dilakukan setelah pembayaran terverifikasi.\n• Hasil survey dikirim via Whatsapp (Link GDrive).\n• Dokumentasi mencakup kondisi kamar, WC, & lingkungan.\n• Pembayaran via Transfer Bank.'
);

-- Default Admin (Password: admin123)
-- Hash: $2y$10$8.1p... (You should generate a real hash, but for now we can use a placeholder or insert via PHP)
-- For this script, we'll insert a raw password and rely on the PHP application to handle hashing or manual update.
-- IN PRODUCTION: ALWAYS USE PASSWORD_HASH(). CHECK login_admin.php for implementation.
INSERT INTO admin (username, password, nama_lengkap, email) VALUES 
('admin', 'admin123', 'Administrator', 'admin@survey.com'); 
-- Password default adalah 'admin123' (Plain Text)
