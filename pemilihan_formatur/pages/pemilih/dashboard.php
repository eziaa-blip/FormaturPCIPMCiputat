<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check apakah user sudah login dan role pemilih
if (!isLoggedIn() || getUserRole() !== 'pemilih') {
    header('Location: ../../login.php');
    exit();
}

$id_user = $_SESSION['id_user'];
$kandidat_list = getAllKandidat($conn);

// KONSTANTA
$MAX_SUARA = 9;

// Get data pemilih
$stmt = $conn->prepare("SELECT nama, asal_sekolah, sudah_memilih, voting_selesai FROM users WHERE id_user = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
$data_pemilih = $result->fetch_assoc();
$stmt->close();

$jumlah_suara_terisi = intval($data_pemilih['sudah_memilih'] ?? 0);
$voting_selesai = intval($data_pemilih['voting_selesai'] ?? 0);
$sisa_suara = $MAX_SUARA - $jumlah_suara_terisi;

// Get daftar kandidat yang sudah dipilih
$kandidat_dipilih = array();
$stmt = $conn->prepare("SELECT k.id_kandidat, k.nama_kandidat FROM voting v 
                       JOIN kandidat k ON v.id_kandidat = k.id_kandidat 
                       WHERE v.id_user = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $kandidat_dipilih[$row['id_kandidat']] = $row['nama_kandidat'];
}
$stmt->close();

// Handle cancel vote
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_vote') {
    // Prevent changing votes after finalized
    if ($voting_selesai) {
        $_SESSION['alert'] = array(
            'message' => 'Anda sudah menyelesaikan voting dan tidak bisa mengubah pilihan lagi!',
            'type' => 'error'
        );
    } else {
        $id_kandidat = intval($_POST['id_kandidat']);
        
        // Delete the vote
        $stmt = $conn->prepare("DELETE FROM voting WHERE id_user = ? AND id_kandidat = ?");
        $stmt->bind_param("ii", $id_user, $id_kandidat);
        
        if ($stmt->execute()) {
            // Update jumlah_suara_terisi
            $jumlah_suara_terisi--;
            $stmt2 = $conn->prepare("UPDATE users SET sudah_memilih = ? WHERE id_user = ?");
            $stmt2->bind_param("ii", $jumlah_suara_terisi, $id_user);
            $stmt2->execute();
            $stmt2->close();
            
            $sisa_suara = $MAX_SUARA - $jumlah_suara_terisi;
            
            // Get nama kandidat untuk message
            $stmt3 = $conn->prepare("SELECT nama_kandidat FROM kandidat WHERE id_kandidat = ?");
            $stmt3->bind_param("i", $id_kandidat);
            $stmt3->execute();
            $result3 = $stmt3->get_result();
            $kand = $result3->fetch_assoc();
            $stmt3->close();
            
            $_SESSION['alert'] = array(
                'message' => 'Suara untuk ' . htmlspecialchars($kand['nama_kandidat'] ?? '') . ' telah dibatalkan. Sisa suara: ' . $sisa_suara,
                'type' => 'success'
            );
            
            // Refresh data
            unset($kandidat_dipilih[$id_kandidat]);
            
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['alert'] = array(
                'message' => 'Terjadi kesalahan saat membatalkan suara!',
                'type' => 'error'
            );
        }
        $stmt->close();
    }
}

// Handle submit voting
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_voting') {
    if ($jumlah_suara_terisi < $MAX_SUARA) {
        $_SESSION['alert'] = array(
            'message' => 'Anda harus memberikan semua 9 suara sebelum bisa menyelesaikan voting!',
            'type' => 'error'
        );
    } else {
        // Mark voting as completed
        $stmt = $conn->prepare("UPDATE users SET voting_selesai = 1 WHERE id_user = ?");
        $stmt->bind_param("i", $id_user);
        
        if ($stmt->execute()) {
            $voting_selesai = 1;
            $_SESSION['alert'] = array(
                'message' => '✓ Terima kasih! Semua suara Anda telah dikumpulkan dan tidak bisa diubah lagi.',
                'type' => 'success'
            );
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['alert'] = array(
                'message' => 'Terjadi kesalahan saat menyelesaikan voting!',
                'type' => 'error'
            );
        }
        $stmt->close();
    }
}

