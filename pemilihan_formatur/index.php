<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get statistik untuk public dashboard
$totalPemilih = getTotalPemilih($conn);
$totalSuaraIsi = getTotalSuaraIsi($conn);
$hasilVoting = getHasilVoting($conn);

$persentasiTing = $totalPemilih > 0 ? ($totalSuaraIsi / $totalPemilih) * 100 : 0;

// Get periode
$periode_result = $conn->query("SELECT * FROM periode_pemilihan WHERE status = 'aktif' LIMIT 1");
$periode = $periode_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Voting Pemilihan Formatur Cabang Ciputat 2026</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 20px;
            text-align: center;
            min-height: 400px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .hero h1 {
            font-size: 48px;
            margin-bottom: 10px;
        }
        
        .hero p {
            font-size: 20px;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        
        .hero .btn {
            display: inline-block;
            background: white;
            color: #667eea;
            padding: 15px 40px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: transform 0.2s;
        }
        
        .hero .btn:hover {
            transform: translateY(-2px);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 50px;
        }
        
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .card h3 {
            color: #667eea;
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .card p {
            color: #666;
            font-size: 14px;
        }
        
        .section-title {
            color: #333;
            font-size: 28px;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
        }
        
        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .result-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .result-card h4 {
            color: #333;
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .progress-bar {
            background: #e9ecef;
            height: 30px;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 10px;
        }
        
        .progress-fill {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 12px;
        }
        
        .vote-count {
            font-size: 20px;
            font-weight: bold;
            color: #667eea;
            text-align: center;
        }
        
        .footer {
            background: #333;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="hero">
        <h1>E-Voting Pemilihan Formatur</h1>
        <p>Cabang Ciputat 2026</p>
        <p style="font-size: 16px; opacity: 0.8;">
            <?php if ($periode): ?>
                Periode: <?php echo htmlspecialchars($periode['nama_periode']); ?> 
                (<?php echo formatTanggalIndonesia($periode['tanggal_mulai']); ?> - <?php echo formatTanggalIndonesia($periode['tanggal_selesai']); ?>)
            <?php endif; ?>
        </p>
        <a href="login.php" class="btn">Mulai Pemilihan</a>
    </div>
    
    <div class="container">
        <div class="grid">
            <div class="card">
                <h3><?php echo $totalPemilih; ?></h3>
                <p>Total Pemilih Terdaftar</p>
            </div>
            <div class="card">
                <h3><?php echo $totalSuaraIsi; ?></h3>
                <p>Suara yang Telah Masuk</p>
            </div>
            <div class="card">
                <h3><?php echo round($persentasiTing, 1); ?>%</h3>
                <p>Tingkat Partisipasi</p>
            </div>
        </div>
        
        <h2 class="section-title">Hasil Voting Real-Time</h2>
        <div class="results-grid">
            <?php if (empty($hasilVoting)): ?>
                <p style="color: #999; text-align: center; grid-column: 1/-1; padding: 30px;">
                    Belum ada data voting
                </p>
            <?php else: ?>
                <?php foreach ($hasilVoting as $hasil): ?>
                    <div class="result-card">
                        <h4><?php echo htmlspecialchars($hasil['nama_kandidat']); ?></h4>
                        <div class="progress-bar">
                            <?php 
                            $persentasi = $totalSuaraIsi > 0 ? ($hasil['jumlah_suara'] / $totalSuaraIsi) * 100 : 0;
                            ?>
                            <div class="progress-fill" style="width: <?php echo $persentasi; ?>%;">
                                <?php echo round($persentasi, 1); ?>%
                            </div>
                        </div>
                        <div class="vote-count"><?php echo $hasil['jumlah_suara']; ?> Suara</div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="footer">
        <p>E-Voting Pemilihan Formatur 2026 © 2026 - All Rights Reserved</p>
    </div>
</body>
</html>
