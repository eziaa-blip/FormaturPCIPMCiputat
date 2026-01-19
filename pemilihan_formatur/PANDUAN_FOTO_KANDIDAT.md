# 📸 Fitur Upload Foto Kandidat

Dokumentasi fitur upload dan tampilan foto kandidat untuk e-voting.

## ✨ Apa yang Ditambahkan?

### 1. **Upload Foto di Admin Panel**
- Admin bisa upload foto kandidat saat menambah kandidat baru
- Support format: JPG, PNG, GIF
- Max size: 5MB
- Foto otomatis disimpan di folder: `assets/images/kandidat/`

### 2. **Tampilan Foto di Halaman Pemilih**
- Setiap kandidat menampilkan foto mereka
- Layout responsif dan menarik
- Jika tidak ada foto, tampil placeholder

### 3. **Tampilan Foto di Halaman Hasil**
- Foto kandidat ditampilkan di samping hasil voting
- Bisa langsung lihat siapa pemenang dengan fotonya

## 🛠️ Teknologi yang Digunakan

- **File Upload:** PHP `move_uploaded_file()`
- **Validasi:** Tipe file, ukuran file
- **Storage:** Folder `assets/images/kandidat/`
- **Keamanan:** Unique filename dengan timestamp

## 📋 Cara Menggunakan

### **Untuk Admin - Upload Foto Kandidat:**

1. **Login:** http://localhost/pemilihan_formatur/login.php
   - Username: `admin`
   - Password: `admin123`

2. **Klik:** Menu "Kelola Kandidat"

3. **Form Tambah Kandidat:**
   - Nama Kandidat: (Isi nama)
   - Visi: (Isi visi)
   - Misi: (Isi misi)
   - **Foto Kandidat:** Pilih file gambar
   - Klik: "Tambah Kandidat"

4. **Verifikasi:**
   - Foto akan muncul di tabel "Daftar Kandidat"
   - Lihat thumbnail 60x60px di kolom Foto

### **Untuk Pemilih - Lihat Foto Kandidat:**

1. **Login:** http://localhost/pemilihan_formatur/login.php
   - Username: `budi01`
   - Password: `123456`

2. **Dashboard Pemilih:**
   - Lihat setiap kartu kandidat dengan foto mereka
   - Foto berukuran 250x250px dengan rounded corners
   - Klik "Pilih Kandidat Ini" untuk vote

3. **Lihat Hasil:**
   - Klik "Lihat Hasil Voting"
   - Setiap kandidat ditampilkan dengan foto + hasil voting

## 📁 Struktur File

```
pemilihan_formatur/
├── assets/
│   └── images/
│       ├── placeholder.svg          ← Default image jika tidak ada foto
│       └── kandidat/                ← Folder untuk foto kandidat
│           ├── kandidat_1705431234_5678.jpg
│           ├── kandidat_1705431567_1234.png
│           └── ...
│
├── includes/
│   └── functions.php                ← Fungsi upload/delete foto
│
├── pages/
│   ├── admin/
│   │   └── kelola_kandidat.php      ← Upload foto di sini
│   └── pemilih/
│       ├── dashboard.php            ← Tampil foto saat voting
│       └── hasil.php                ← Tampil foto di hasil
```

## 🔧 Fungsi-Fungsi Baru

### **uploadFotoKandidat($file)**
```php
$result = uploadFotoKandidat($_FILES['foto']);
if ($result['success']) {
    $filename = $result['filename'];
} else {
    $error = $result['message'];
}
```
- Return: `['success' => bool, 'filename' => string, 'message' => string]`
- Validasi: Tipe file, ukuran (max 5MB)
- Generate: Unique filename dengan timestamp

### **deleteFotoKandidat($filename)**
```php
deleteFotoKandidat('kandidat_1705431234_5678.jpg');
```
- Hapus file foto jika ada
- Dipanggil otomatis saat delete kandidat
- Return: `true`

### **getFotoKandidatUrl($filename)**
```php
$url = getFotoKandidatUrl('kandidat_1705431234_5678.jpg');
// Output: http://localhost/pemilihan_formatur/assets/images/kandidat/...
```
- Get full URL untuk display foto
- Jika kosong, return placeholder image

## ⚙️ Konfigurasi

