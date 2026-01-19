<?php
session_start();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['id_user'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: pages/admin/dashboard.php');
    } else {
        header('Location: pages/pemilih/dashboard.php');
    }
    exit();
}

require_once 'config/database.php';
require_once 'includes/functions.php';

$error_message = '';
$success_message = '';

// KONSTANTA - Default kuota jika tidak ada di database
$DEFAULT_KUOTA = 5;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $asal_sekolah = trim($_POST['asal_sekolah'] ?? '');
    
    // Validasi input
    if (empty($nama) || empty($username) || empty($password) || empty($confirm_password) || empty($asal_sekolah)) {
        $error_message = 'Semua field harus diisi!';
    } elseif (strlen($username) < 4) {
        $error_message = 'Username minimal 4 karakter!';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password minimal 6 karakter!';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Password tidak cocok!';
    } else {
        // Cek apakah username sudah terdaftar
        $stmt = $conn->prepare("SELECT id_user FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = 'Username sudah terdaftar! Gunakan username lain.';
        } else {
            // Cek kuota sekolah dari database
            $stmt = $conn->prepare("SELECT kuota FROM sekolah WHERE nama_sekolah = ?");
            $stmt->bind_param("s", $asal_sekolah);
            $stmt->execute();
            $result = $stmt->get_result();
            $sekolah_data = $result->fetch_assoc();
            $stmt->close();
            $kuota = $sekolah_data ? intval($sekolah_data['kuota']) : $DEFAULT_KUOTA;
            
            // Cek apakah sekolah sudah penuh
            $stmt = $conn->prepare("SELECT COUNT(*) as jumlah FROM users WHERE asal_sekolah = ? AND role = 'pemilih'");
            $stmt->bind_param("s", $asal_sekolah);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            $stmt->close();
            
            if (intval($data['jumlah']) >= $kuota) {
                $error_message = 'Maaf, kuota pendaftar untuk sekolah ' . htmlspecialchars($asal_sekolah) . ' sudah penuh! Maksimal ' . $kuota . ' pemilih untuk sekolah ini.';
            } else {
                // Insert user baru
                $hashed_password = hashPassword($password);
                $role = 'pemilih'; // Default role untuk pendaftar baru
                
                $stmt = $conn->prepare("INSERT INTO users (nama, username, password, asal_sekolah, role) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $nama, $username, $hashed_password, $asal_sekolah, $role);
                
                if ($stmt->execute()) {
                    $success_message = 'Pendaftaran berhasil! Silakan login dengan akun Anda.';
                    // Clear form
                    $nama = '';
                    $username = '';
                    $password = '';
                    $confirm_password = '';
                    $asal_sekolah = '';
                    
                    // Redirect ke login setelah 2 detik
                    header("refresh:2;url=login.php");
                } else {
                    $error_message = 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - E-Voting Pemilihan Formatur</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 20s ease-in-out infinite;
        }
        
        body::after {
            content: '';
            position: absolute;
            bottom: -10%;
            left: -5%;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 15s ease-in-out infinite reverse;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(30px); }
        }
        
        .register-container {
            background: white;
            padding: 50px 45px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 480px;
            position: relative;
            z-index: 1;
            animation: slideUp 0.6s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 35px;
        }
        
        .logo-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .register-container h1 {
            text-align: center;
            color: #333;
            margin-bottom: 8px;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .register-container p {
            text-align: center;
            color: #999;
            margin-bottom: 0;
            font-size: 13px;
            font-weight: 500;
        }
        
        .form-group {
            margin-bottom: 22px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #333;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #f8f9fa;
            font-family: 'Inter', sans-serif;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        
        .form-group input::placeholder,
        .form-group select::placeholder {
            color: #ccc;
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            letter-spacing: 0.3px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .alert {
            padding: 16px;
            margin-bottom: 22px;
            border-radius: 10px;
            font-size: 13px;
            border-left: 4px solid;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
        
        .login-link {
            text-align: center;
            color: #999;
            margin-top: 28px;
            font-size: 13px;
        }
        
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 700;
            transition: color 0.3s ease;
        }
        
        .login-link a:hover {
            color: #764ba2;
        }
        
        .kapasitas-info {
            background: #e3f2fd;
            border: 2px solid #90caf9;
            color: #1565c0;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 12px;
            margin-top: 8px;
            font-weight: 600;
            animation: slideDown 0.3s ease;
        }
        
        .kapasitas-info.penuh {
            background: #fee;
            border-color: #ef5350;
            color: #c62828;
        }
        
        .requirements {
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            padding: 14px;
            border-radius: 10px;
            border-left: 3px solid #667eea;
            font-size: 12px;
            color: #666;
            margin-bottom: 22px;
            line-height: 1.6;
        }
        
        .requirements strong {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <!-- LOGO SECTION -->
        <div class="logo-section">
            <div class="logo-icon">🗳️</div>
            <h1>E-Voting</h1>
            <p>Pemilihan Formatur Cabang Ciputat 2026</p>
        </div>
        
        <!-- REQUIREMENTS BOX -->
        <div class="requirements">
            <strong>📋 Persyaratan:</strong><br>
            • Username minimal 4 karakter<br>
            • Password minimal 6 karakter<br>
            • Kuota berbeda untuk setiap sekolah
        </div>
        
        <!-- ALERTS -->
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error">⚠️ <?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">✓ <?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <!-- REGISTER FORM -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="nama">👤 Nama Lengkap</label>
                <input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap Anda" value="<?php echo htmlspecialchars($nama ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="asal_sekolah">🏫 Asal Sekolah</label>
                <select id="asal_sekolah" name="asal_sekolah" required onchange="updateKapasitas()">
                    <option value="">-- Pilih Sekolah --</option>
                    <?php 
                    $result_sekolah = $conn->query("SELECT * FROM sekolah ORDER BY nama_sekolah ASC");
                    while ($sekolah = $result_sekolah->fetch_assoc()): 
                        $sekolah_kuota = intval($sekolah['kuota']);
                        $stmt_count = $conn->prepare("SELECT COUNT(*) as jumlah FROM users WHERE asal_sekolah = ? AND role = 'pemilih'");
                        $stmt_count->bind_param("s", $sekolah['nama_sekolah']);
                        $stmt_count->execute();
                        $result_count = $stmt_count->get_result();
                        $data_count = $result_count->fetch_assoc();
                        $jumlah_user = intval($data_count['jumlah']);
                        $stmt_count->close();
                        
                        $sisa_kuota = $sekolah_kuota - $jumlah_user;
                        $is_disabled = $sisa_kuota <= 0;
                    ?>
                        <option value="<?php echo htmlspecialchars($sekolah['nama_sekolah']); ?>"
                                data-jumlah="<?php echo $jumlah_user; ?>"
                                data-sisa="<?php echo $sisa_kuota; ?>"
                                <?php echo $is_disabled ? 'disabled' : ''; ?>
                                <?php echo isset($asal_sekolah) && $asal_sekolah === $sekolah['nama_sekolah'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($sekolah['nama_sekolah']); ?> (<?php echo $jumlah_user; ?>/<?php echo $sekolah_kuota; ?>)<?php if ($is_disabled): ?> PENUH<?php endif; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <div id="kapasitas-info"></div>
            </div>
            
            <div class="form-group">
                <label for="username">🔐 Username</label>
                <input type="text" id="username" name="username" placeholder="Minimal 4 karakter" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">🔒 Password</label>
                <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">🔒 Konfirmasi Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password Anda" required>
            </div>
            
            <button type="submit" class="btn">📝 Daftar Sekarang</button>
        </form>
        
        <div class="login-link">
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </div>
    </div>
    
    <script>
        function updateKapasitas() {
            const select = document.getElementById('asal_sekolah');
            const infoDiv = document.getElementById('kapasitas-info');
            const selectedOption = select.options[select.selectedIndex];
            
            if (selectedOption.value === '') {
                infoDiv.innerHTML = '';
                return;
            }
            
            const sisa = parseInt(selectedOption.getAttribute('data-sisa'));
            const nama = selectedOption.text.split('(')[0].trim();
            
            if (sisa <= 0) {
                infoDiv.innerHTML = '<div class="kapasitas-info penuh">✗ Maaf, kuota untuk ' + nama + ' sudah penuh</div>';
            } else if (sisa <= 2) {
                infoDiv.innerHTML = '<div class="kapasitas-info">⚠️ Hanya tersisa ' + sisa + ' kuota untuk ' + nama + '</div>';
            } else {
                infoDiv.innerHTML = '<div class="kapasitas-info">✓ Tersedia ' + sisa + ' kuota untuk ' + nama + '</div>';
            }
        }
        
        window.addEventListener('load', function() {
            updateKapasitas();
        });
    </script>
</body>
</html>
