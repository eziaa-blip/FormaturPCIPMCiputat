<?php
// ================================================================
// DATABASE SCHEMA & SETUP GUIDE
// ================================================================
// Jalankan file ini jika setup.php tidak bekerja
// Copy-paste query ke phpMyAdmin

/*

-- =========================================================
-- DATABASE CREATION
-- =========================================================
CREATE DATABASE IF NOT EXISTS pemilihan_formatur;
USE pemilihan_formatur;

-- =========================================================
-- TABLE: users
-- =========================================================
CREATE TABLE IF NOT EXISTS users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','pemilih') NOT NULL,
    sudah_memilih TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- TABLE: kandidat
-- =========================================================
CREATE TABLE IF NOT EXISTS kandidat (
    id_kandidat INT AUTO_INCREMENT PRIMARY KEY,
    nama_kandidat VARCHAR(100) NOT NULL,
    visi TEXT,
    misi TEXT,
    foto VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- TABLE: voting
-- =========================================================
CREATE TABLE IF NOT EXISTS voting (
    id_voting INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_kandidat INT NOT NULL,
    waktu_memilih TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_voting_user
        FOREIGN KEY (id_user) REFERENCES users(id_user)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_voting_kandidat
        FOREIGN KEY (id_kandidat) REFERENCES kandidat(id_kandidat)
        ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uq_user_satu_suara (id_user)
) ENGINE=InnoDB;

-- =========================================================
-- TABLE: periode_pemilihan
-- =========================================================
CREATE TABLE IF NOT EXISTS periode_pemilihan (
    id_periode INT AUTO_INCREMENT PRIMARY KEY,
    nama_periode VARCHAR(100),
    tanggal_mulai DATE,
    tanggal_selesai DATE,
    status ENUM('aktif','nonaktif') DEFAULT 'nonaktif'
) ENGINE=InnoDB;

-- =========================================================
-- INSERT DATA AWAL
-- =========================================================

-- Admin
INSERT INTO users (nama, username, password, role) 
VALUES ('Admin Utama', 'admin', MD5('admin123'), 'admin');

-- Pemilih
INSERT INTO users (nama, username, password, role) 
VALUES 
('Budi', 'budi01', MD5('123456'), 'pemilih'),
('Siti', 'siti01', MD5('123456'), 'pemilih');

-- Kandidat
INSERT INTO kandidat (nama_kandidat, visi, misi) 
VALUES 
('Andi Saputra', 'Mewujudkan organisasi yang solid dan bersatu', 'Transparansi, Kolaborasi, dan Integritas'),
('Rizky Pratama', 'Organisasi aktif dan progresif menghadapi tantangan', 'Inovasi, Aspiratif, dan Responsif');

-- Periode Pemilihan
INSERT INTO periode_pemilihan (nama_periode, tanggal_mulai, tanggal_selesai, status) 
VALUES ('Pemilihan Formatur 2026', '2026-01-01', '2026-01-31', 'aktif');

*/
?>
