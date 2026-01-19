<?php
/**
 * Migration script to add voting_selesai column to users table
 */

require_once 'config/database.php';

$success = false;
$message = '';

// Check if column already exists
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'voting_selesai'");

if ($result->num_rows > 0) {
    $message = "Kolom 'voting_selesai' sudah ada di tabel users";
} else {
    // Add column
    $sql = "ALTER TABLE users ADD COLUMN voting_selesai TINYINT(1) DEFAULT 0 AFTER sudah_memilih";
    
    if ($conn->query($sql) === TRUE) {
        $success = true;
        $message = "Kolom 'voting_selesai' berhasil ditambahkan ke tabel users";
    } else {
        $message = 'Error: ' . $conn->error;
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migration - E-Voting</title>
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
        
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
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
            margin-top: 10px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo $success ? '✓ Berhasil!' : '✓ Info'; ?></h1>
        
        <div class="alert <?php echo $success ? 'alert-success' : 'alert-info'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        
        <p style="color: #666; font-size: 14px; margin-bottom: 30px;">
            Fitur "Kumpulkan Suara" sekarang siap digunakan.<br>
            Pemilih harus mengumpulkan semua 9 suara sebelum voting selesai.
        </p>
        
        <a href="pages/pemilih/dashboard.php" class="btn">Ke Dashboard Pemilih</a>
        <a href="index.php" class="btn" style="background: #888;">Kembali ke Home</a>
    </div>
</body>
</html>
