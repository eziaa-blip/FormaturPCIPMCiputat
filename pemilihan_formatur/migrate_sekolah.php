<?php
require_once 'config/database.php';

// 1. Buat tabel sekolah
$sql_create_sekolah = "
CREATE TABLE IF NOT EXISTS sekolah (
    id_sekolah INT AUTO_INCREMENT PRIMARY KEY,
    nama_sekolah VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
";

// 2. Insert data sekolah default
$sql_insert_sekolah = array(
    "INSERT IGNORE INTO sekolah (nama_sekolah) VALUES ('SMAM8')",
    "INSERT IGNORE INTO sekolah (nama_sekolah) VALUES ('SMKM3')",
    "INSERT IGNORE INTO sekolah (nama_sekolah) VALUES ('SMKM1')"
);

$success = true;
$messages = array();

// Buat tabel
if ($conn->query($sql_create_sekolah) === TRUE) {
    $messages[] = "✓ Tabel sekolah berhasil dibuat";
} else {
    if (strpos($conn->error, 'already exists') !== false) {
        $messages[] = "✓ Tabel sekolah sudah ada";
    } else {
        $messages[] = "✗ Error membuat tabel: " . $conn->error;
        $success = false;
    }
}

// Insert data sekolah
foreach ($sql_insert_sekolah as $query) {
    if ($conn->query($query) === TRUE) {
        $messages[] = "✓ Data sekolah berhasil ditambahkan";
    } else {
        if (strpos($conn->error, 'Duplicate entry') !== false) {
            // Abaikan jika sudah ada
        } else {
            $messages[] = "✗ Error: " . $conn->error;
            $success = false;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migrasi Database</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 500px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .messages {
            list-style: none;
            margin-bottom: 30px;
        }
        .messages li {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            background: #f5f5f5;
            color: #333;
        }
        .messages li:before {
            content: "• ";
            color: #667eea;
            font-weight: bold;
        }
        .button-group {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        a {
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
        }
        a:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>✓ Migrasi Database Berhasil</h2>
        <ul class="messages">
            <?php foreach ($messages as $msg): ?>
                <li><?php echo htmlspecialchars($msg); ?></li>
            <?php endforeach; ?>
        </ul>
        <div class="button-group">
            <a href="index.php">Kembali ke Homepage</a>
            <a href="login.php">Login</a>
        </div>
    </div>
</body>
</html>
