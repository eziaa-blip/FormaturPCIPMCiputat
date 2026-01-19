# 📊 SQL Query untuk phpMyAdmin

Gunakan query berikut di phpMyAdmin untuk testing dan monitoring.

## 🔍 Query Testing

### 1. Cek Semua User
```sql
SELECT * FROM users;
```

### 2. Cek Semua Kandidat
```sql
SELECT * FROM kandidat;
```

### 3. Cek Semua Voting
```sql
SELECT * FROM voting;
```

### 4. Cek Periode Pemilihan
```sql
SELECT * FROM periode_pemilihan;
```

## 📈 Query Statistik

### 1. Hasil Voting dengan Persentase
```sql
SELECT 
    kandidat.id_kandidat,
    kandidat.nama_kandidat,
    COUNT(voting.id_voting) AS jumlah_suara,
    ROUND((COUNT(voting.id_voting) / (SELECT COUNT(*) FROM voting) * 100), 2) AS persentase
FROM kandidat
LEFT JOIN voting ON kandidat.id_kandidat = voting.id_kandidat
GROUP BY kandidat.id_kandidat
ORDER BY jumlah_suara DESC;
```

### 2. Total Pemilih
```sql
SELECT COUNT(*) AS total_pemilih FROM users WHERE role = 'pemilih';
```

### 3. Total Suara Masuk
```sql
SELECT COUNT(*) AS total_suara FROM voting;
```

### 4. Tingkat Partisipasi
```sql
SELECT 
    COUNT(DISTINCT CASE WHEN voting.id_user IS NOT NULL THEN voting.id_user END) AS sudah_memilih,
    COUNT(DISTINCT users.id_user) AS total_pemilih,
    ROUND((COUNT(DISTINCT CASE WHEN voting.id_user IS NOT NULL THEN voting.id_user END) / 
            COUNT(DISTINCT users.id_user) * 100), 2) AS persentase_partisipasi
FROM users
LEFT JOIN voting ON users.id_user = voting.id_user
WHERE users.role = 'pemilih';
```

### 5. Pemilih yang Belum Memilih
```sql
SELECT id_user, nama, username FROM users 
WHERE role = 'pemilih' AND sudah_memilih = 0
ORDER BY nama;
```

### 6. Pemilih yang Sudah Memilih
```sql
SELECT u.id_user, u.nama, u.username, k.nama_kandidat, v.waktu_memilih
FROM users u
JOIN voting v ON u.id_user = v.id_user
JOIN kandidat k ON v.id_kandidat = k.id_kandidat
ORDER BY v.waktu_memilih DESC;
```

## 🔧 Query Management

### 1. Reset Voting (Hapus Semua Suara)
```sql
DELETE FROM voting;
UPDATE users SET sudah_memilih = 0 WHERE role = 'pemilih';
```

### 2. Reset Password Pemilih
```sql
-- Password: 123456 (MD5)
UPDATE users SET password = MD5('123456') WHERE role = 'pemilih';
```

### 3. Reset Password Admin
```sql
-- Password: admin123 (MD5)
UPDATE users SET password = MD5('admin123') WHERE role = 'admin';
```

### 4. Tambah User Baru
```sql
INSERT INTO users (nama, username, password, role) 
VALUES ('Nama Pemilih', 'username', MD5('password'), 'pemilih');
```

### 5. Tambah Kandidat Baru
```sql
INSERT INTO kandidat (nama_kandidat, visi, misi) 
VALUES ('Nama Kandidat', 'Visi kandidat', 'Misi kandidat');
```

### 6. Hapus User Tertentu
```sql
DELETE FROM users WHERE id_user = [id];
```

### 7. Hapus Kandidat Tertentu
```sql
DELETE FROM kandidat WHERE id_kandidat = [id];
```

### 8. Hapus Voting User Tertentu
```sql
DELETE FROM voting WHERE id_user = [id];
UPDATE users SET sudah_memilih = 0 WHERE id_user = [id];
```

## 🔐 Referential Integrity Check

### Cek Foreign Key Constraint
```sql
-- Cek voting dengan user yang sudah dihapus
SELECT v.* FROM voting v
LEFT JOIN users u ON v.id_user = u.id_user
WHERE u.id_user IS NULL;

-- Cek voting dengan kandidat yang sudah dihapus
SELECT v.* FROM voting v
LEFT JOIN kandidat k ON v.id_kandidat = k.id_kandidat
WHERE k.id_kandidat IS NULL;
```

## 📋 Query untuk Export

### Export Hasil Voting (CSV Ready)
```sql
SELECT 
    CONCAT(ROW_NUMBER() OVER (ORDER BY COUNT(voting.id_voting) DESC), '.') AS urutan,
    kandidat.nama_kandidat,
    COUNT(voting.id_voting) AS jumlah_suara,
    CONCAT(ROUND((COUNT(voting.id_voting) / (SELECT COUNT(*) FROM voting) * 100), 2), '%') AS persentase
FROM kandidat
LEFT JOIN voting ON kandidat.id_kandidat = voting.id_kandidat
GROUP BY kandidat.id_kandidat
ORDER BY jumlah_suara DESC;
```

### Export Daftar Pemilih & Status
```sql
SELECT 
    CONCAT(ROW_NUMBER() OVER (ORDER BY u.id_user), '.') AS no,
    u.nama,
    u.username,
    CASE WHEN u.sudah_memilih = 1 THEN 'Sudah Memilih' ELSE 'Belum Memilih' END AS status_memilih,
    DATE_FORMAT(u.created_at, '%d-%m-%Y %H:%i:%s') AS waktu_daftar
FROM users u
WHERE u.role = 'pemilih'
ORDER BY u.id_user;
```

## 📊 Database Info

### Cek Ukuran Database
```sql
SELECT 
    TABLE_NAME,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'pemilihan_formatur'
ORDER BY (data_length + index_length) DESC;
```

### Cek Total Row Count
```sql
SELECT 
    TABLE_NAME,
    TABLE_ROWS
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'pemilihan_formatur';
```

## 🚀 Optimization Query

### Index untuk Performance (Optional)
```sql
-- Jika database besar, tambahkan index
CREATE INDEX idx_voting_user ON voting(id_user);
CREATE INDEX idx_voting_kandidat ON voting(id_kandidat);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_sudah_memilih ON users(sudah_memilih);
```

### Cek Slow Query
```sql
-- Aktifkan slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;
```

---

**Tips:** Copy-paste query langsung ke phpMyAdmin untuk hasil instant!
