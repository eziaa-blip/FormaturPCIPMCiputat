<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check apakah user sudah login dan role admin
if (!isLoggedIn() || getUserRole() !== 'admin') {
    header('Location: ../../login.php');
    exit();
}

// Get filter sekolah
$filter_sekolah = $_GET['sekolah'] ?? '';

// Get semua sekolah untuk dropdown
$result_sekolah = $conn->query("SELECT * FROM sekolah ORDER BY nama_sekolah ASC");
$sekolah_list = [];
while ($row = $result_sekolah->fetch_assoc()) {
    $sekolah_list[] = $row;
}

// Query pemilih dan voting mereka
$query = "SELECT 
            u.id_user,
            u.nama,
            u.asal_sekolah,
            u.sudah_memilih,
            GROUP_CONCAT(k.nama_kandidat SEPARATOR ', ') as kandidat_pilihan
         FROM users u
         LEFT JOIN voting v ON u.id_user = v.id_user
         LEFT JOIN kandidat k ON v.id_kandidat = k.id_kandidat
         WHERE u.role = 'pemilih'";

if (!empty($filter_sekolah)) {
    $query .= " AND u.asal_sekolah = '" . $conn->real_escape_string($filter_sekolah) . "'";
}

$query .= " GROUP BY u.id_user
            ORDER BY u.asal_sekolah, u.nama";

$result_pemilih = $conn->query($query);
$pemilih_data = [];
while ($row = $result_pemilih->fetch_assoc()) {
    $pemilih_data[] = $row;
}

// Group data by sekolah
$pemilih_by_sekolah = [];
foreach ($pemilih_data as $pemilih) {
    $sekolah = $pemilih['asal_sekolah'] ?? 'Belum Ditentukan';
    if (!isset($pemilih_by_sekolah[$sekolah])) {
        $pemilih_by_sekolah[$sekolah] = [];
    }
    $pemilih_by_sekolah[$sekolah][] = $pemilih;
}

