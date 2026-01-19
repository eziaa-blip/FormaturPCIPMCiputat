# Panduan Developer

## 📚 Struktur Kode

### 1. Config Database (`config/database.php`)
Konfigurasi koneksi ke database MySQL:
```php
$host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'pemilihan_formatur';
```

Untuk production, ganti dengan:
```php
$host = 'prod-host';
$db_user = 'prod-user';
$db_password = 'prod-password';
$db_name = 'pemilihan_formatur';
```

### 2. Helper Functions (`includes/functions.php`)
Berisi fungsi-fungsi yang reusable:

**Authentikasi:**
- `hashPassword($password)` - Hash password
- `verifyPassword($password, $hash)` - Verifikasi password
- `isLoggedIn()` - Cek user sudah login
- `getUserRole()` - Get role user

**Data:**
- `getAllKandidat($conn)` - Get semua kandidat
- `getKandidatDetail($conn, $id)` - Get detail kandidat
- `getHasilVoting($conn)` - Get hasil voting
- `getTotalPemilih($conn)` - Get total pemilih
- `getTotalSuaraIsi($conn)` - Get total suara
- `sudahMemilih($conn, $id_user)` - Cek user sudah memilih

**Utility:**
- `redirect($url)` - Redirect ke halaman
- `setAlert($message, $type)` - Set alert message
- `getAlert()` - Get dan hapus alert
- `formatTanggalIndonesia($tanggal)` - Format tanggal

## 🔄 Alur Request

### Login Flow:
```
login.php (GET)
    ↓
form dengan username & password
    ↓
login.php (POST)
    ↓
Query: SELECT * FROM users WHERE username=? AND password=?
    ↓
Jika found:
  - Set $_SESSION
  - Redirect ke dashboard sesuai role
Jika not found:
  - Tampilkan error message
```

### Voting Flow:
```
pemilih/dashboard.php (GET)
    ↓
Check: sudahMemilih($conn, $id_user)
    ↓
Jika belum:
  - Tampilkan semua kandidat
Jika sudah:
  - Tampilkan status "Sudah Memilih"
    ↓
User klik "Pilih Kandidat"
    ↓
pemilih/dashboard.php (POST dengan id_kandidat)
    ↓
INSERT INTO voting (id_user, id_kandidat)
UPDATE users SET sudah_memilih=1
    ↓
Tampilkan success message
```

## 🔐 Security Considerations

### Current Implementation:
✓ Prepared Statements - Melindungi dari SQL Injection
✓ htmlspecialchars() - Melindungi dari XSS
✓ Session handling - User authentication

### Yang perlu diperbaiki:
⚠️ Password hashing - Ganti MD5 dengan bcrypt
⚠️ CSRF protection - Tambahkan CSRF token
⚠️ Rate limiting - Proteksi dari brute force
⚠️ Input validation - Validasi lebih ketat
⚠️ HTTPS - Mandatory untuk production

### Improvement Code Examples:

**1. Ganti MD5 dengan Bcrypt:**
```php
// Hash saat register/ubah password
$hash = password_hash($password, PASSWORD_BCRYPT);

// Verifikasi saat login
if (password_verify($password, $hash)) {
    // Password benar
}
```

**2. CSRF Protection:**
```php
// Generate token
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Dalam form
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

// Verifikasi
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF token validation failed');
}
```

**3. Input Validation:**
```php
$nama = filter_input(INPUT_POST, 'nama', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
```

## 🗄️ Database Queries

### Query Hasil Voting:
```sql
SELECT kandidat.nama_kandidat, 
       COUNT(voting.id_voting) AS jumlah_suara,
       ROUND((COUNT(voting.id_voting) / 
       (SELECT COUNT(*) FROM voting)) * 100, 2) AS persentase
FROM kandidat
LEFT JOIN voting ON kandidat.id_kandidat = voting.id_kandidat
GROUP BY kandidat.id_kandidat
ORDER BY jumlah_suara DESC;
```

### Query Statistik:
```sql
-- Total pemilih
SELECT COUNT(*) FROM users WHERE role = 'pemilih';

-- Total suara masuk
SELECT COUNT(*) FROM voting;

-- Tingkat partisipasi
SELECT ROUND((SELECT COUNT(*) FROM voting) / 
             (SELECT COUNT(*) FROM users WHERE role = 'pemilih') * 100, 2);

-- Pemilih belum memilih
SELECT * FROM users WHERE role = 'pemilih' AND sudah_memilih = 0;
```

## 📁 Menambah Fitur Baru

### Contoh: Tambah halaman export hasil CSV

1. Buat file: `pages/admin/export_hasil.php`
```php
<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if (!isLoggedIn() || getUserRole() !== 'admin') {
    exit('Unauthorized');
}

$hasilVoting = getHasilVoting($conn);

// Set header untuk download CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="hasil_voting.csv"');

// Output
$output = fopen('php://output', 'w');
fputcsv($output, ['No Urut', 'Nama Kandidat', 'Jumlah Suara']);

$no = 1;
foreach ($hasilVoting as $row) {
    fputcsv($output, [$no++, $row['nama_kandidat'], $row['jumlah_suara']]);
}

fclose($output);
?>
```

2. Tambah link di admin dashboard:
```html
<a href="export_hasil.php">Export CSV</a>
```

## 🧪 Testing

### Manual Testing Checklist:
- [ ] Login admin berfungsi
- [ ] Login pemilih berfungsi
- [ ] Tambah kandidat berfungsi
- [ ] Voting berfungsi
- [ ] Hasil voting update real-time
- [ ] Pemilih tidak bisa vote 2x
- [ ] Logout berfungsi
- [ ] Responsive di mobile

### Database Testing:
```sql
-- Cek voting terakhir
SELECT * FROM voting ORDER BY id_voting DESC LIMIT 5;

-- Cek integritas data
SELECT v.id_user, u.nama, v.id_kandidat, k.nama_kandidat
FROM voting v
JOIN users u ON v.id_user = u.id_user
JOIN kandidat k ON v.id_kandidat = k.id_kandidat;
```

## 📝 Changelog

### v1.0 (Initial Release)
- Fitur login admin & pemilih
- Kelola kandidat
- Voting system
- Hasil voting real-time
- Dashboard admin
- Halaman home public

---

Happy Coding! 🚀
