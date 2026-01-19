<?php
// ===================================================
// DATABASE SETUP - Jalankan file ini di browser
// Atau copy-paste query ke phpMyAdmin
// ===================================================
// Akses: http://localhost/pemilihan_formatur/setup.php

require_once 'config/database.php';

$sql = "
-- =========================================================
-- DATABASE: pemilihan_formatur
-- =========================================================

-- =========================================================
-- TABLE: users (admin & pemilih)
-- =========================================================
CREATE TABLE IF NOT EXISTS users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    asal_sekolah VARCHAR(100),
    role ENUM('admin','pemilih') NOT NULL,
    sudah_memilih TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- TABLE: kandidat
-- =========================================================
CREATE TABLE IF NOT EXISTS kandidat (
    id_kandidat INT AUTO_INCREMENT PRIMARY KEY,
    nama_kandidat VARCHAR(100) NOT NULL,
    visi TEXT,
    misi TEXT,
    foto VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- TABLE: voting
-- =========================================================
CREATE TABLE IF NOT EXISTS voting (
    id_voting INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_kandidat INT NOT NULL,
    waktu_memilih TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_voting_user
        FOREIGN KEY (id_user) REFERENCES users(id_user)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_voting_kandidat
        FOREIGN KEY (id_kandidat) REFERENCES kandidat(id_kandidat)
        ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uq_user_satu_suara (id_user)
) ENGINE=InnoDB;

-- =========================================================
-- TABLE: sekolah
-- =========================================================
CREATE TABLE IF NOT EXISTS sekolah (
    id_sekolah INT AUTO_INCREMENT PRIMARY KEY,
    nama_sekolah VARCHAR(100) NOT NULL UNIQUE,
    kuota INT DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- TABLE: periode_pemilihan
-- =========================================================
CREATE TABLE IF NOT EXISTS periode_pemilihan (
    id_periode INT AUTO_INCREMENT PRIMARY KEY,
    nama_periode VARCHAR(100),
    tanggal_mulai DATE,
    tanggal_selesai DATE,
    status ENUM('aktif','nonaktif') DEFAULT 'nonaktif'
) ENGINE=InnoDB;
";

$queries = array_filter(array_map('trim', explode(';', $sql)));

$success_count = 0;
$error_count = 0;
$errors = array();

foreach ($queries as $query) {
    if (!empty($query)) {
        if ($conn->query($query) === TRUE) {
            $success_count++;
        } else {
            $error_count++;
            $errors[] = $conn->error;
        }
    }
}

// Insert data contoh
$insert_sql = array(
    "INSERT IGNORE INTO sekolah (nama_sekolah) VALUES ('SMAM8')",
    "INSERT IGNORE INTO sekolah (nama_sekolah) VALUES ('SMKM3')",
    "INSERT IGNORE INTO sekolah (nama_sekolah) VALUES ('SMKM1')",
    "INSERT IGNORE INTO users (nama, username, password, role) VALUES ('Admin Utama', 'admin', MD5('admin123'), 'admin')",
    "INSERT IGNORE INTO users (nama, username, password, role) VALUES ('Budi', 'budi01', MD5('123456'), 'pemilih')",
    "INSERT IGNORE INTO users (nama, username, password, role) VALUES ('Siti', 'siti01', MD5('123456'), 'pemilih')",
    "INSERT IGNORE INTO kandidat (nama_kandidat, visi, misi) VALUES ('Andi Saputra', 'Mewujudkan organisasi yang solid', 'Transparansi, Kolaborasi')",
    "INSERT IGNORE INTO kandidat (nama_kandidat, visi, misi) VALUES ('Rizky Pratama', 'Organisasi aktif dan progresif', 'Inovasi, Aspiratif')",
    "INSERT IGNORE INTO periode_pemilihan (nama_periode, tanggal_mulai, tanggal_selesai, status) VALUES ('Pemilihan Formatur 2026', '2026-01-01', '2026-01-31', 'aktif')"
);

foreach ($insert_sql as $query) {
    if ($conn->query($query) === TRUE) {
        $success_count++;
    } else {
        // Abaikan error duplicate entry
        if (strpos($conn->error, 'Duplicate') === false) {
            $error_count++;
            $errors[] = $conn->error;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Database - E-Voting</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 100%;
        }
        
        h1 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .alert ul {
            margin-left: 20px;
            margin-top: 10px;
        }
        
        .alert li {
            margin-bottom: 5px;
        }
        
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            text-align: center;
            margin-top: 20px;
            width: 100%;
            border: none;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .credentials {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        
        .credentials h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .credentials p {
            color: #666;
            font-size: 13px;
            margin-bottom: 8px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✓ Setup Database Berhasil!</h1>
        
        <div class="alert alert-success">
            Database dan tabel telah dibuat dengan sukses.
            <ul>
                <li>Total query berhasil: <strong><?php echo $success_count; ?></strong></li>
                <?php if ($error_count > 0): ?>
                    <li>Total error: <strong><?php echo $error_count; ?></strong></li>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="credentials">
            <h3>📋 Akun Demo untuk Login:</h3>
            <p><strong>Admin</strong></p>
            <p>Username: <code>admin</code></p>
            <p>Password: <code>admin123</code></p>
            
            <p style="margin-top: 15px;"><strong>Pemilih</strong></p>
            <p>Username: <code>budi01</code></p>
            <p>Password: <code>123456</code></p>
            
            <p>atau</p>
            <p>Username: <code>siti01</code></p>
            <p>Password: <code>123456</code></p>
        </div>
        
        <div class="alert alert-info">
            <strong>Catatan:</strong>
            <ul>
                <li>2 Kandidat demo sudah ditambahkan</li>
                <li>3 Akun demo sudah dibuat (1 admin, 2 pemilih)</li>
                <li>Password menggunakan hash MD5 (jangan gunakan di production)</li>
            </ul>
        </div>
        
        <a href="login.php" class="btn">Lanjut ke Halaman Login →</a>
    </div>
</body>
</html>
