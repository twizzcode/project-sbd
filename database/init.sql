-- ============================================
-- VetClinic Management System - Unified Auth
-- Database Schema Version 3.0
-- Created: 2025-12-01
-- Unified authentication: Admin & Owner only
-- ============================================

-- Drop database if exists (BE CAREFUL IN PRODUCTION!)
DROP DATABASE IF EXISTS vetclinic;

-- Create database
CREATE DATABASE IF NOT EXISTS vetclinic 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE vetclinic;

-- ============================================
-- TABLE: users (UNIFIED)
-- Semua user: Admin dan Owner
-- ============================================
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('Admin', 'Owner') NOT NULL,
    no_telepon VARCHAR(15),
    alamat TEXT,
    foto_url VARCHAR(255),
    status ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif',
    email_verified BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login DATETIME,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- ============================================
-- TABLE: veterinarian
-- Dokter hewan
-- ============================================
CREATE TABLE veterinarian (
    dokter_id INT PRIMARY KEY AUTO_INCREMENT,
    nama_dokter VARCHAR(100) NOT NULL,
    no_lisensi VARCHAR(50) UNIQUE,
    spesialisasi ENUM('Umum', 'Bedah', 'Gigi', 'Kulit', 'Kardio', 'Eksotik') DEFAULT 'Umum',
    no_telepon VARCHAR(15),
    email VARCHAR(100),
    jadwal_praktek TEXT,
    status ENUM('Aktif', 'Cuti', 'Resign') DEFAULT 'Aktif',
    foto_url VARCHAR(255),
    tanggal_bergabung DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_spesialisasi (spesialisasi)
) ENGINE=InnoDB;

-- ============================================
-- TABLE: pet
-- Hewan peliharaan (owner_id sekarang reference ke users)
-- ============================================
CREATE TABLE pet (
    pet_id INT PRIMARY KEY AUTO_INCREMENT,
    owner_id INT NOT NULL, -- Reference ke users table (role = Owner)
    nama_hewan VARCHAR(50) NOT NULL,
    jenis ENUM('Anjing', 'Kucing', 'Burung', 'Kelinci', 'Hamster', 'Reptil', 'Lainnya'),
    ras VARCHAR(50),
    jenis_kelamin ENUM('Jantan', 'Betina'),
    tanggal_lahir DATE,
    berat_badan DECIMAL(5,2),
    warna VARCHAR(50),
    ciri_khusus TEXT,
    foto_url VARCHAR(255),
    status ENUM('Aktif', 'Meninggal') DEFAULT 'Aktif',
    tanggal_registrasi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_owner (owner_id),
    INDEX idx_status (status),
    INDEX idx_jenis (jenis)
) ENGINE=InnoDB;