// Handle voting
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_kandidat'])) {
    // Prevent voting after finalized
    if ($voting_selesai) {
        $_SESSION['alert'] = array(
            'message' => 'Anda sudah menyelesaikan voting dan tidak bisa mengubah pilihan lagi!',
            'type' => 'error'
        );
    } else {
        $id_kandidat = intval($_POST['id_kandidat']);
        
        if ($jumlah_suara_terisi >= $MAX_SUARA) {
            $_SESSION['alert'] = array(
                'message' => 'Anda sudah memberikan 9 suara! Tidak bisa menambah lagi.',
                'type' => 'error'
            );
        } else if (isset($kandidat_dipilih[$id_kandidat])) {
            $_SESSION['alert'] = array(
                'message' => 'Anda sudah memilih kandidat ini! Tidak bisa memilih kandidat yang sama dua kali.',
                'type' => 'error'
            );
        } else {
            // Insert vote
            $stmt = $conn->prepare("INSERT INTO voting (id_user, id_kandidat) VALUES (?, ?)");
            $stmt->bind_param("ii", $id_user, $id_kandidat);
            
            if ($stmt->execute()) {
                // Update jumlah_suara_terisi
                $jumlah_suara_terisi++;
                $stmt2 = $conn->prepare("UPDATE users SET sudah_memilih = ? WHERE id_user = ?");
                $stmt2->bind_param("ii", $jumlah_suara_terisi, $id_user);
                $stmt2->execute();
                
                $sisa_suara = $MAX_SUARA - $jumlah_suara_terisi;
                
                // Get nama kandidat untuk message
                $stmt3 = $conn->prepare("SELECT nama_kandidat FROM kandidat WHERE id_kandidat = ?");
                $stmt3->bind_param("i", $id_kandidat);
                $stmt3->execute();
                $result3 = $stmt3->get_result();
                $kand = $result3->fetch_assoc();
                $stmt3->close();
                
                $_SESSION['alert'] = array(
                    'message' => 'Suara Anda untuk ' . htmlspecialchars($kand['nama_kandidat'] ?? '') . ' telah tercatat. Sisa suara: ' . $sisa_suara,
                    'type' => 'success'
                );
                
                // Refresh data
                $kandidat_dipilih[$id_kandidat] = $kand['nama_kandidat'] ?? '';
                
                header("Location: dashboard.php");
                exit();
            } else {
                $_SESSION['alert'] = array(
                    'message' => 'Terjadi kesalahan saat menyimpan suara!',
                    'type' => 'error'
                );
            }
        }
    }
}

