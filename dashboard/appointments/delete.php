<?php
session_start();
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/appointment_functions.php';

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net code.jquery.com cdn.datatables.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com cdn.datatables.net fonts.googleapis.com; img-src 'self' data: https:; font-src 'self' cdnjs.cloudflare.com fonts.gstatic.com data:");

// Validate user role
if ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Dokter') {
    $_SESSION['error'] = "Anda tidak memiliki akses untuk menghapus janji temu";
    header("Location: index.php");
    exit;
}

// Get appointment ID
$appointment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$appointment_id) {
    $_SESSION['error'] = "ID Janji Temu tidak valid";
    header("Location: index.php");
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Get appointment details first
    $stmt = $pdo->prepare("
        SELECT 
            a.*,
            p.nama_hewan,
            o.owner_id,
            o.nama_lengkap as owner_name,
            o.no_telepon as owner_phone
        FROM appointment a
        JOIN pet p ON a.pet_id = p.pet_id
        JOIN users o ON a.owner_id = o.user_id
        WHERE a.appointment_id = ?
    ");
    $stmt->execute([$appointment_id]);
    $appointment = $stmt->fetch();

    if (!$appointment) {
        throw new Exception("Data janji temu tidak ditemukan");
    }

    // Check if appointment can be deleted
    $appointment_datetime = $appointment['tanggal_appointment'] . ' ' . $appointment['jam_appointment'];
    $appointment_date = strtotime($appointment_datetime);
    $now = time();

    // Only allow deletion of future appointments or by admin
    if ($appointment_date < $now && $_SESSION['role'] !== 'Admin') {
        throw new Exception("Tidak dapat menghapus janji temu yang sudah lewat");
    }

    // Delete the appointment (CASCADE will handle medical_record)
    $stmt = $pdo->prepare("DELETE FROM appointment WHERE appointment_id = ?");
    $stmt->execute([$appointment_id]);

    // Commit transaction
    $pdo->commit();

    // Set success message
    $_SESSION['success'] = "Janji temu berhasil dihapus";

} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    
    // Log error
    error_log("Error deleting appointment #$appointment_id: " . $e->getMessage());
    
    // Set error message
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

// Redirect back to index
header("Location: index.php");
exit;