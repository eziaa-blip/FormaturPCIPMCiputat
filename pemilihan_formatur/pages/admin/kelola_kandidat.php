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
$kandidat_list = getAllKandidat($conn);

// Handle tambah/edit/hapus
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $nama_kandidat = trim($_POST['nama_kandidat'] ?? '');
        $foto = '';
        
        if (empty($nama_kandidat)) {
            setAlert('Nama kandidat harus diisi!', 'error');
        } else {
            // Handle upload foto
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
                $upload_result = uploadFotoKandidat($_FILES['foto']);
                if ($upload_result['success']) {
                    $foto = $upload_result['filename'];
                } else {
                    setAlert($upload_result['message'], 'error');
                }
            }
            
            // Jika ada error upload, jangan insert
            if (empty($foto) && isset($_FILES['foto']) && $_FILES['foto']['error'] != UPLOAD_ERR_NO_FILE) {
                // Ada error saat upload, pesan error sudah di-set
            } else {
                // Insert kandidat
                $stmt = $conn->prepare("INSERT INTO kandidat (nama_kandidat, foto) VALUES (?, ?)");
                $stmt->bind_param("ss", $nama_kandidat, $foto);
                
                if ($stmt->execute()) {
                    setAlert('Kandidat berhasil ditambahkan!', 'success');
                    header('Location: kelola_kandidat.php');
                    exit();
                } else {
                    setAlert('Gagal menambahkan kandidat!', 'error');
                    // Hapus foto jika insert gagal
                    if (!empty($foto)) {
                        deleteFotoKandidat($foto);
                    }
                }
            }
        }
    } 
    else if ($action === 'delete') {
        $id_kandidat = intval($_POST['id_kandidat'] ?? 0);
        
        if ($id_kandidat > 0) {
            // Get foto sebelum delete
            $stmt = $conn->prepare("SELECT foto FROM kandidat WHERE id_kandidat = ?");
            $stmt->bind_param("i", $id_kandidat);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $foto = $result['foto'] ?? '';
            
            // Delete kandidat
            $stmt = $conn->prepare("DELETE FROM kandidat WHERE id_kandidat = ?");
            $stmt->bind_param("i", $id_kandidat);
            
            if ($stmt->execute()) {
                // Hapus foto
                if (!empty($foto)) {
                    deleteFotoKandidat($foto);
                }
                setAlert('Kandidat berhasil dihapus!', 'success');
                header('Location: kelola_kandidat.php');
                exit();
            } else {
                setAlert('Gagal menghapus kandidat!', 'error');
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
    <title>Kelola Kandidat - E-Voting</title>
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
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .btn-danger {
            background: #dc3545;
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
            margin-top: 20px;
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
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div>
            <h1>E-Voting Pemilihan Formatur</h1>
            <p>Kelola Kandidat</p>
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
            <h2>Tambah Kandidat Baru</h2>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label for="nama_kandidat">Nama Kandidat</label>
                    <input type="text" id="nama_kandidat" name="nama_kandidat" required>
                </div>
                
                <div class="form-group">
                    <label for="foto">Foto Kandidat</label>
                    <input type="file" id="foto" name="foto" accept="image/*">
                    <small style="color: #666; display: block; margin-top: 5px;">Format: JPG, PNG, GIF | Max: 5MB</small>
                </div>
                
                <button type="submit" class="btn">Tambah Kandidat</button>
            </form>
        </div>
        
        <div class="card">
            <h2>Daftar Kandidat</h2>
            <?php if (empty($kandidat_list)): ?>
                <p style="color: #999;">Belum ada kandidat</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Foto</th>
                            <th>Nama Kandidat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; ?>
                        <?php foreach ($kandidat_list as $kandidat): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td>
                                    <?php if (!empty($kandidat['foto'])): ?>
                                        <img src="<?php echo SITE_URL; ?>assets/images/kandidat/<?php echo htmlspecialchars($kandidat['foto']); ?>" 
                                             alt="<?php echo htmlspecialchars($kandidat['nama_kandidat']); ?>" 
                                             style="width: 60px; height: 60px; border-radius: 5px; object-fit: cover;">
                                    <?php else: ?>
                                        <span style="color: #999; font-size: 12px;">Belum ada foto</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($kandidat['nama_kandidat']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id_kandidat" value="<?php echo $kandidat['id_kandidat']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">Hapus</button>
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