$alert = getAlert();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pemilih - E-Voting Pemilihan Formatur</title>
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
        
        .alert {
            padding: 18px 20px;
            margin-bottom: 25px;
            border-radius: 12px;
            font-size: 14px;
            border-left: 5px solid;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .alert-error {
            background-color: #fee;
            color: #721c24;
            border-color: #dc3545;
        }
        
        .alert-success {
            background-color: #efe;
            color: #155724;
            border-color: #28a745;
        }
        
        .pemilih-info {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 35px;
            border-left: 5px solid #667eea;
        }
        
        .pemilih-info h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 20px;
            font-weight: 700;
        }
        
        .info-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .info-item {
            padding: 18px;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            border-radius: 10px;
            border-left: 3px solid #667eea;
        }
        
        .info-item label {
            display: block;
            color: #999;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        
        .info-item .value {
            color: #333;
            font-size: 18px;
            font-weight: 700;
        }
        
        .suara-counter {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 35px;
            text-align: center;
            box-shadow: 0 10px 35px rgba(102, 126, 234, 0.25);
            position: relative;
            overflow: hidden;
        }
        
        .suara-counter::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }
        
        .suara-counter h3 {
            margin: 0;
            font-size: 14px;
            opacity: 0.95;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        
        .suara-counter .counter {
            font-size: 64px;
            font-weight: 800;
            margin: 0;
            line-height: 1;
        }
        
        .suara-counter .counter-text {
            font-size: 13px;
            opacity: 0.95;
            margin-top: 15px;
        }
        
        .warning-suara {
            background: #fff3cd;
            border: 2px solid #ffc107;
            color: #856404;
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 14px;
            font-weight: 600;
            animation: slideIn 0.3s ease;
        }
        
        .warning-suara.danger {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        
        .list-dipilih {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 35px;
            border-left: 5px solid #28a745;
        }
        
        .list-dipilih h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 20px;
            font-weight: 700;
        }
        
        .dipilih-items {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 12px;
        }
        
        .dipilih-item {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border: 2px solid #28a745;
            padding: 14px;
            border-radius: 8px;
            color: #155724;
            font-weight: 700;
            font-size: 13px;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.15);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        
        .dipilih-item-text {
            flex: 1;
            text-align: left;
        }
        
        .btn-cancel-vote {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 6px 10px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s ease;
        }
        
        .btn-cancel-vote:hover {
            background: #c82333;
            transform: scale(1.05);
        }
        
        .btn-cancel-vote:active {
            transform: scale(0.95);
        }
        
        .submit-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 35px;
            border-left: 5px solid #28a745;
            text-align: center;
        }
        
        .submit-section h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .submit-section p {
            color: #666;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 14px 40px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
        }
        
        .btn-submit:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.3);
        }
        
        .btn-submit:disabled {
            background: #ccc;
            cursor: not-allowed;
            opacity: 0.6;
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
        
        .candidates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
        }
        
        .candidate-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        
        .candidate-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }
        
        .candidate-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px 20px;
            text-align: center;
        }
        
        .candidate-header h3 {
            font-size: 18px;
            margin-bottom: 5px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }
        
        .candidate-body {
            padding: 25px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .candidate-photo {
            width: 100%;
            max-width: 240px;
            height: 240px;
            object-fit: cover;
            border-radius: 12px;
            margin: 0 auto 20px;
            display: block;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .candidate-body h4 {
            color: #667eea;
            font-size: 12px;
            margin-bottom: 8px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        .candidate-body p {
            color: #666;
            font-size: 13px;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .btn-vote {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s ease;
            margin-top: auto;
            font-weight: 600;
        }
        
        .btn-vote:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-vote:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            background: #ccc;
        }
        
        .complete-message {
            text-align: center;
            padding: 60px 20px;
            color: #28a745;
        }
        
        .complete-message h2 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .complete-message p {
            font-size: 16px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="navbar-left">
            <h1>🗳️ E-Voting</h1>
            <p>Dashboard Pemilih</p>
        </div>
        <div class="navbar-right">
            <span>👤 <?php echo htmlspecialchars($_SESSION['nama']); ?></span>
            <a href="../../logout.php" class="btn-logout">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="menu">
            <a href="dashboard.php">🔄 Refresh</a>
        </div>
        
        <!-- INFO PEMILIH -->
        <div class="pemilih-info">
            <h3>👤 Data Anda</h3>
            <div class="info-row">
                <div class="info-item">
                    <label>Nama Lengkap</label>
                    <div class="value"><?php echo htmlspecialchars($data_pemilih['nama'] ?? '-'); ?></div>
                </div>
                <div class="info-item">
                    <label>Asal Sekolah</label>
                    <div class="value"><?php echo htmlspecialchars($data_pemilih['asal_sekolah'] ?? '-'); ?></div>
                </div>
            </div>
        </div>
        
        <!-- SUARA COUNTER BESAR -->
        <div class="suara-counter">
            <h3>🎯 SISA SUARA ANDA</h3>
            <p class="counter"><?php echo $sisa_suara; ?> / <?php echo $MAX_SUARA; ?></p>
            <p class="counter-text">Anda telah memilih <?php echo $jumlah_suara_terisi; ?> dari <?php echo $MAX_SUARA; ?> kandidat</p>
        </div>
        
        <!-- WARNING SUARA -->
        <?php if ($sisa_suara == 0): ?>
            <div class="warning-suara danger">
                ✓ Anda sudah memberikan semua 9 suara. Terima kasih atas partisipasi Anda!
            </div>
        <?php elseif ($sisa_suara <= 3): ?>
            <div class="warning-suara">
                ⚠️ Perhatian! Anda hanya punya <?php echo $sisa_suara; ?> suara tersisa
            </div>
        <?php endif; ?>
        
        <!-- LIST KANDIDAT YANG SUDAH DIPILIH -->
        <?php if (!empty($kandidat_dipilih)): ?>
            <div class="list-dipilih">
                <h3>✓ Kandidat yang Sudah Anda Pilih (<?php echo count($kandidat_dipilih); ?>/<?php echo $MAX_SUARA; ?>)</h3>
                <div class="dipilih-items">
                    <?php foreach ($kandidat_dipilih as $id => $nama): ?>
                        <div class="dipilih-item">
                            <span class="dipilih-item-text">✓ <?php echo htmlspecialchars($nama); ?></span>
                            <?php if (!$voting_selesai): ?>
                            <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Batalkan suara untuk ' + '<?php echo addslashes(htmlspecialchars($nama)); ?>' + '?');">
                                <input type="hidden" name="action" value="cancel_vote">
                                <input type="hidden" name="id_kandidat" value="<?php echo $id; ?>">
                                <button type="submit" class="btn-cancel-vote">✕ Batal</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- SUBMIT VOTING SECTION -->
        <?php if ($voting_selesai): ?>
            <div class="submit-section">
                <h3>✓ Voting Anda Sudah Selesai</h3>
                <p>Semua 9 suara Anda telah dikumpulkan dan tidak bisa diubah lagi</p>
            </div>
        <?php elseif (count($kandidat_dipilih) > 0): ?>
            <div class="submit-section">
                <h3>🎯 Finalisasi Voting</h3>
                <p>Anda harus memilih tepat 9 kandidat sebelum bisa mengumpulkan suara</p>
                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="action" value="submit_voting">
                    <button type="submit" class="btn-submit" <?php echo ($jumlah_suara_terisi < $MAX_SUARA) ? 'disabled title="Pilih ' . $sisa_suara . ' kandidat lagi untuk melanjutkan"' : 'title="Klik untuk mengumpulkan semua suara Anda"'; ?>>
                        📝 Kumpulkan Semua Suara (<?php echo $jumlah_suara_terisi; ?>/<?php echo $MAX_SUARA; ?>)
                    </button>
                </form>
            </div>
        <?php endif; ?>
        
        <!-- ALERT MESSAGE -->
        <?php if ($alert): ?>
            <div class="alert alert-<?php echo $alert['type'] === 'error' ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($alert['message']); ?>
            </div>
        <?php endif; ?>
        
        <!-- SECTION TITLE -->
        <?php if ($voting_selesai): ?>
            <h2 class="section-title">Voting Sudah Selesai</h2>
        <?php elseif ($sisa_suara > 0): ?>
            <h2 class="section-title">Pilih Kandidat (<?php echo $sisa_suara; ?> suara tersisa)</h2>
        <?php else: ?>
            <div class="complete-message">
                <h2>✓ Semua Pilihan Sudah Terpenuhi!</h2>
                <p>Silakan kumpulkan suara Anda dengan klik tombol "Kumpulkan Semua Suara" di atas</p>
            </div>
        <?php endif; ?>
        
        <!-- GRID KANDIDAT -->
        <?php if (!$voting_selesai): ?>
        <div class="candidates-grid">
            <?php foreach ($kandidat_list as $kandidat): ?>
                <div class="candidate-card">
                    <div class="candidate-header">
                        <h3><?php echo htmlspecialchars($kandidat['nama_kandidat']); ?></h3>
                    </div>
                    <div class="candidate-body">
                        <?php if (!empty($kandidat['foto'])): ?>
                            <img src="<?php echo SITE_URL; ?>assets/images/kandidat/<?php echo htmlspecialchars($kandidat['foto']); ?>" 
                                 alt="<?php echo htmlspecialchars($kandidat['nama_kandidat']); ?>"
                                 class="candidate-photo">
                        <?php endif; ?>
                        
                        <?php 
                            $is_sudah_dipilih = isset($kandidat_dipilih[$kandidat['id_kandidat']]);
                            $is_suara_habis = $sisa_suara <= 0;
                        ?>
                        
                        <form method="POST" action="" style="margin-top: auto;">
                            <input type="hidden" name="id_kandidat" value="<?php echo $kandidat['id_kandidat']; ?>">
                            <?php if ($is_sudah_dipilih): ?>
                                <button type="button" class="btn-vote" disabled title="Anda sudah memilih kandidat ini">✓ Sudah Dipilih</button>
                            <?php elseif ($is_suara_habis): ?>
                                <button type="button" class="btn-vote" disabled title="Anda sudah menggunakan semua 9 suara">Suara Habis</button>
                            <?php else: ?>
                                <button type="submit" class="btn-vote">🎯 Pilih Kandidat</button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
