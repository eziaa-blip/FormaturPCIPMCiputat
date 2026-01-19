<?php
require_once 'config/database.php';

echo "<h2>Verifikasi Data Sekolah</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Nama Sekolah</th><th>Kuota</th></tr>";

$result = $conn->query("SELECT id_sekolah, nama_sekolah, kuota FROM sekolah ORDER BY nama_sekolah");
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id_sekolah'] . "</td>";
    echo "<td>" . htmlspecialchars($row['nama_sekolah']) . "</td>";
    echo "<td>" . $row['kuota'] . " pemilih</td>";
    echo "</tr>";
}
echo "</table>";

// Highlight PIMPINAN CABANG CIPUTAT
$stmt = $conn->prepare("SELECT kuota FROM sekolah WHERE nama_sekolah = ?");
$sekolah = 'PIMPINAN CABANG CIPUTAT';
$stmt->bind_param("s", $sekolah);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

echo "<br><hr><br>";
echo "<h3>Status PIMPINAN CABANG CIPUTAT:</h3>";
if ($data) {
    echo "<p style='background: #d4edda; padding: 15px; border-radius: 5px;'>";
    echo "✅ Kuota saat ini: <strong>" . $data['kuota'] . " pemilih</strong>";
    echo "</p>";
} else {
    echo "<p style='background: #fee; padding: 15px; border-radius: 5px;'>";
    echo "❌ Sekolah belum terdaftar";
    echo "</p>";
}
?>
