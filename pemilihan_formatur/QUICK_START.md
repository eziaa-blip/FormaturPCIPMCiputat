# INSTALASI & QUICK START

## ✅ Langkah-Langkah Setup

### 1️⃣ Pastikan File Sudah Lengkap
Struktur folder seharusnya:
```
C:\xampp\htdocs\pemilihan_formatur\
├── config/
│   └── database.php
├── includes/
│   └── functions.php
├── pages/
│   ├── admin/
│   │   ├── dashboard.php
│   │   ├── kelola_kandidat.php
│   │   └── kelola_pemilih.php
│   └── pemilih/
│       ├── dashboard.php
│       └── hasil.php
├── login.php
├── logout.php
├── setup.php
├── index.php
├── README.md
├── PANDUAN_PENGGUNA.md
├── DEVELOPER_GUIDE.md
└── DATABASE_SCHEMA.php
```

### 2️⃣ Pastikan XAMPP Berjalan
- Start **Apache**
- Start **MySQL**

### 3️⃣ Jalankan Setup Database
Buka browser dan akses:
```
http://localhost/pemilihan_formatur/setup.php
```

Halaman akan:
- ✓ Membuat database `pemilihan_formatur`
- ✓ Membuat semua tabel
- ✓ Insert data demo (2 kandidat, 3 user)
- ✓ Menampilkan akun untuk login

### 4️⃣ Login & Gunakan Aplikasi

**Dashboard Utama:**
```
http://localhost/pemilihan_formatur/
```

**Login:**
```
http://localhost/pemilihan_formatur/login.php
```

## 🔑 Akun Demo

| Role | Username | Password |
|------|----------|----------|
| Admin | admin | admin123 |
| Pemilih | budi01 | 123456 |
| Pemilih | siti01 | 123456 |

## 🎯 Langkah Testing Pertama

### 1. Cek Home Page
```
http://localhost/pemilihan_formatur/
```
✓ Harus menampilkan statistik voting

### 2. Login sebagai Admin
```
Username: admin
Password: admin123
```
✓ Akan redirect ke: `pages/admin/dashboard.php`

### 3. Lihat Dashboard Admin
- Klik "Kelola Kandidat" - Lihat 2 kandidat demo
- Klik "Kelola Pemilih" - Lihat 3 user demo

### 4. Logout & Login sebagai Pemilih
```
Username: budi01
Password: 123456
```
✓ Akan redirect ke: `pages/pemilih/dashboard.php`

### 5. Lakukan Voting
- Pilih salah satu kandidat
- Klik "Pilih Kandidat Ini"
- Harus melihat pesan: "Suara Anda telah tercatat"

### 6. Lihat Hasil
- Klik "Lihat Hasil Voting"
- Harus melihat grafik dengan 1 suara

## ⚙️ Konfigurasi (Optional)

### Ubah Database Credentials
Edit file: `config/database.php`
```php
$host = 'localhost';      // Ganti host
$db_user = 'root';        // Ganti username
$db_password = '';        // Ganti password
$db_name = 'pemilihan_formatur';
```

### Ubah Warna Theme
Edit di setiap file PHP (di dalam `<style>`):
- `#667eea` - Warna ungu muda
- `#764ba2` - Warna ungu gelap

Ganti dengan warna yang Anda inginkan.

### Ubah Nama Aplikasi
Edit: `config/database.php`
```php
define('SITE_NAME', 'Nama Aplikasi Baru');
```

## 🆘 Troubleshooting

### Error: "Connection failed: ..."
**Solusi:**
1. Pastikan MySQL running
2. Cek username/password di `config/database.php`
3. Pastikan default port MySQL = 3306

### Error: "Table 'pemilihan_formatur.users' doesn't exist"
**Solusi:**
1. Jalankan ulang: `http://localhost/pemilihan_formatur/setup.php`
2. Atau setup manual di phpMyAdmin

### Error: "Call to undefined function..."
**Solusi:**
Pastikan file `includes/functions.php` ada dan tidak ada syntax error

### Login gagal / Password salah
**Solusi:**
1. Pastikan menggunakan akun demo yang benar
2. Cek di phpMyAdmin:
   ```sql
   SELECT * FROM users;
   ```

### Pemilih tidak bisa vote
**Solusi:**
1. Cek apakah sudah login terlebih dahulu
2. Cek status di tabel users:
   ```sql
   SELECT * FROM users WHERE id_user = [id];
   ```
3. Jika `sudah_memilih = 1`, hapus record di tabel voting:
   ```sql
   DELETE FROM voting WHERE id_user = [id];
   UPDATE users SET sudah_memilih = 0 WHERE id_user = [id];
   ```

### Hasil voting tidak update
**Solusi:**
- Refresh browser (F5)
- Cek apakah voting termasuk di database:
  ```sql
  SELECT COUNT(*) FROM voting;
  ```

## 📱 Test di Mobile

Aplikasi ini responsive dan bisa diakses dari mobile:

**Dari device yang sama:**
```
http://localhost/pemilihan_formatur/
```

**Dari device berbeda (misalnya smartphone):**
Ganti `localhost` dengan IP PC:
```
http://[IP_PC]:80/pemilihan_formatur/
```

Cari IP PC dengan:
```powershell
ipconfig
# Cari "IPv4 Address" (biasanya 192.168.x.x)
```

## 🔒 Security Note untuk Production

⚠️ **JANGAN gunakan untuk production tanpa perbaikan:**

1. **Password hashing:**
   ```php
   // Ganti MD5 dengan:
   $hash = password_hash($password, PASSWORD_BCRYPT);
   ```

2. **HTTPS:**
   - Wajib menggunakan HTTPS
   - Ganti `http://` dengan `https://`

3. **Environment variables:**
   - Jangan hard-code credentials
   - Gunakan `.env` file

4. **CSRF protection:**
   - Tambahkan CSRF token di setiap form

5. **Rate limiting:**
   - Proteksi login dari brute force

## 📚 File Dokumentasi

- **README.md** - Dokumentasi lengkap
- **PANDUAN_PENGGUNA.md** - Panduan untuk end-user
- **DEVELOPER_GUIDE.md** - Panduan untuk developer
- **DATABASE_SCHEMA.php** - Struktur database
- **Ini** - Quick start guide

## 🎉 Selesai!

Aplikasi e-voting Anda sudah siap digunakan!

**Akses:** `http://localhost/pemilihan_formatur/`

---

**Pertanyaan?** Lihat file dokumentasi atau cek DEVELOPER_GUIDE.md untuk technical details.
