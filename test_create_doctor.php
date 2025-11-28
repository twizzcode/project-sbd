<?php
session_start();
$_SESSION['role'] = 'Staff'; // Simulate Staff role
$_SESSION['user_id'] = 999;

require_once __DIR__ . '/config/database.php';

// Mock POST data
$_POST['nama_dokter'] = 'Dr. Test Debug';
$_POST['no_lisensi'] = 'VET-DEBUG-001';
$_POST['spesialisasi'] = 'Umum';
$_POST['no_telepon'] = '08123456789';
$_POST['email'] = 'debug@test.com';
$_POST['jadwal_praktek'] = 'Senin-Jumat';
$_POST['tanggal_bergabung'] = date('Y-m-d');
$_POST['status'] = 'Aktif';

echo "Testing doctor creation...\n";

try {
    $pdo->beginTransaction();

    // 1. Insert Veterinarian
    $stmt = $pdo->prepare("
        INSERT INTO veterinarian (
            nama_dokter, no_lisensi, spesialisasi,
            no_telepon, email, jadwal_praktek,
            tanggal_bergabung, status
        ) VALUES (
            ?, ?, ?,
            ?, ?, ?,
            ?, ?
        )
    ");

    $stmt->execute([
        $_POST['nama_dokter'],
        $_POST['no_lisensi'],
        $_POST['spesialisasi'],
        $_POST['no_telepon'],
        $_POST['email'],
        $_POST['jadwal_praktek'],
        $_POST['tanggal_bergabung'],
        $_POST['status']
    ]);

    $dokter_id = $pdo->lastInsertId();
    echo "Doctor inserted with ID: $dokter_id\n";

    // 2. Create Default Schedule
    $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
    $scheduleStmt = $pdo->prepare("
        INSERT INTO doctor_schedule (
            dokter_id, hari, jam_mulai, jam_selesai, durasi_slot, status
        ) VALUES (
            ?, ?, '09:00:00', '17:00:00', 30, 'Aktif'
        )
    ");

    foreach ($days as $day) {
        $scheduleStmt->execute([$dokter_id, $day]);
    }
    echo "Schedules created.\n";

    $pdo->commit();
    echo "Transaction committed successfully.\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
