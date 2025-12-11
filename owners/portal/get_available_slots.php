<?php
require_once __DIR__ . '/../../config/database.php';
session_start();

// Check if owner is logged in
if (!isset($_SESSION['owner_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$tanggal = $_GET['tanggal'] ?? null;
$hari = $_GET['hari'] ?? null;

if (!$tanggal || !$hari) {
    echo json_encode([]);
    exit;
}

// Get doctors with their schedules for this day
try {
$result = mysqli_query($conn, "
    SELECT 
        v.dokter_id,
        v.nama_dokter,
        v.spesialisasi,
        ds.jam_mulai,
        ds.jam_selesai,
        ds.durasi_slot
    FROM veterinarian v
    INNER JOIN doctor_schedule ds ON v.dokter_id = ds.dokter_id
    WHERE v.status = 'Aktif' 
    AND ds.status = 'Aktif'
    AND ds.hari = '$hari'
    ORDER BY v.nama_dokter, ds.jam_mulai
");

$schedules = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get existing appointments for this date
$result = mysqli_query($conn, "
    SELECT dokter_id, jam_appointment
    FROM appointment
    WHERE tanggal_appointment = '$tanggal'
    AND status NOT IN ('Cancelled', 'No_Show')
");

$booked = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Create a map of booked slots
$bookedSlots = [];
foreach ($booked as $b) {
    $key = $b['dokter_id'] . '_' . $b['jam_appointment'];
    $bookedSlots[$key] = true;
}

// Generate time slots for each doctor
$doctors = [];
$currentDoctorId = null;
$currentDoctor = null;

foreach ($schedules as $schedule) {
    if ($currentDoctorId !== $schedule['dokter_id']) {
        if ($currentDoctor) {
            $doctors[] = $currentDoctor;
        }
        
        $currentDoctorId = $schedule['dokter_id'];
        $currentDoctor = [
            'dokter_id' => $schedule['dokter_id'],
            'nama_dokter' => $schedule['nama_dokter'],
            'spesialisasi' => $schedule['spesialisasi'],
            'slots' => []
        ];
    }
    
    // Generate time slots for this schedule block
    $startTime = new DateTime($schedule['jam_mulai']);
    $endTime = new DateTime($schedule['jam_selesai']);
    $duration = intval($schedule['durasi_slot']);
    
    if ($duration <= 0) $duration = 30; // Safety fallback
    
    $maxSlots = 100; // Prevent infinite loop
    $count = 0;

    while ($startTime < $endTime && $count < $maxSlots) {
        $timeStr = $startTime->format('H:i:s');
        $displayTime = $startTime->format('H:i');
        $key = $schedule['dokter_id'] . '_' . $timeStr;
        
        // Check if this time has passed today
        $isPast = false;
        if ($tanggal === date('Y-m-d')) {
            $now = new DateTime();
            $isPast = $startTime <= $now;
        }
        
        $currentDoctor['slots'][] = [
            'time' => $timeStr,
            'display' => $displayTime,
            'available' => !isset($bookedSlots[$key]) && !$isPast
        ];
        
        $startTime->modify("+{$duration} minutes");
        $count++;
    }
}

if ($currentDoctor) {
    $doctors[] = $currentDoctor;
}

header('Content-Type: application/json');
echo json_encode($doctors);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