-- ============================================
-- TABLE: appointment
-- Janji temu pemeriksaan
-- ============================================
CREATE TABLE appointment (
    appointment_id INT PRIMARY KEY AUTO_INCREMENT,
    pet_id INT NOT NULL,
    owner_id INT NOT NULL, -- Reference ke users table
    dokter_id INT NOT NULL,
    tanggal_appointment DATE NOT NULL,
    jam_appointment TIME NOT NULL,
    jenis_layanan VARCHAR(100),
    status ENUM('Pending', 'Confirmed', 'Completed', 'Cancelled', 'No_Show') DEFAULT 'Pending',
    keluhan_awal TEXT,
    catatan TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pet(pet_id) ON DELETE CASCADE,
    FOREIGN KEY (owner_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (dokter_id) REFERENCES veterinarian(dokter_id),
    INDEX idx_tanggal (tanggal_appointment),
    INDEX idx_status (status),
    INDEX idx_pet (pet_id),
    INDEX idx_owner (owner_id),
    INDEX idx_dokter (dokter_id)
) ENGINE=InnoDB;

-- ============================================
-- TABLE: medical_record
-- Rekam medis/pertemuan
-- ============================================
CREATE TABLE medical_record (
    record_id INT PRIMARY KEY AUTO_INCREMENT,
    pet_id INT NOT NULL,
    dokter_id INT NOT NULL,
    appointment_id INT,
    tanggal_kunjungan DATETIME DEFAULT CURRENT_TIMESTAMP,
    keluhan TEXT NOT NULL,
    diagnosis TEXT NOT NULL,
    tindakan TEXT,
    resep TEXT,
    catatan_dokter TEXT,
    berat_badan DECIMAL(5,2),
    suhu_tubuh DECIMAL(4,1),
    biaya DECIMAL(10,2),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pet(pet_id) ON DELETE CASCADE,
    FOREIGN KEY (dokter_id) REFERENCES veterinarian(dokter_id),
    FOREIGN KEY (appointment_id) REFERENCES appointment(appointment_id) ON DELETE SET NULL,
    INDEX idx_pet (pet_id),
    INDEX idx_dokter (dokter_id),
    INDEX idx_appointment (appointment_id),
    INDEX idx_tanggal (tanggal_kunjungan)
) ENGINE=InnoDB;

-- ============================================
-- TABLE: doctor_schedule
-- Jadwal praktek dokter
-- ============================================
CREATE TABLE doctor_schedule (
    schedule_id INT PRIMARY KEY AUTO_INCREMENT,
    dokter_id INT NOT NULL,
    hari ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu') NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    durasi_slot INT DEFAULT 30 COMMENT 'Durasi per slot dalam menit',
    status ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dokter_id) REFERENCES veterinarian(dokter_id) ON DELETE CASCADE,
    INDEX idx_dokter (dokter_id),
    INDEX idx_hari (hari),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- ============================================
-- TABLE: rate_limits
-- Pembatasan request API/Halaman
-- ============================================
CREATE TABLE rate_limits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    request_key VARCHAR(255) NOT NULL,
    request_count INT DEFAULT 1,
    expires_at INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_key (request_key),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB;

-- ============================================
-- INSERT DEFAULT DATA
-- ============================================

-- Default Users
-- Admin: admin / admin123
-- Owner Demo: owner_demo / password123
INSERT INTO users (username, email, password, nama_lengkap, role, no_telepon, status) VALUES 
('admin', 'admin@vetclinic.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'Admin', '081234567890', 'Aktif');

-- Default Veterinarian
INSERT INTO veterinarian (nama_dokter, no_lisensi, spesialisasi, no_telepon, email, status, tanggal_bergabung) VALUES
('Dr. Sarah Johnson', 'VET-2024-001', 'Umum', '081234567890', 'dr.sarah@vetclinic.com', 'Aktif', '2024-01-15'),
('Dr. Michael Chen', 'VET-2024-002', 'Bedah', '081234567891', 'dr.michael@vetclinic.com', 'Aktif', '2024-02-01'),
('Dr. Emily Rodriguez', 'VET-2024-003', 'Gigi', '081234567892', 'dr.emily@vetclinic.com', 'Aktif', '2024-03-10');

-- Default Doctor Schedule (Senin - Sabtu, 08:00-12:00 dan 13:00-17:00)
INSERT INTO doctor_schedule (dokter_id, hari, jam_mulai, jam_selesai, durasi_slot, status)
SELECT 
    v.dokter_id,
    d.hari,
    d.jam_mulai,
    d.jam_selesai,
    30,
    'Aktif'
FROM veterinarian v
CROSS JOIN (
    SELECT 'Senin' AS hari, '08:00:00' AS jam_mulai, '12:00:00' AS jam_selesai UNION ALL
    SELECT 'Senin', '13:00:00', '17:00:00' UNION ALL
    SELECT 'Selasa', '08:00:00', '12:00:00' UNION ALL
    SELECT 'Selasa', '13:00:00', '17:00:00' UNION ALL
    SELECT 'Rabu', '08:00:00', '12:00:00' UNION ALL
    SELECT 'Rabu', '13:00:00', '17:00:00' UNION ALL
    SELECT 'Kamis', '08:00:00', '12:00:00' UNION ALL
    SELECT 'Kamis', '13:00:00', '17:00:00' UNION ALL
    SELECT 'Jumat', '08:00:00', '12:00:00' UNION ALL
    SELECT 'Jumat', '13:00:00', '17:00:00' UNION ALL
    SELECT 'Sabtu', '08:00:00', '12:00:00'
) AS d
WHERE v.status = 'Aktif';

-- ============================================
-- VIEWS (Optional - untuk kemudahan query)
-- ============================================

-- View untuk appointment dengan detail lengkap
CREATE OR REPLACE VIEW v_appointment_detail AS
SELECT 
    a.appointment_id,
    a.tanggal_appointment,
    a.jam_appointment,
    a.jenis_layanan,
    a.status,
    a.keluhan_awal,
    a.catatan,
    a.created_at,
    p.pet_id,
    p.nama_hewan,
    p.jenis AS jenis_hewan,
    p.ras,
    u.user_id AS owner_id,
    u.nama_lengkap AS owner_name,
    u.no_telepon AS owner_phone,
    u.email AS owner_email,
    v.dokter_id,
    v.nama_dokter,
    v.spesialisasi
FROM appointment a
JOIN pet p ON a.pet_id = p.pet_id
JOIN users u ON a.owner_id = u.user_id
JOIN veterinarian v ON a.dokter_id = v.dokter_id;

-- View untuk medical record dengan detail lengkap
CREATE OR REPLACE VIEW v_medical_record_detail AS
SELECT 
    mr.record_id,
    mr.tanggal_kunjungan,
    mr.keluhan,
    mr.diagnosis,
    mr.tindakan,
    mr.resep,
    mr.catatan_dokter,
    mr.berat_badan,
    mr.suhu_tubuh,
    mr.biaya,
    p.pet_id,
    p.nama_hewan,
    p.jenis AS jenis_hewan,
    u.user_id AS owner_id,
    u.nama_lengkap AS owner_name,
    v.dokter_id,
    v.nama_dokter,
    a.appointment_id
FROM medical_record mr
JOIN pet p ON mr.pet_id = p.pet_id
JOIN users u ON p.owner_id = u.user_id
JOIN veterinarian v ON mr.dokter_id = v.dokter_id
LEFT JOIN appointment a ON mr.appointment_id = a.appointment_id;

-- ============================================
-- STORED PROCEDURES (Optional)
-- ============================================

DELIMITER //

-- Procedure untuk approve appointment
CREATE PROCEDURE sp_approve_appointment(
    IN p_appointment_id INT
)
BEGIN
    UPDATE appointment 
    SET status = 'Confirmed', 
        updated_at = NOW() 
    WHERE appointment_id = p_appointment_id 
    AND status = 'Pending';
END //

-- Procedure untuk reject appointment
CREATE PROCEDURE sp_reject_appointment(
    IN p_appointment_id INT
)
BEGIN
    UPDATE appointment 
    SET status = 'Cancelled', 
        updated_at = NOW() 
    WHERE appointment_id = p_appointment_id 
    AND status = 'Pending';
END //

DELIMITER ;

-- ============================================
-- TRIGGERS (Optional - untuk audit trail)
-- ============================================

-- Trigger untuk update timestamp appointment
DELIMITER //

CREATE TRIGGER trg_appointment_updated
BEFORE UPDATE ON appointment
FOR EACH ROW
BEGIN
    SET NEW.updated_at = NOW();
END //

DELIMITER ;

-- ============================================
-- INDEXES untuk Performance
-- ============================================

-- Sudah dibuat di dalam CREATE TABLE statements

-- ============================================
-- GRANTS (Optional - untuk security)
-- ============================================

-- Uncomment jika ingin membuat user khusus
-- CREATE USER 'vetclinic_user'@'localhost' IDENTIFIED BY 'your_secure_password';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON vetclinic.* TO 'vetclinic_user'@'localhost';
-- FLUSH PRIVILEGES;

-- ============================================
-- DATABASE SETUP COMPLETE
-- ============================================

-- Login Credentials:
-- Admin: admin / admin123
-- Owner Demo: owner_demo / password123

SELECT 'Database vetclinic created successfully!' AS Status;
SELECT 'Unified authentication with Admin & Owner roles' AS Info;
SELECT COUNT(*) AS 'Total Tables' FROM information_schema.tables WHERE table_schema = 'vetclinic';