### **Ubah Max Size File:**
Edit `includes/functions.php`, cari:
```php
$max_size = 5 * 1024 * 1024; // 5MB
```
Ganti dengan:
```php
$max_size = 10 * 1024 * 1024; // 10MB
```

### **Ubah Tipe File yang Diizinkan:**
Edit `includes/functions.php`, cari:
```php
$allowed_types = array('image/jpeg', 'image/png', 'image/gif');
```

### **Ubah Ukuran Tampilan Foto:**

**Di Admin (thumbnail):**
Edit `pages/admin/kelola_kandidat.php`:
```php
style="width: 60px; height: 60px; ..."
```

**Di Pemilih (voting):**
Edit `pages/pemilih/dashboard.php`:
```php
style="width: 100%; max-width: 250px; height: 250px; ..."
```

**Di Hasil:**
Edit `pages/pemilih/hasil.php`:
```php
style="width: 80px; height: 80px; ..."
```

## 🔒 Keamanan

### **Sudah Implementasi:**
✅ Validasi tipe file (MIME type)
✅ Validasi ukuran file (max 5MB)
✅ Unique filename (timestamp + random)
✅ File hanya disimpan saat form valid
✅ File dihapus saat kandidat dihapus

### **Best Practices:**
- Folder `assets/images/kandidat/` harus writable
- Set permission 755 untuk folder
- Validate di server side (tidak hanya client)
- Gunakan HTTPS untuk production

## 🐛 Troubleshooting

### **Error: "Gagal mengupload file"**
**Penyebab:**
- Folder `assets/images/kandidat/` tidak writable
- Permission issue
- Disk space penuh

**Solusi:**
1. Pastikan folder ada: `C:\xampp\htdocs\pemilihan_formatur\assets\images\kandidat\`
2. Set permission 755 (Linux/Mac) atau IUSR permissions (Windows)
3. Cek free disk space

### **Error: "Ukuran file terlalu besar"**
**Solusi:**
- Kompres gambar terlebih dahulu
- Max size: 5MB (bisa diubah di `functions.php`)
- Gunakan format JPG (lebih kecil dari PNG)

### **Error: "Hanya file gambar yang diperbolehkan"**
**Penyebab:**
- File bukan gambar
- Tipe MIME tidak sesuai

**Solusi:**
- Upload file JPG, PNG, atau GIF
- Jangan upload file lain

### **Foto tidak tampil**
**Penyebab:**
- Path salah
- File tidak ada di folder
- Browser cache

**Solusi:**
1. Cek folder `assets/images/kandidat/` ada file foto
2. Refresh browser (Ctrl+F5)
3. Cek console error (F12)

## 📊 Database

Database sudah support kolom `foto` untuk menyimpan filename:
```sql
ALTER TABLE kandidat ADD COLUMN foto VARCHAR(255);
```

Jika menggunakan database lama, jalankan query di atas di phpMyAdmin.

## 🎯 Next Steps (Optional)

### **Improvement Ideas:**
1. **Crop/Resize Foto:** Gunakan GD Library
2. **Watermark:** Tambah logo di foto
3. **Thumbnail Preview:** Generate thumbnail otomatis
4. **Drag & Drop Upload:** Lebih user-friendly
5. **Multiple Photos:** Support lebih dari 1 foto per kandidat
6. **Image Gallery:** Tampilkan galeri foto kandidat

### **Implementation Example:**

**Untuk resize foto:**
```php
function resizeFoto($source, $destination, $width, $height) {
    $image = imagecreatefromjpeg($source);
    $resized = imagecreatetruecolor($width, $height);
    imagecopyresampled($resized, $image, 0, 0, 0, 0, 
                       $width, $height, 
                       imagesx($image), imagesy($image));
    imagejpeg($resized, $destination);
    return true;
}
```

## 📞 Support

Jika ada pertanyaan atau masalah:
1. Cek dokumentasi di atas
2. Lihat folder `assets/images/kandidat/` (ada foto atau tidak)
3. Cek browser console (F12) untuk error
4. Cek phpMyAdmin untuk data yang tersimpan

---

**Fitur upload foto kandidat berhasil ditambahkan! ✅**

Happy Voting dengan Foto Kandidat! 📸🗳️
