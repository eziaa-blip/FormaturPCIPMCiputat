<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check apakah user sudah login dan role admin
if (!isLoggedIn() || getUserRole() !== 'admin') {
    header('Location: ../../login.php');
    exit();
}

$error_message = '';
$success_message = '';
$action = $_GET['action'] ?? '';
$id_sekolah = $_GET['id'] ?? '';

// PROSES TAMBAH SEKOLAH
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'add') {
        $nama_sekolah = trim($_POST['nama_sekolah'] ?? '');
        $kuota = intval($_POST['kuota'] ?? 5);
        
        if (empty($nama_sekolah)) {
            $error_message = 'Nama sekolah tidak boleh kosong!';
        } elseif ($kuota <= 0) {
            $error_message = 'Kuota harus lebih dari 0!';
        } else {
            $stmt = $conn->prepare("INSERT INTO sekolah (nama_sekolah, kuota) VALUES (?, ?)");
            $stmt->bind_param("si", $nama_sekolah, $kuota);
            
            if ($stmt->execute()) {
                $success_message = 'Sekolah berhasil ditambahkan!';
            } else {
                if (strpos($stmt->error, 'Duplicate entry') !== false) {
                    $error_message = 'Nama sekolah sudah terdaftar!';
                } else {
                    $error_message = 'Error: ' . $stmt->error;
                }
            }
            $stmt->close();
        }
    }
    
    // PROSES EDIT SEKOLAH
    elseif ($_POST['action'] === 'edit') {
        $id_sekolah = $_POST['id_sekolah'] ?? '';
        $nama_sekolah = trim($_POST['nama_sekolah'] ?? '');
        $kuota = intval($_POST['kuota'] ?? 5);
        
        if (empty($nama_sekolah)) {
            $error_message = 'Nama sekolah tidak boleh kosong!';
        } elseif ($kuota <= 0) {
            $error_message = 'Kuota harus lebih dari 0!';
        } else {
            $stmt = $conn->prepare("UPDATE sekolah SET nama_sekolah = ?, kuota = ? WHERE id_sekolah = ?");
            $stmt->bind_param("sii", $nama_sekolah, $kuota, $id_sekolah);
            
            if ($stmt->execute()) {
                $success_message = 'Sekolah berhasil diperbarui!';
            } else {
                if (strpos($stmt->error, 'Duplicate entry') !== false) {
                    $error_message = 'Nama sekolah sudah terdaftar!';
                } else {
                    $error_message = 'Error: ' . $stmt->error;
                }
            }
            $stmt->close();
        }
    }
    
    // PROSES HAPUS SEKOLAH
    elseif ($_POST['action'] === 'delete') {
        $id_sekolah = $_POST['id_sekolah'] ?? '';
        
        // Cek apakah ada pemilih yang menggunakan sekolah ini
        $stmt = $conn->prepare("SELECT COUNT(*) as jumlah FROM users WHERE asal_sekolah = ?");
        $stmt->bind_param("s", $id_sekolah);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        
        if ($data['jumlah'] > 0) {
            $error_message = 'Tidak bisa menghapus sekolah karena masih ada pemilih yang menggunakannya!';
        } else {
            $stmt = $conn->prepare("DELETE FROM sekolah WHERE id_sekolah = ?");
            $stmt->bind_param("i", $id_sekolah);
            
            if ($stmt->execute()) {
                $success_message = 'Sekolah berhasil dihapus!';
            } else {
                $error_message = 'Error: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// GET DATA SEKOLAH UNTUK EDIT
$edit_sekolah = null;
if ($action === 'edit' && !empty($id_sekolah)) {
    $stmt = $conn->prepare("SELECT * FROM sekolah WHERE id_sekolah = ?");
    $stmt->bind_param("i", $id_sekolah);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_sekolah = $result->fetch_assoc();
    $stmt->close();
}

// GET SEMUA SEKOLAH
$result_sekolah = $conn->query("SELECT * FROM sekolah ORDER BY nama_sekolah ASC");
$sekolah_list = [];
while ($row = $result_sekolah->fetch_assoc()) {
    $sekolah_list[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Sekolah - E-Voting</title>
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
            transition: background 0.3s;
        }
        
        .btn-logout:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .form-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .form-card h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #999;
        }
        
        .btn-secondary:hover {
            background: #777;
        }
        
        .alert {
            padding: 15px;
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
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        table thead {
            background: #667eea;
            color: white;
        }
        
        table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        table tbody tr:hover {
            background-color: #f5f5f5;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn-edit, .btn-delete {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: opacity 0.2s;
        }
        
        .btn-edit {
            background-color: #ffc107;
            color: #333;
        }
        
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-edit:hover, .btn-delete:hover {
            opacity: 0.8;
        }
        
        .empty-message {
            text-align: center;
            padding: 40px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div>
            <h1>E-Voting Pemilihan Formatur</h1>
            <p>Kelola Sekolah</p>
        </div>
        <div class="navbar-right">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['nama']); ?></span>
            <a href="../../logout.php" class="btn-logout">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <a href="dashboard.php" class="back-link">← Kembali ke Dashboard</a>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <!-- FORM TAMBAH/EDIT SEKOLAH -->
        <div class="form-card">
            <h2><?php echo $action === 'edit' ? 'Edit Sekolah' : 'Tambah Sekolah Baru'; ?></h2>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="<?php echo $action === 'edit' ? 'edit' : 'add'; ?>">
                <?php if ($action === 'edit' && $edit_sekolah): ?>
                    <input type="hidden" name="id_sekolah" value="<?php echo htmlspecialchars($edit_sekolah['id_sekolah']); ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="nama_sekolah">Nama Sekolah</label>
                    <input type="text" id="nama_sekolah" name="nama_sekolah" 
                           value="<?php echo $action === 'edit' && $edit_sekolah ? htmlspecialchars($edit_sekolah['nama_sekolah']) : ''; ?>" 
                           required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="kuota">Kuota Pendaftar</label>
                    <input type="number" id="kuota" name="kuota" 
                           value="<?php echo $action === 'edit' && $edit_sekolah ? intval($edit_sekolah['kuota']) : 5; ?>" 
                           min="1" required>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="btn">
                        <?php echo $action === 'edit' ? 'Simpan Perubahan' : 'Tambah Sekolah'; ?>
                    </button>
                    <?php if ($action === 'edit'): ?>
                        <a href="kelola_sekolah.php" class="btn btn-secondary" style="text-decoration: none;">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <!-- DAFTAR SEKOLAH -->
        <div class="form-card">
            <h2>Daftar Sekolah</h2>
            
            <?php if (empty($sekolah_list)): ?>
                <div class="empty-message">
                    <p>Belum ada data sekolah</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Sekolah</th>
                            <th>Kuota</th>
                            <th>Tanggal Ditambahkan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($sekolah_list as $sekolah): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($sekolah['nama_sekolah']); ?></td>
                                <td><?php echo intval($sekolah['kuota']); ?> pemilih</td>
                                <td><?php echo formatTanggalIndonesia($sekolah['created_at']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?action=edit&id=<?php echo $sekolah['id_sekolah']; ?>" class="btn-edit">Edit</a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus sekolah ini?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id_sekolah" value="<?php echo $sekolah['id_sekolah']; ?>">
                                            <button type="submit" class="btn-delete">Hapus</button>
                                        </form>
                                    </div>
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
