<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check apakah user sudah login dan role admin
if (!isLoggedIn() || getUserRole() !== 'admin') {
    header('Location: ../../login.php');
    exit();
}

$message = getAlert();

// Get semua pemilih
$pemilih_list = $conn->query("SELECT * FROM users WHERE role = 'pemilih' ORDER BY id_user ASC")->fetch_all(MYSQLI_ASSOC);

// Handle hapus pemilih
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete') {
        $id_user = intval($_POST['id_user'] ?? 0);
        
        if ($id_user > 0) {
            $stmt = $conn->prepare("DELETE FROM users WHERE id_user = ? AND role = 'pemilih'");
            $stmt->bind_param("i", $id_user);
            
            if ($stmt->execute()) {
                setAlert('Pemilih berhasil dihapus!', 'success');
                header('Location: kelola_pemilih.php');
                exit();
            } else {
                setAlert('Gagal menghapus pemilih!', 'error');
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
    <title>Kelola Pemilih - E-Voting</title>
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
        
        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .card h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 20px;
        }
        
        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th,
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        table tr:hover {
            background-color: #f8f9fa;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .btn {
            background: #dc3545;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div>
            <h1>E-Voting Pemilihan Formatur</h1>
            <p>Kelola Pemilih</p>
        </div>
        <div class="navbar-right">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['nama']); ?></span>
            <a href="../../logout.php" class="btn-logout">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <a href="dashboard.php" class="back-link">← Kembali ke Dashboard</a>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message['type'] === 'error' ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message['message']); ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Daftar Pemilih</h2>
            <?php if (empty($pemilih_list)): ?>
                <p style="color: #999;">Belum ada pemilih terdaftar</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Status Memilih</th>
                            <th>Tanggal Daftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; ?>
                        <?php foreach ($pemilih_list as $pemilih): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($pemilih['nama']); ?></td>
                                <td><?php echo htmlspecialchars($pemilih['username']); ?></td>
                                <td>
                                    <?php if ($pemilih['sudah_memilih']): ?>
                                        <span class="badge badge-success">Sudah Memilih</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Belum Memilih</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo formatTanggalIndonesia(substr($pemilih['created_at'], 0, 10)); ?></td>
                                <td>
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id_user" value="<?php echo $pemilih['id_user']; ?>">
                                        <button type="submit" class="btn" onclick="return confirm('Yakin ingin menghapus?')">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
