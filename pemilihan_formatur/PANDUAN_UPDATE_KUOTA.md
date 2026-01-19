## Update Kuota Sekolah - Panduan Implementasi

### ✓ Perubahan yang Telah Dilakukan

1. **Database Schema Update**
   - Menambahkan kolom `kuota` ke tabel `sekolah` dengan default nilai 5
   - File konfigurasi di `setup.php` dan `DATABASE_SCHEMA.php` telah diperbarui

2. **Register Page (register.php)**
   - Mengubah sistem dari hardcoded `$MAX_USER_PER_SEKOLAH = 5` menjadi per-school quota
   - Dropdown sekolah sekarang menampilkan kuota spesifik masing-masing sekolah
   - Validasi pendaftaran menggunakan kuota dari database

3. **Admin Panel - Kelola Sekolah (pages/admin/kelola_sekolah.php)**
   - Menambahkan input field "Kuota Pendaftar" di form tambah/edit sekolah
   - Menampilkan kolom kuota di daftar sekolah
   - Admin dapat mengubah kuota sekolah melalui menu edit

### 📝 Langkah-Langkah Untuk Menggunakan

#### Opsi 1: Melalui Script (Rekomendasi)

1. **Jalankan script update database:**
   - Buka browser: `http://localhost/pemilihan_formatur/add_kuota_column.php`
   - Script akan menambahkan kolom kuota ke database

2. **Set kuota PIMPINAN CABANG CIPUTAT ke 39:**
   - Buka browser: `http://localhost/pemilihan_formatur/set_kuota.php`
   - Kuota akan otomatis diubah menjadi 39

#### Opsi 2: Melalui Admin Panel

1. Login sebagai admin
2. Pilih menu "Kelola Sekolah"
3. Cari "PIMPINAN CABANG CIPUTAT"
4. Klik "Edit"
5. Ubah "Kuota Pendaftar" dari 5 menjadi 39
6. Simpan perubahan

#### Opsi 3: Langsung via Database (SQL)

```sql
-- Tambahkan kolom kuota jika belum ada
ALTER TABLE sekolah ADD COLUMN kuota INT DEFAULT 5;

-- Set kuota PIMPINAN CABANG CIPUTAT ke 39
UPDATE sekolah SET kuota = 39 WHERE nama_sekolah = 'PIMPINAN CABANG CIPUTAT';
```

### ✨ Fitur yang Tersedia

- ✓ Setiap sekolah dapat memiliki kuota pendaftar yang berbeda
- ✓ Admin dapat mengubah kuota kapan saja
- ✓ Sistem validasi otomatis saat pendaftaran
- ✓ Dropdown di register page menampilkan sisa kuota
- ✓ Pesan error jelas saat kuota penuh

### 📋 File yang Dimodifikasi

- `register.php` - Update logika kuota
- `setup.php` - Update schema table sekolah
- `DATABASE_SCHEMA.php` - Update schema table sekolah
- `pages/admin/kelola_sekolah.php` - Update form dan tabel untuk manajemen kuota

### 📁 File Baru yang Dibuat

- `add_kuota_column.php` - Script untuk menambah kolom kuota
- `set_kuota.php` - Script untuk set kuota PIMPINAN CABANG CIPUTAT ke 39

---

**Catatan:** Setelah menjalankan script update, Anda dapat menghapus file `add_kuota_column.php` dan `set_kuota.php` jika sudah tidak digunakan.
