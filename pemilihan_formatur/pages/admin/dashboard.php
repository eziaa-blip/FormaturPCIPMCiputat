<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check apakah user sudah login dan role admin
if (!isLoggedIn() || getUserRole() !== 'admin') {
    header('Location: ../../login.php');
    exit();
}

// Get statistik
$totalPemilih = getTotalPemilih($conn);
$totalSuaraIsi = getTotalSuaraIsi($conn);
$hasilVoting = getHasilVoting($conn);

// Count kandidat
$resultKandidat = $conn->query("SELECT COUNT(*) as jumlah FROM kandidat");
$dataKandidat = $resultKandidat->fetch_assoc();
$totalKandidat = $dataKandidat['jumlah'];

$persentasiTing = $totalPemilih > 0 ? ($totalSuaraIsi / $totalPemilih) * 100 : 0;

// Get semua sekolah dari database
$resultSekolah = $conn->query("SELECT * FROM sekolah ORDER BY nama_sekolah ASC");
$sekolahList = array();
while ($row = $resultSekolah->fetch_assoc()) {
    $sekolahList[] = $row;
}

// Get pemilih per asal sekolah
$pemilihPerSekolah = array();
foreach ($sekolahList as $sekolah) {
    $stmt = $conn->prepare("SELECT COUNT(*) as jumlah FROM users WHERE role = 'pemilih' AND asal_sekolah = ?");
    $stmt->bind_param("s", $sekolah['nama_sekolah']);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $pemilihPerSekolah[$sekolah['nama_sekolah']] = $data['jumlah'];
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - E-Voting Pemilihan Formatur</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .navbar-left h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 5px 0;
            letter-spacing: -0.5px;
        }
        
        .navbar-left p {
            font-size: 13px;
            opacity: 0.9;
            margin: 0;
        }
        
        .navbar-right {
            display: flex;
            gap: 25px;
            align-items: center;
        }
        
        .navbar-right span {
            font-size: 14px;
            font-weight: 500;
        }
        
        .btn-logout {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-logout:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        
        .container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 30px;
        }
        
        .menu {
            display: flex;
            gap: 12px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }
        
        .menu a {
            background: white;
            color: #667eea;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .menu a:hover {
            border-color: #667eea;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.2);
        }
        
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 45px;
        }
        
        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border-left: 5px solid #667eea;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.12);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), transparent);
            border-radius: 50%;
            pointer-events: none;
        }
        
        .stat-card.green {
            border-left-color: #28a745;
        }
        
        .stat-card.orange {
            border-left-color: #ffc107;
        }
        
        .stat-card.red {
            border-left-color: #dc3545;
        }
        
        .stat-label {
            color: #999;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }
        
        .stat-number {
            font-size: 42px;
            font-weight: 800;
            color: #333;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stat-card.green .stat-number {
            background: linear-gradient(135deg, #28a745, #20c997);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stat-card.orange .stat-number {
            background: linear-gradient(135deg, #ffc107, #ff9800);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stat-card.red .stat-number {
            background: linear-gradient(135deg, #dc3545, #c82333);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stat-subtitle {
            color: #bbb;
            font-size: 12px;
        }
        
        .section-title {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 30px;
            position: relative;
            padding-bottom: 15px;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 2px;
        }
        
        .sekolah-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 50px;
        }
        
        .sekolah-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .sekolah-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }
        
        .sekolah-icon {
            font-size: 40px;
            margin-bottom: 12px;
        }
        
        .sekolah-name {
            font-size: 16px;
            font-weight: 700;
            color: #333;
            margin-bottom: 12px;
        }
        
        .sekolah-count {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 5px;
        }
        
        .sekolah-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .results-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 25px;
        }
        
        .result-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        
        .result-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }
        
        .result-card h4 {
            color: #333;
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 18px;
        }
        
        .progress-bar {
            background: #e9ecef;
            height: 35px;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 12px;
            position: relative;
        }
        
        .progress-fill {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 13px;
            transition: width 0.4s ease;
            min-width: 40px;
        }
        
        .vote-count {
            font-size: 32px;
            font-weight: 800;
            color: #667eea;
            text-align: center;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="navbar-left">
            <h1>🗳️ E-Voting</h1>
            <p>Panel Administrasi</p>
        </div>
        <div class="navbar-right">
            <span>👤 <?php echo htmlspecialchars($_SESSION['nama']); ?></span>
            <a href="../../logout.php" class="btn-logout">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <!-- MENU NAVIGASI -->
        <div class="menu">
            <a href="kelola_kandidat.php">📋 Kelola Kandidat</a>
            <a href="kelola_pemilih.php">👥 Kelola Pemilih</a>
            <a href="kelola_sekolah.php">🏫 Kelola Sekolah</a>
            <a href="laporan_voting.php">📊 Laporan Voting Detail</a>
            <a href="dashboard.php">🔄 Refresh Data</a>
        </div>
        
        <!-- STATISTIK UTAMA -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-label">📍 Total Pemilih</div>
                <div class="stat-number"><?php echo $totalPemilih; ?></div>
                <div class="stat-subtitle">Pemilih Terdaftar</div>
            </div>
            <div class="stat-card green">
                <div class="stat-label">✓ Suara Masuk</div>
                <div class="stat-number"><?php echo $totalSuaraIsi; ?></div>
                <div class="stat-subtitle">Total Suara Diterima</div>
            </div>
            <div class="stat-card orange">
                <div class="stat-label">🎯 Total Kandidat</div>
                <div class="stat-number"><?php echo $totalKandidat; ?></div>
                <div class="stat-subtitle">Kandidat Terdaftar</div>
            </div>
            <div class="stat-card red">
                <div class="stat-label">📈 Partisipasi</div>
                <div class="stat-number"><?php echo round($persentasiTing, 1); ?>%</div>
                <div class="stat-subtitle">Tingkat Kehadiran</div>
            </div>
        </div>
        
        <!-- STATISTIK SEKOLAH -->
        <h2 class="section-title">📚 Pemilih Berdasarkan Sekolah</h2>
        <div class="sekolah-grid">
            <?php foreach ($sekolahList as $sekolah): ?>
                <div class="sekolah-card">
                    <div class="sekolah-icon">🏫</div>
                    <div class="sekolah-name"><?php echo htmlspecialchars($sekolah['nama_sekolah']); ?></div>
                    <div class="sekolah-count"><?php echo $pemilihPerSekolah[$sekolah['nama_sekolah']]; ?></div>
                    <div class="sekolah-label">Pemilih Terdaftar</div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- HASIL VOTING REAL-TIME -->
        <h2 class="section-title">📊 Hasil Voting Real-Time</h2>
        <div class="results-container">
            <?php if (empty($hasilVoting)): ?>
                <p style="color: #999; text-align: center; grid-column: 1/-1;">Belum ada data voting</p>
            <?php else: ?>
                <?php foreach ($hasilVoting as $hasil): ?>
                    <div class="result-card">
                        <h4>🎖️ <?php echo htmlspecialchars($hasil['nama_kandidat']); ?></h4>
                        <div class="progress-bar">
                            <?php 
                            $persentasi = $totalSuaraIsi > 0 ? ($hasil['jumlah_suara'] / $totalSuaraIsi) * 100 : 0;
                            ?>
                            <div class="progress-fill" style="width: <?php echo $persentasi; ?>%;">
                                <?php echo round($persentasi, 1); ?>%
                            </div>
                        </div>
                        <div class="vote-count"><?php echo $hasil['jumlah_suara']; ?></div>
                        <p style="color: #999; font-size: 12px; text-align: center; margin-top: 5px;">Suara Masuk</p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