// Get statistik per sekolah
$statistik_sekolah = [];
foreach ($sekolah_list as $sekolah) {
    $nama = $sekolah['nama_sekolah'];
    $stmt = $conn->prepare("SELECT 
                             COUNT(DISTINCT u.id_user) as total_pemilih,
                             COUNT(DISTINCT v.id_user) as pemilih_voting,
                             COUNT(v.id_voting) as total_suara
                           FROM users u
                           LEFT JOIN voting v ON u.id_user = v.id_user
                           WHERE u.role = 'pemilih' AND u.asal_sekolah = ?");
    $stmt->bind_param("s", $nama);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats = $result->fetch_assoc();
    $stmt->close();
    
    $statistik_sekolah[$nama] = $stats;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Voting Detail - E-Voting Pemilihan Formatur</title>
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
            padding: 0 30px 40px;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 30px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(-5px);
        }
        
        .filter-section {
            background: white;
            padding: 28px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 35px;
        }
        
        .filter-group {
            display: flex;
            gap: 18px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        
        .filter-item {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .filter-item label {
            color: #333;
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .filter-item select {
            padding: 12px 14px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 14px;
            background: #f8f9fa;
            transition: all 0.3s ease;
            font-family: inherit;
            min-width: 250px;
        }
        
        .filter-item select:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        
        .btn-filter {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 28px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-reset {
            background: #999;
            color: white;
            padding: 12px 28px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            font-size: 14px;
        }
        
        .btn-reset:hover {
            background: #777;
            transform: translateY(-2px);
        }
        
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .summary-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-left: 5px solid #667eea;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }
        
        .summary-card h3 {
            color: #999;
            font-size: 11px;
            margin-bottom: 15px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        .summary-card .number {
            font-size: 38px;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }
        
        .summary-card p {
            color: #999;
            font-size: 12px;
        }
        
        .sekolah-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .sekolah-section:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }
        
        .sekolah-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .sekolah-header h2 {
            font-size: 22px;
            margin: 0;
            font-weight: 700;
        }
        
        .sekolah-stats {
            display: flex;
            gap: 25px;
            font-size: 13px;
            flex-wrap: wrap;
        }
        
        .stat-item {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        
        .stat-item strong {
            color: white;
            font-weight: 700;
        }
        
        .sekolah-content {
            padding: 28px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table thead {
            background: #f8f9fa;
        }
        
        table th {
            padding: 16px;
            text-align: left;
            font-weight: 700;
            color: #333;
            border-bottom: 2px solid #e9ecef;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        table td {
            padding: 16px;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
        }
        
        table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .no-voting {
            color: #999;
            font-style: italic;
        }
        
        .voting-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .badge {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            padding: 7px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.15);
        }
        
        .badge-count {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 7px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }
        
        .empty-message {
            text-align: center;
            padding: 60px 30px;
            color: #999;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        .empty-message p {
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="navbar-left">
            <h1>📊 Laporan Voting</h1>
            <p>Detail Pemilih & Voting</p>
        </div>
        <div class="navbar-right">
            <span>👤 <?php echo htmlspecialchars($_SESSION['nama']); ?></span>
            <a href="../../logout.php" class="btn-logout">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <a href="dashboard.php" class="back-link">← Kembali ke Dashboard</a>
        
        <!-- SUMMARY STATISTIK -->
        <div class="summary-stats">
            <?php foreach ($sekolah_list as $sekolah): 
                $stats = $statistik_sekolah[$sekolah['nama_sekolah']] ?? [];
            ?>
                <div class="summary-card">
                    <h3>🏫 <?php echo htmlspecialchars($sekolah['nama_sekolah']); ?></h3>
                    <p class="number"><?php echo intval($stats['total_pemilih'] ?? 0); ?></p>
                    <p>Pemilih Terdaftar</p>
                    <p style="color: #667eea; font-weight: 600; margin-top: 12px;">
                        ✓ <?php echo intval($stats['pemilih_voting'] ?? 0); ?> voting
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- FILTER SECTION -->
        <div class="filter-section">
            <form method="GET" class="filter-group">
                <div class="filter-item">
                    <label for="sekolah">🔍 Filter Sekolah</label>
                    <select name="sekolah" id="sekolah">
                        <option value="">-- Semua Sekolah --</option>
                        <?php foreach ($sekolah_list as $sekolah): ?>
                            <option value="<?php echo htmlspecialchars($sekolah['nama_sekolah']); ?>"
                                <?php echo $filter_sekolah === $sekolah['nama_sekolah'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($sekolah['nama_sekolah']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-filter">🔎 Filter Data</button>
                <a href="laporan_voting.php" class="btn-reset">↺ Reset Filter</a>
            </form>
        </div>
        
        <!-- LAPORAN PER SEKOLAH -->
        <?php if (empty($pemilih_data)): ?>
            <div class="empty-message">
                <p>📭 Tidak ada data pemilih untuk ditampilkan</p>
            </div>
        <?php else: ?>
            <?php foreach ($pemilih_by_sekolah as $sekolah => $pemilih_list): 
                if (!empty($filter_sekolah) && $sekolah !== $filter_sekolah) continue;
                
                $total_voting = 0;
                $total_suara = 0;
                foreach ($pemilih_list as $p) {
                    if (!empty($p['kandidat_pilihan'])) {
                        $total_voting++;
                        $total_suara += $p['sudah_memilih'];
                    }
                }
            ?>
                <div class="sekolah-section">
                    <div class="sekolah-header">
                        <h2>🏫 <?php echo htmlspecialchars($sekolah); ?></h2>
                        <div class="sekolah-stats">
                            <div class="stat-item">
                                <strong>👥 <?php echo count($pemilih_list); ?></strong>
                                Pemilih
                            </div>
                            <div class="stat-item">
                                <strong>✓ <?php echo $total_voting; ?></strong>
                                Voting
                            </div>
                            <div class="stat-item">
                                <strong>🎯 <?php echo $total_suara; ?></strong>
                                Suara
                            </div>
                        </div>
                    </div>
                    
                    <div class="sekolah-content">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 40px;">No</th>
                                    <th style="width: 220px;">Nama Pemilih</th>
                                    <th style="width: 120px;">Suara</th>
                                    <th>Kandidat Pilihan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($pemilih_list as $pemilih): ?>
                                    <tr>
                                        <td><strong>#<?php echo $no++; ?></strong></td>
                                        <td><strong><?php echo htmlspecialchars($pemilih['nama']); ?></strong></td>
                                        <td>
                                            <strong style="color: #667eea; font-size: 15px;"><?php echo intval($pemilih['sudah_memilih']); ?>/9</strong>
                                        </td>
                                        <td>
                                            <?php if (!empty($pemilih['kandidat_pilihan'])): ?>
                                                <div class="voting-badges">
                                                    <?php 
                                                    $kandidat_arr = explode(', ', $pemilih['kandidat_pilihan']);
                                                    $count = count($kandidat_arr);
                                                    foreach (array_slice($kandidat_arr, 0, 3) as $kand): 
                                                    ?>
                                                        <span class="badge">✓ <?php echo htmlspecialchars($kand); ?></span>
                                                    <?php endforeach; ?>
                                                    <?php if ($count > 3): ?>
                                                        <span class="badge-count">+<?php echo ($count - 3); ?> lagi</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="no-voting">Belum memilih</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
