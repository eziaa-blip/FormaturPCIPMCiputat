<?php
require_once 'config/database.php';

// Query untuk menambahkan kolom asal_sekolah jika belum ada
$sql = "ALTER TABLE users ADD COLUMN asal_sekolah VARCHAR(100) AFTER password";

if ($conn->query($sql) === TRUE) {
    echo "<div style='background-color: #d4edda; color: #155724; padding: 20px; border-radius: 5px; text-align: center; max-width: 500px; margin: 50px auto; font-family: Arial;'>
        <h2>✓ Berhasil!</h2>
        <p>Kolom 'asal_sekolah' telah ditambahkan ke tabel users.</p>
        <p>Anda sekarang bisa <a href='register.php' style='color: #155724; font-weight: bold;'>mendaftar akun baru</a></p>
        <p style='font-size: 12px; margin-top: 20px;'>Halaman ini bisa dihapus setelah proses selesai.</p>
    </div>";
} else {
    // Mungkin kolom sudah ada
    if (strpos($conn->error, 'Duplicate column') !== false) {
        echo "<div style='background-color: #fff3cd; color: #856404; padding: 20px; border-radius: 5px; text-align: center; max-width: 500px; margin: 50px auto; font-family: Arial;'>
            <h2>✓ Informasi</h2>
            <p>Kolom 'asal_sekolah' sudah ada di database.</p>
            <p>Anda sekarang bisa <a href='register.php' style='color: #856404; font-weight: bold;'>mendaftar akun baru</a></p>
        </div>";
    } else {
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px; text-align: center; max-width: 500px; margin: 50px auto; font-family: Arial;'>
            <h2>✗ Error</h2>
            <p>Error: " . htmlspecialchars($conn->error) . "</p>
        </div>";
    }
}

$conn->close();
?>
