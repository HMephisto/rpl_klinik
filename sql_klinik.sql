-- ============================================================
-- DATABASE: klinik
-- Buat database dan gunakan
-- ============================================================
CREATE DATABASE IF NOT EXISTS klinik
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE klinik;

-- ============================================================
-- HAPUS TABEL LAMA (urutan terbalik karena foreign key)
-- ============================================================
DROP TABLE IF EXISTS Antrian;
DROP TABLE IF EXISTS Struk;
DROP TABLE IF EXISTS Detail_Resep;
DROP TABLE IF EXISTS Resep;
DROP TABLE IF EXISTS Rekam_Medis;
DROP TABLE IF EXISTS Obat;
DROP TABLE IF EXISTS Pasien;
DROP TABLE IF EXISTS Pengguna;

-- ============================================================
-- 1. Tabel Pengguna (Multi-Role: Dokter, Resepsionis, Apoteker)
-- ============================================================
CREATE TABLE Pengguna (
    id_user INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('Dokter', 'Resepsionis', 'Apoteker') NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    spesialis VARCHAR(100) NULL -- Hanya diisi jika role = 'Dokter', sisanya dibiarkan NULL
);

-- ============================================================
-- 2. Tabel Pasien
-- ============================================================
CREATE TABLE Pasien (
    id_pasien INT PRIMARY KEY AUTO_INCREMENT,
    nama_pasien VARCHAR(100),
    tempat_tanggal_lahir VARCHAR(100),
    alamat_pasien TEXT,
    umur_pasien INT,
    jenis_kelamin ENUM('L', 'P'),
    berat_badan DECIMAL(5,2),
    tinggi_badan DECIMAL(5,2)
);

-- ============================================================
-- 3. Tabel Obat
-- ============================================================
CREATE TABLE Obat (
    id_obat INT PRIMARY KEY AUTO_INCREMENT,
    nama_obat VARCHAR(100),
    jenis_obat VARCHAR(50),
    harga DECIMAL(10,2) DEFAULT 0
);

-- ============================================================
-- 4. Tabel Rekam_Medis
-- ============================================================
CREATE TABLE Rekam_Medis (
    nomor_RM INT PRIMARY KEY AUTO_INCREMENT,
    id_pasien INT,
    id_dokter INT, -- Mengambil dari tabel Pengguna (dengan role 'Dokter')
    id_antrian INT NULL, -- Referensi ke tabel antrian (opsional)
    tgl_masuk DATETIME,
    alasan_masuk TEXT, -- Keluhan (Subjective)
    pemeriksaan_fisik TEXT, -- Fisik (Objective)
    diagnosa_masuk VARCHAR(100), -- Diagnosis (Assessment)
    tindakan TEXT, -- Tindakan/Plan (Plan)
    FOREIGN KEY (id_pasien) REFERENCES Pasien(id_pasien),
    FOREIGN KEY (id_dokter) REFERENCES Pengguna(id_user) 
);

-- ============================================================
-- 5. Tabel Resep (Header)
-- ============================================================
CREATE TABLE Resep (
    id_resep INT PRIMARY KEY AUTO_INCREMENT,
    nomor_RM INT,
    id_dokter INT, -- Mengambil dari tabel Pengguna
    tgl_resep DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Menunggu Racikan', 'Selesai') DEFAULT 'Menunggu Racikan',
    FOREIGN KEY (nomor_RM) REFERENCES Rekam_Medis(nomor_RM),
    FOREIGN KEY (id_dokter) REFERENCES Pengguna(id_user)
);

-- ============================================================
-- 6. Tabel Detail_Resep
-- ============================================================
CREATE TABLE Detail_Resep (
    id_detail INT PRIMARY KEY AUTO_INCREMENT,
    id_resep INT,
    id_obat INT,
    jumlah INT,
    dosis VARCHAR(50),
    FOREIGN KEY (id_resep) REFERENCES Resep(id_resep),
    FOREIGN KEY (id_obat) REFERENCES Obat(id_obat)
);

-- ============================================================
-- 7. Tabel Struk
-- ============================================================
CREATE TABLE Struk (
    id_struk INT PRIMARY KEY AUTO_INCREMENT,
    id_pasien INT,
    id_staff INT, -- Mengambil dari tabel Pengguna (Resepsionis/Kasir)
    total_harga DECIMAL(15,2),
    pemeriksaan_penunjang TEXT,
    tgl_bayar DATETIME,
    FOREIGN KEY (id_pasien) REFERENCES Pasien(id_pasien),
    FOREIGN KEY (id_staff) REFERENCES Pengguna(id_user)
);

-- ============================================================
-- 8. Tabel Antrian
-- ============================================================
CREATE TABLE Antrian (
    id_antrian INT PRIMARY KEY AUTO_INCREMENT,
    id_pasien INT,
    id_dokter INT, -- Dokter/Poli tujuan
    nomor_antrian VARCHAR(20) NOT NULL,
    tgl_antrian DATE NOT NULL,
    status ENUM('Menunggu', 'Diperiksa', 'Menunggu Obat', 'Menunggu Pembayaran', 'Selesai', 'Batal') DEFAULT 'Menunggu',
    FOREIGN KEY (id_pasien) REFERENCES Pasien(id_pasien),
    FOREIGN KEY (id_dokter) REFERENCES Pengguna(id_user)
);

-- ============================================================
-- SEED DATA: Akun Pengguna
-- Password untuk semua akun: "password"
-- (di-hash menggunakan password_hash PHP / bcrypt)
-- ============================================================

-- Resepsionis (1 akun)
INSERT INTO Pengguna (username, password, role, nama_lengkap, spesialis) VALUES
('resepsionis', '$2y$10$0bC.37oqasR7vul7z.Rz6.S1ZlrkXLXAX5mrhhr.v0LRtMPToyaRG', 'Resepsionis', 'Siti Nurhaliza', NULL);

-- Dokter (5 akun)
INSERT INTO Pengguna (username, password, role, nama_lengkap, spesialis) VALUES
('dokter1', '$2y$10$0bC.37oqasR7vul7z.Rz6.S1ZlrkXLXAX5mrhhr.v0LRtMPToyaRG', 'Dokter', 'dr. Ahmad Fauzi', 'Umum'),
('dokter2', '$2y$10$0bC.37oqasR7vul7z.Rz6.S1ZlrkXLXAX5mrhhr.v0LRtMPToyaRG', 'Dokter', 'dr. Budi Santoso', 'Penyakit Dalam'),
('dokter3', '$2y$10$0bC.37oqasR7vul7z.Rz6.S1ZlrkXLXAX5mrhhr.v0LRtMPToyaRG', 'Dokter', 'dr. Citra Dewi', 'Anak'),
('dokter4', '$2y$10$0bC.37oqasR7vul7z.Rz6.S1ZlrkXLXAX5mrhhr.v0LRtMPToyaRG', 'Dokter', 'dr. Dian Prasetyo', 'Gigi'),
('dokter5', '$2y$10$0bC.37oqasR7vul7z.Rz6.S1ZlrkXLXAX5mrhhr.v0LRtMPToyaRG', 'Dokter', 'dr. Eka Putri', 'Mata');

-- Apoteker (1 akun)
INSERT INTO Pengguna (username, password, role, nama_lengkap, spesialis) VALUES
('apoteker', '$2y$10$0bC.37oqasR7vul7z.Rz6.S1ZlrkXLXAX5mrhhr.v0LRtMPToyaRG', 'Apoteker', 'Farhan Ramadhan', NULL);