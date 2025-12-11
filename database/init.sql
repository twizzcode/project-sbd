
-- Drop database if exists (BE CAREFUL IN PRODUCTION!)
DROP DATABASE IF EXISTS vetclinic;

-- Create database
CREATE DATABASE IF NOT EXISTS vetclinic 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE vetclinic;

CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('Admin', 'Owner') NOT NULL,
    no_telepon VARCHAR(15),
    alamat TEXT,
    status ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif',

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
    status ENUM('Aktif', 'Cuti', 'Resign') DEFAULT 'Aktif',
    tanggal_bergabung DATE,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- TABLE: pet
-- Hewan peliharaan (owner_id sekarang reference ke users)
-- ============================================
CREATE TABLE pet (
    pet_id INT PRIMARY KEY AUTO_INCREMENT,
    owner_id INT NOT NULL,
    nama_hewan VARCHAR(50) NOT NULL,
    jenis ENUM('Anjing', 'Kucing', 'Burung', 'Kelinci', 'Hamster', 'Reptil'),
    ras VARCHAR(50),
    jenis_kelamin ENUM('Jantan', 'Betina'),
    tanggal_lahir DATE,
    berat_badan DECIMAL(5,2),
    warna VARCHAR(50),
    ciri_khusus TEXT,
    status ENUM('Aktif', 'Meninggal') DEFAULT 'Aktif',

    FOREIGN KEY (owner_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- TABLE: appointment
-- Janji temu pemeriksaan
-- ============================================
CREATE TABLE appointment (
    appointment_id INT PRIMARY KEY AUTO_INCREMENT,
    pet_id INT NOT NULL,
    owner_id INT NOT NULL,
    dokter_id INT NOT NULL,
    tanggal_appointment DATE NOT NULL,
    keluhan_awal TEXT,
    catatan TEXT,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (pet_id) REFERENCES pet(pet_id) ON DELETE CASCADE,
    FOREIGN KEY (owner_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (dokter_id) REFERENCES veterinarian(dokter_id)
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
    keluhan TEXT,
    diagnosis TEXT NOT NULL,
    tindakan TEXT,
    resep TEXT,
    catatan_dokter TEXT,
    berat_badan DECIMAL(5,2),
    suhu_tubuh DECIMAL(4,1),

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (pet_id) REFERENCES pet(pet_id) ON DELETE CASCADE,
    FOREIGN KEY (dokter_id) REFERENCES veterinarian(dokter_id),
    FOREIGN KEY (appointment_id) REFERENCES appointment(appointment_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- INSERT DEFAULT DATA
-- ============================================

-- Default Admin User
-- Admin: admin / admin123
INSERT INTO users (username, email, password, nama_lengkap, role, no_telepon, status) VALUES 
('admin', 'admin@vetclinic.com', 'admin123', 'Administrator', 'Admin', '081234567890', 'Aktif');

-- Default Veterinarian
INSERT INTO veterinarian (nama_dokter, no_lisensi, spesialisasi, no_telepon, email, status, tanggal_bergabung) VALUES
('Dr. Sarah Johnson', 'VET-2024-001', 'Umum', '081234567890', 'dr.sarah@vetclinic.com', 'Aktif', '2024-01-15'),
('Dr. Michael Chen', 'VET-2024-002', 'Bedah', '081234567891', 'dr.michael@vetclinic.com', 'Aktif', '2024-02-01'),
('Dr. Emily Rodriguez', 'VET-2024-003', 'Gigi', '081234567892', 'dr.emily@vetclinic.com', 'Aktif', '2024-03-10');
