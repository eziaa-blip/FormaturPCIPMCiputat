<?php
// =========================================================
// Helper Functions
// =========================================================

/**
 * Hash password dengan MD5 (untuk development, gunakan bcrypt di production)
 */
function hashPassword($password) {
    return md5($password);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return md5($password) === $hash;
}

/**
 * Check apakah user sudah login
 */
function isLoggedIn() {
    return isset($_SESSION['id_user']);
}

/**
 * Check role user
 */
function getUserRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Redirect ke halaman
 */
function redirect($url) {
    header('Location: ' . SITE_URL . $url);
    exit();
}

/**
 * Get detail user
 */
function getUserDetail($conn, $id_user) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id_user = ?");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Check apakah user sudah memilih
 */
function sudahMemilih($conn, $id_user) {
    $stmt = $conn->prepare("SELECT COUNT(*) as jumlah FROM voting WHERE id_user = ?");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['jumlah'] > 0;
}

/**
 * Get semua kandidat
 */
function getAllKandidat($conn) {
    $result = $conn->query("SELECT * FROM kandidat ORDER BY id_kandidat ASC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get detail kandidat
 */
function getKandidatDetail($conn, $id_kandidat) {
    $stmt = $conn->prepare("SELECT * FROM kandidat WHERE id_kandidat = ?");
    $stmt->bind_param("i", $id_kandidat);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Get hasil voting (rekapitulasi suara)
 */
function getHasilVoting($conn) {
    $query = "
        SELECT kandidat.id_kandidat, kandidat.nama_kandidat, kandidat.foto, 
               COUNT(voting.id_voting) AS jumlah_suara
        FROM kandidat
        LEFT JOIN voting ON kandidat.id_kandidat = voting.id_kandidat
        GROUP BY kandidat.id_kandidat
        ORDER BY jumlah_suara DESC
    ";
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get total pemilih
 */
function getTotalPemilih($conn) {
    $result = $conn->query("SELECT COUNT(*) as jumlah FROM users WHERE role = 'pemilih'");
    $row = $result->fetch_assoc();
    return $row['jumlah'];
}

/**
 * Get total suara terisi
 */
function getTotalSuaraIsi($conn) {
    $result = $conn->query("SELECT COUNT(*) as jumlah FROM voting");
    $row = $result->fetch_assoc();
    return $row['jumlah'];
}

/**
 * Format tanggal Indonesia
 */
function formatTanggalIndonesia($tanggal) {
    $bulan = array(
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    );
    
    $split = explode('-', $tanggal);
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

/**
 * Alert message
 */
function setAlert($message, $type = 'success') {
    $_SESSION['alert'] = array(
        'message' => $message,
        'type' => $type
    );
}

/**
 * Get dan hapus alert
 */
function getAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        return $alert;
    }
    return null;
}

/**
 * Upload foto kandidat
 */
function uploadFotoKandidat($file) {
    // Validasi file ada
    if (!isset($file) || $file['error'] != UPLOAD_ERR_OK) {
        return array('success' => false, 'message' => 'File tidak valid');
    }
    
    // Validasi tipe file (hanya gambar)
    $allowed_types = array('image/jpeg', 'image/png', 'image/gif');
    if (!in_array($file['type'], $allowed_types)) {
        return array('success' => false, 'message' => 'Hanya file gambar yang diperbolehkan (JPG, PNG, GIF)');
    }
    
    // Validasi ukuran file (max 5MB)
    $max_size = 5 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        return array('success' => false, 'message' => 'Ukuran file terlalu besar (max 5MB)');
    }
    
    // Generate nama file unik
    $upload_dir = dirname(__DIR__) . '/assets/images/kandidat/';
    $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'kandidat_' . time() . '_' . mt_rand(1000, 9999) . '.' . $file_ext;
    $filepath = $upload_dir . $filename;
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return array('success' => true, 'filename' => $filename);
    } else {
        return array('success' => false, 'message' => 'Gagal mengupload file');
    }
}

/**
 * Hapus foto kandidat
 */
function deleteFotoKandidat($filename) {
    if (empty($filename)) {
        return true;
    }
    
    $filepath = dirname(__DIR__) . '/assets/images/kandidat/' . $filename;
    if (file_exists($filepath)) {
        unlink($filepath);
    }
    return true;
}

/**
 * Get URL foto kandidat
 */
function getFotoKandidatUrl($filename) {
    if (empty($filename)) {
        return SITE_URL . 'assets/images/placeholder.png';
    }
    return SITE_URL . 'assets/images/kandidat/' . $filename;
}

?>
