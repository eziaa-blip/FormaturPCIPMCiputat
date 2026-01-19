# E-Voting Pemilihan Formatur 2026

Website e-voting untuk pemilihan formatur dengan fitur lengkap untuk admin dan pemilih.

## 🚀 Fitur Utama

### Untuk Admin:
- **Dashboard Admin** - Melihat statistik voting real-time
  - Total pemilih
  - Jumlah suara masuk
  - Tingkat partisipasi
  - Grafik hasil voting

- **Kelola Kandidat** - Menambah/menghapus kandidat
  - Tambah visi dan misi
  - Lihat daftar semua kandidat

- **Kelola Pemilih** - Manajemen pemilih
  - Lihat status pemilih (sudah/belum memilih)
  - Hapus pemilih jika diperlukan

### Untuk Pemilih:
- **Dashboard Pemilih** - Interface voting
  - Melihat semua kandidat
  - Pilih kandidat favorit
  - Satu orang hanya bisa memilih sekali

- **Lihat Hasil** - Melihat hasil voting real-time
  - Grafik persentase
  - Jumlah suara per kandidat

### Publik:
- **Home Page** - Halaman utama
  - Statistik voting terkini
  - Hasil voting real-time untuk umum

## 📋 Persyaratan

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Apache dengan mod_rewrite (opsional)

## 💾 Instalasi

1. **Clone/Download project ini ke folder:**
   ```
   C:\xampp\htdocs\pemilihan_formatur
   ```

2. **Akses setup page di browser:**
   ```
   http://localhost/pemilihan_formatur/setup.php
   ```
   Halaman ini akan membuat semua tabel database otomatis.

3. **Atau setup manual di phpMyAdmin:**
   - Buka phpMyAdmin
   - Buat database baru: `pemilihan_formatur`
   - Run semua query dari file `database.sql` (jika ada)

## 🔐 Akun Demo

Setelah setup, gunakan akun berikut untuk login:

### Admin:
- **Username:** `admin`
- **Password:** `admin123`

### Pemilih:
- **Username:** `budi01` atau `siti01`
- **Password:** `123456`

## 📁 Struktur Folder

```
pemilihan_formatur/
├── config/
│   └── database.php          # Konfigurasi database
├── includes/
│   └── functions.php         # Fungsi-fungsi helper
├── pages/
│   ├── admin/
│   │   ├── dashboard.php     # Dashboard admin
│   │   ├── kelola_kandidat.php
│   │   └── kelola_pemilih.php
│   └── pemilih/
│       ├── dashboard.php     # Halaman voting
│       └── hasil.php         # Lihat hasil
├── assets/
│   ├── css/                  # File CSS
│   └── js/                   # File JavaScript
├── index.php                 # Home page
├── login.php                 # Halaman login
├── logout.php                # Proses logout
└── setup.php                 # Setup database
```

## 🔄 Alur Kerja Aplikasi

### Alur Pemilihan:
1. Pemilih login dengan username dan password
2. Sistem cek apakah pemilih sudah memilih
3. Jika belum, tampilkan daftar kandidat
4. Pemilih klik tombol "Pilih Kandidat Ini"
5. Suara tercatat dan flag `sudah_memilih` diubah menjadi 1
6. Hasil voting langsung terupdate

### Alur Admin:
1. Admin login
2. Dashboard menampilkan statistik real-time
3. Admin bisa menambah/menghapus kandidat
4. Admin bisa menghapus pemilih jika diperlukan
5. Admin bisa lihat hasil voting real-time

## 🗄️ Struktur Database

### Tabel `users`:
```sql
- id_user (PK)
- nama
- username (UNIQUE)
- password (MD5)
- role (admin/pemilih)
- sudah_memilih (0/1)
- created_at
```

### Tabel `kandidat`:
```sql
- id_kandidat (PK)
- nama_kandidat
- visi
- misi
- foto (optional)
- created_at
```

### Tabel `voting`:
```sql
- id_voting (PK)
- id_user (FK)
- id_kandidat (FK)
- waktu_memilih
- UNIQUE (id_user) - Satu user hanya bisa vote sekali
```

### Tabel `periode_pemilihan`:
```sql
- id_periode (PK)
- nama_periode
- tanggal_mulai
- tanggal_selesai
- status (aktif/nonaktif)
```

## 🔒 Keamanan

⚠️ **Catatan Penting untuk Production:**
- Password saat ini menggunakan MD5 (TIDAK AMAN untuk production)
- Untuk production, gunakan `password_hash()` dan `password_verify()`
- Tambahkan CSRF protection
- Implementasikan rate limiting
- Gunakan HTTPS
- Sanitasi semua input dari user

### Perbaikan yang disarankan:

1. **Hash Password lebih aman:**
```php
// Ganti MD5 dengan:
$hashed_password = password_hash($password, PASSWORD_BCRYPT);
// Verifikasi dengan:
password_verify($password, $hashed_password);
```

2. **Tambah CSRF Token**
3. **Input Validation lebih ketat**
4. **SQL Injection Protection** (sudah menggunakan prepared statements ✓)

## 🎨 Customize

### Mengubah warna:
Edit warna pada file CSS di dalam file PHP (bagian `<style>`):
- `#667eea` - Warna ungu muda
- `#764ba2` - Warna ungu gelap

### Mengubah nama aplikasi:
Edit di `config/database.php`:
```php
define('SITE_NAME', 'Nama Aplikasi Baru');
```

## 🚨 Troubleshooting

### Error: "Connection failed"
- Pastikan MySQL running
- Cek credentials di `config/database.php`
- Pastikan database `pemilihan_formatur` sudah dibuat

### Error: "Table doesn't exist"
- Jalankan `setup.php` di browser
- Atau run query database manual di phpMyAdmin

### Pemilih tidak bisa vote
- Cek apakah sudah login
- Cek di database tabel `voting` apakah sudah ada record

### Password tidak sesuai saat login
- Reset password di phpMyAdmin atau `setup.php`

## 📞 Kontak & Support

Untuk masalah teknis, periksa:
- Browser console untuk JavaScript error (F12)
- Log PHP di `C:\xampp\apache\logs\error.log`
- Log MySQL di phpMyAdmin

## 📄 Lisensi

Bebas digunakan dan dimodifikasi untuk keperluan pribadi.

---

**Happy Voting! 🗳️**
