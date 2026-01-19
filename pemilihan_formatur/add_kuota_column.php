<?php
/**
 * Script untuk menambahkan kolom kuota ke tabel sekolah
 * Jalankan file ini di browser untuk mengupdate database
 */

require_once 'config/database.php';

$success = false;
$message = '';

// Cek apakah kolom kuota sudah ada
$result = $conn->query("SHOW COLUMNS FROM sekolah LIKE 'kuota'");

if ($result->num_rows === 0) {
    // Kolom belum ada, tambahkan
    if ($conn->query("ALTER TABLE sekolah ADD COLUMN kuota INT DEFAULT 5") === TRUE) {
        $success = true;
        $message = 'Kolom kuota berhasil ditambahkan dengan nilai default 5 untuk semua sekolah!';
    } else {
        $message = 'Error: ' . $conn->error;
    }
} else {
    // Kolom sudah ada
    $success = true;
    $message = 'Kolom kuota sudah ada di database.';
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Database - E-Voting</title>
    <style>
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
            text-align: center;
        }
        
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        
        .alert {
            padding: 20px;
            border-radius: 5px;
            font-size: 16px;
            margin-bottom: 30px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            border: none;
            cursor: pointer;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✓ Update Database</h1>
        
        <div class="alert alert-success">
            <?php echo htmlspecialchars($message); ?>
        </div>
        
        <p>Sistem kuota pendaftar per sekolah telah berhasil dikonfigurasi!</p>
        <p style="margin-top: 20px; color: #666; font-size: 14px;">
            Anda sekarang dapat mengelola kuota pendaftar untuk setiap sekolah melalui menu Kelola Sekolah di admin panel.
        </p>
        
        <a href="pages/admin/kelola_sekolah.php" class="btn" style="margin-top: 30px;">Kelola Kuota Sekolah →</a>
        <a href="login.php" class="btn" style="margin-top: 15px; background: #888;">Kembali ke Login</a>
    </div>
</body>
</html>
