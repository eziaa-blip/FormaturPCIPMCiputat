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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        $error_message = 'Username dan password harus diisi!';
    } else {
        // Query untuk cek login
        $stmt = $conn->prepare("SELECT id_user, nama, username, role FROM users WHERE username = ? AND password = ?");
        $hashed_password = hashPassword($password);
        $stmt->bind_param("ss", $username, $hashed_password);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Set session
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect ke dashboard sesuai role
            if ($user['role'] === 'admin') {
                header('Location: pages/admin/dashboard.php');
            } else {
                header('Location: pages/pemilih/dashboard.php');
            }
            exit();
        } else {
            $error_message = 'Username atau password salah!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Voting Pemilihan Formatur</title>
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
            position: relative;
            overflow: hidden;
        }
        
        /* Animated background elements */
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
        
        .login-container {
            background: white;
            padding: 50px 45px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 420px;
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
            margin-bottom: 40px;
        }
        
        .logo-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .login-container h1 {
            text-align: center;
            color: #333;
            margin-bottom: 8px;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .login-container p {
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
        
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #f8f9fa;
            font-family: 'Inter', sans-serif;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        
        .form-group input::placeholder {
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
        
        .footer-text {
            text-align: center;
            color: #999;
            margin-top: 28px;
            font-size: 13px;
        }
        
        .footer-text a {
            color: #667eea;
            text-decoration: none;
            font-weight: 700;
            transition: color 0.3s ease;
        }
        
        .footer-text a:hover {
            color: #764ba2;
        }
        
        .divider {
            height: 1px;
            background: #e9ecef;
            margin: 28px 0;
        }
        
        .info-box {
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            padding: 14px;
            border-radius: 10px;
            border-left: 3px solid #667eea;
            font-size: 12px;
            color: #666;
            margin-bottom: 22px;
        }
        
        .info-box strong {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- LOGO SECTION -->
        <div class="logo-section">
            <div class="logo-icon">🗳️</div>
            <h1>E-Voting</h1>
            <p>Pemilihan Formatur Cabang Ciputat 2026</p>
        </div>
        
        <!-- ALERTS -->
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error">⚠️ <?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">✓ <?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <!-- LOGIN FORM -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">👤 Username</label>
                <input type="text" id="username" name="username" placeholder="Masukkan username Anda" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">🔒 Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password Anda" required>
            </div>
            
            <button type="submit" class="btn">🔓 Login</button>
        </form>
        
        <div class="divider"></div>
        
        <!-- FOOTER TEXT -->
        <div class="footer-text">
            Belum punya akun? <a href="register.php">Daftar di sini</a>
        </div>
    </div>
</body>
</html>
