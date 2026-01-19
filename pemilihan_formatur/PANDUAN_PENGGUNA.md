# Panduan Penggunaan E-Voting

## 🎯 Memulai

### 1. Setup Database
Pertama kali, akses: `http://localhost/pemilihan_formatur/setup.php`

Halaman ini akan:
- Membuat semua tabel database
- Insert data demo
- Tampilkan akun untuk login

### 2. Halaman Utama
Akses: `http://localhost/pemilihan_formatur/`

Halaman ini menampilkan:
- Statistik voting terkini
- Hasil voting real-time

## 👨‍💼 Untuk Admin

### Login Admin
```
URL: http://localhost/pemilihan_formatur/login.php
Username: admin
Password: admin123
```

### Dashboard Admin
Setelah login, Anda akan melihat:
- Total pemilih terdaftar
- Jumlah suara masuk
- Tingkat partisipasi
- Grafik hasil voting

### Kelola Kandidat
1. Klik menu "Kelola Kandidat"
2. Untuk tambah:
   - Masukkan nama kandidat
   - Isi visi dan misi
   - Klik "Tambah Kandidat"
3. Untuk hapus:
   - Klik tombol "Hapus" di daftar
   - Konfirmasi

### Kelola Pemilih
1. Klik menu "Kelola Pemilih"
2. Lihat daftar semua pemilih
3. Lihat status "Sudah Memilih" atau "Belum Memilih"
4. Untuk hapus pemilih, klik "Hapus"

## 🗳️ Untuk Pemilih

### Login Pemilih
```
URL: http://localhost/pemilihan_formatur/login.php
Username: budi01 atau siti01
Password: 123456
```

### Memilih Kandidat
1. Setelah login, lihat daftar kandidat
2. Baca visi dan misi setiap kandidat
3. Klik "Pilih Kandidat Ini" pada kandidat pilihan
4. Sistem akan mencatat suara Anda
5. Anda akan melihat pesan "Terima kasih! Suara Anda telah tercatat"
6. **PENTING:** Anda hanya bisa memilih satu kali

### Lihat Hasil Voting
1. Dari dashboard, klik "Lihat Hasil Voting"
2. Atau akses langsung: `http://localhost/pemilihan_formatur/pages/pemilih/hasil.php`
3. Lihat:
   - Persentase suara per kandidat
   - Jumlah suara
   - Total suara masuk

## 🔐 Logout
Klik tombol "Logout" di sudut kanan atas untuk keluar dari sistem

---

## ⚙️ Informasi Teknis

### Database Credentials
```
Host: localhost
User: root
Password: (kosong)
Database: pemilihan_formatur
```

### File Penting
- `login.php` - Halaman login
- `pages/admin/dashboard.php` - Dashboard admin
- `pages/pemilih/dashboard.php` - Halaman voting
- `config/database.php` - Konfigurasi database

### Query Hasil Voting
Untuk melihat hasil voting di phpMyAdmin:
```sql
SELECT kandidat.nama_kandidat, COUNT(voting.id_voting) AS jumlah_suara
FROM kandidat
LEFT JOIN voting ON kandidat.id_kandidat = voting.id_kandidat
GROUP BY kandidat.id_kandidat
ORDER BY jumlah_suara DESC;
```

---

**Selamat menggunakan! 🎉**
