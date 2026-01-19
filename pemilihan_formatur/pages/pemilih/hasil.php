<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check apakah user sudah login dan role pemilih
if (!isLoggedIn() || getUserRole() !== 'pemilih') {
    header('Location: ../../login.php');
    exit();
}

$hasilVoting = getHasilVoting($conn);
$totalSuara = getTotalSuaraIsi($conn);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Voting - E-Voting</title>
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
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .navbar h1 {
            font-size: 24px;
        }
        
        .navbar-right {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .btn-logout {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid white;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        
        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .title {
            color: #333;
            margin-bottom: 20px;
            font-size: 20px;
            font-weight: 600;
        }
        
        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .result-item {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .result-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .result-name {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
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
            font-size: 13px;
            transition: width 0.3s;
        }
        
        .result-info {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: #666;
        }
        
        .info-box {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            color: #004085;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div>
            <h1>E-Voting Pemilihan Formatur</h1>
            <p>Hasil Voting</p>
        </div>
        <div class="navbar-right">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['nama']); ?></span>
            <a href="../../logout.php" class="btn-logout">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <a href="dashboard.php" class="back-link">← Kembali ke Dashboard</a>
        
        <h2 class="title">Hasil Voting Real-Time</h2>
        
        <div class="info-box">
            Total suara yang telah masuk: <strong><?php echo $totalSuara; ?></strong> suara
        </div>
        
        <div class="card">
            <?php if (empty($hasilVoting)): ?>
                <p style="color: #999; text-align: center; padding: 30px;">
                    Belum ada data voting
                </p>
            <?php else: ?>
                <?php foreach ($hasilVoting as $hasil): ?>
                    <div class="result-item">
                        <div style="display: flex; gap: 15px; align-items: flex-start;">
                            <?php if (!empty($hasil['foto'])): ?>
                                <img src="<?php echo SITE_URL; ?>assets/images/kandidat/<?php echo htmlspecialchars($hasil['foto']); ?>" 
                                     alt="<?php echo htmlspecialchars($hasil['nama_kandidat']); ?>" 
                                     style="width: 80px; height: 80px; border-radius: 5px; object-fit: cover; flex-shrink: 0;">
                            <?php else: ?>
                                <div style="width: 80px; height: 80px; background: #e9ecef; border-radius: 5px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <span style="color: #999; font-size: 12px; text-align: center;">Tidak ada foto</span>
                                </div>
                            <?php endif; ?>
                            <div style="flex: 1;">
                                <div class="result-name">
                                    <?php echo htmlspecialchars($hasil['nama_kandidat']); ?>
                                </div>
                                <div class="progress-bar">
                                    <?php 
                                    $persentasi = $totalSuara > 0 ? ($hasil['jumlah_suara'] / $totalSuara) * 100 : 0;
                                    ?>
                                    <div class="progress-fill" style="width: <?php echo $persentasi; ?>%;">
                                        <?php echo round($persentasi, 1); ?>%
                                    </div>
                                </div>
                                <div class="result-info">
                                    <span>Suara: <strong><?php echo $hasil['jumlah_suara']; ?></strong></span>
                                    <span>Persentase: <strong><?php echo round($persentasi, 1); ?>%</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
