-- Add doctor schedule table
CREATE TABLE IF NOT EXISTS doctor_schedule (
    schedule_id INT PRIMARY KEY AUTO_INCREMENT,
    dokter_id INT NOT NULL,
    hari ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu') NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    durasi_slot INT DEFAULT 30 COMMENT 'Durasi per slot dalam menit',
    status ENUM('Aktif', 'Tidak Aktif') DEFAULT 'Aktif',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dokter_id) REFERENCES veterinarian(dokter_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Insert default schedules for existing active doctors
INSERT INTO doctor_schedule (dokter_id, hari, jam_mulai, jam_selesai, durasi_slot)
SELECT v.dokter_id, 'Senin', '08:00:00', '12:00:00', 30 
FROM veterinarian v 
WHERE v.status = 'Aktif'
;

INSERT INTO doctor_schedule (dokter_id, hari, jam_mulai, jam_selesai, durasi_slot)
SELECT v.dokter_id, 'Senin', '13:00:00', '17:00:00', 30 
FROM veterinarian v 
WHERE v.status = 'Aktif'
;

INSERT INTO doctor_schedule (dokter_id, hari, jam_mulai, jam_selesai, durasi_slot)
SELECT v.dokter_id, 'Selasa', '08:00:00', '12:00:00', 30 
FROM veterinarian v 
WHERE v.status = 'Aktif'
;

INSERT INTO doctor_schedule (dokter_id, hari, jam_mulai, jam_selesai, durasi_slot)
SELECT v.dokter_id, 'Selasa', '13:00:00', '17:00:00', 30 
FROM veterinarian v 
WHERE v.status = 'Aktif'
;

INSERT INTO doctor_schedule (dokter_id, hari, jam_mulai, jam_selesai, durasi_slot)
SELECT v.dokter_id, 'Rabu', '08:00:00', '12:00:00', 30 
FROM veterinarian v 
WHERE v.status = 'Aktif'
;

INSERT INTO doctor_schedule (dokter_id, hari, jam_mulai, jam_selesai, durasi_slot)
SELECT v.dokter_id, 'Rabu', '13:00:00', '17:00:00', 30 
FROM veterinarian v 
WHERE v.status = 'Aktif'
;

INSERT INTO doctor_schedule (dokter_id, hari, jam_mulai, jam_selesai, durasi_slot)
SELECT v.dokter_id, 'Kamis', '08:00:00', '12:00:00', 30 
FROM veterinarian v 
WHERE v.status = 'Aktif'
;

INSERT INTO doctor_schedule (dokter_id, hari, jam_mulai, jam_selesai, durasi_slot)
SELECT v.dokter_id, 'Kamis', '13:00:00', '17:00:00', 30 
FROM veterinarian v 
WHERE v.status = 'Aktif'
;

INSERT INTO doctor_schedule (dokter_id, hari, jam_mulai, jam_selesai, durasi_slot)
SELECT v.dokter_id, 'Jumat', '08:00:00', '12:00:00', 30 
FROM veterinarian v 
WHERE v.status = 'Aktif'
;

INSERT INTO doctor_schedule (dokter_id, hari, jam_mulai, jam_selesai, durasi_slot)
SELECT v.dokter_id, 'Jumat', '13:00:00', '17:00:00', 30 
FROM veterinarian v 
WHERE v.status = 'Aktif'
;

INSERT INTO doctor_schedule (dokter_id, hari, jam_mulai, jam_selesai, durasi_slot)
SELECT v.dokter_id, 'Sabtu', '08:00:00', '12:00:00', 30 
FROM veterinarian v 
WHERE v.status = 'Aktif'
;
