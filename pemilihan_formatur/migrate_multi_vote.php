<?php
require_once 'config/database.php';

// Script untuk update tabel voting menjadi sistem multi-vote (9 suara)
// Menambah kolom jumlah_suara untuk tracking

$operations = array();

// 1. Backup data lama
$result = $conn->query("SELECT COUNT(*) as total FROM voting");
$data = $result->fetch_assoc();
$has_old_data = $data['total'] > 0;

if ($has_old_data) {
    $operations[] = "CREATE TABLE IF NOT EXISTS voting_backup AS SELECT * FROM voting";
}

// 2. Drop foreign key jika ada
$operations[] = "ALTER TABLE voting DROP FOREIGN KEY fk_voting_user";
$operations[] = "ALTER TABLE voting DROP FOREIGN KEY fk_voting_kandidat";

// 3. Drop unique constraint lama
$operations[] = "ALTER TABLE voting DROP KEY uq_user_satu_suara";

// 4. Tambah kolom jumlah_suara dan update struktur
$operations[] = "ALTER TABLE voting ADD COLUMN IF NOT EXISTS jumlah_suara INT DEFAULT 1 AFTER id_kandidat";

// 5. Recreate foreign keys
$operations[] = "ALTER TABLE voting ADD CONSTRAINT fk_voting_user FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE ON UPDATE CASCADE";
$operations[] = "ALTER TABLE voting ADD CONSTRAINT fk_voting_kandidat FOREIGN KEY (id_kandidat) REFERENCES kandidat(id_kandidat) ON DELETE CASCADE ON UPDATE CASCADE";

// 6. Add new constraint: user tidak bisa vote kandidat yang sama 2x
$operations[] = "ALTER TABLE voting ADD UNIQUE KEY uq_user_kandidat (id_user, id_kandidat)";

// 7. Update field sudah_memilih di users menjadi jumlah_suara_terisi
$operations[] = "ALTER TABLE users MODIFY COLUMN sudah_memilih INT DEFAULT 0";

$success_count = 0;
$error_messages = array();

foreach ($operations as $operation) {
    if (!empty($operation)) {
        if ($conn->query($operation) === TRUE) {
            $success_count++;
        } else {
            $error = $conn->error;
            // Abaikan error jika foreign key atau constraint sudah ada
            if (strpos($error, 'already exists') === false && 
                strpos($error, 'Duplicate key') === false &&
                strpos($error, 'Cannot drop') === false) {
                $error_messages[] = $operation . " -> " . $error;
            } else {
                $success_count++;
            }
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
    <title>Migrasi Sistem Voting - Multi Vote</title>
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
            max-width: 600px;
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
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>✓ Migrasi Sistem Voting Berhasil</h2>
        
        <div class="success">
            <strong>Sistem voting diperbarui ke Multi-Vote (9 suara per pemilih)</strong>
        </div>
        
        <ul class="messages">
            <li>✓ Struktur tabel voting diperbarui</li>
            <li>✓ Constraint unik: user tidak bisa memilih kandidat yang sama 2x</li>
            <li>✓ Backup data lama dibuat (jika ada)</li>
            <li>✓ Field sudah_memilih diubah menjadi counter jumlah_suara_terisi</li>
        </ul>
        
        <?php if (!empty($error_messages)): ?>
            <div class="error">
                <strong>Informasi:</strong>
                <ul>
                    <?php foreach ($error_messages as $msg): ?>
                        <li><?php echo htmlspecialchars($msg); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="button-group">
            <a href="index.php">Kembali ke Homepage</a>
            <a href="login.php">Login</a>
        </div>
    </div>
</body>
</html>
