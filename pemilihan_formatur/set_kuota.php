<?php
/**
 * Script untuk mengubah kuota PIMPINAN CABANG CIPUTAT menjadi 39
 */

require_once 'config/database.php';

$success = false;
$message = '';
$sekolah_name = 'PIMPINAN CABANG CIPUTAT';
$kuota_baru = 39;

// Update kuota
$stmt = $conn->prepare("UPDATE sekolah SET kuota = ? WHERE nama_sekolah = ?");
$stmt->bind_param("is", $kuota_baru, $sekolah_name);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $success = true;
        $message = "Kuota untuk $sekolah_name berhasil diubah menjadi $kuota_baru!";
    } else {
        // Sekolah belum ada, tambahkan
        $stmt2 = $conn->prepare("INSERT INTO sekolah (nama_sekolah, kuota) VALUES (?, ?)");
        $stmt2->bind_param("si", $sekolah_name, $kuota_baru);
        
        if ($stmt2->execute()) {
            $success = true;
            $message = "Sekolah '$sekolah_name' berhasil ditambahkan dengan kuota $kuota_baru!";
        } else {
            $message = 'Error: ' . $stmt2->error;
        }
        $stmt2->close();
    }
} else {
    $message = 'Error: ' . $stmt->error;
}

$stmt->close();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Kuota - E-Voting</title>
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
        
        .info-box {
            background: #e3f2fd;
            border: 2px solid #90caf9;
            color: #1565c0;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
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
        <h1><?php echo $success ? '✓ Berhasil!' : '✗ Gagal'; ?></h1>
        
        <div class="alert alert-success">
            <?php echo htmlspecialchars($message); ?>
        </div>
        
        <div class="info-box">
            <strong>Informasi Perubahan:</strong><br>
            Sekolah: <?php echo htmlspecialchars($sekolah_name); ?><br>
            Kuota Baru: <?php echo $kuota_baru; ?> pemilih
        </div>
        
        <p style="color: #666; font-size: 14px;">
            Perubahan kuota telah diterapkan. Pendaftar baru akan mengikuti batasan kuota yang telah diubah.
        </p>
        
        <a href="pages/admin/kelola_sekolah.php" class="btn">Kelola Sekolah</a>
        <a href="index.php" class="btn" style="background: #888;">Kembali</a>
    </div>
</body>
</html>
