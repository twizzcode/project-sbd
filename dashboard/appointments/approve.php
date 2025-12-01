<?php
session_start();
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Only admin can approve appointments
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    $_SESSION['error'] = "Anda tidak memiliki akses untuk melakukan tindakan ini";
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
    // Check if appointment exists and is pending
    $stmt = $pdo->prepare("SELECT * FROM appointment WHERE appointment_id = ?");
    $stmt->execute([$appointment_id]);
    $appointment = $stmt->fetch();
    
    if (!$appointment) {
        $_SESSION['error'] = "Data janji temu tidak ditemukan";
        header("Location: index.php");
        exit;
    }
    
    if ($appointment['status'] !== 'Pending') {
        $_SESSION['error'] = "Hanya appointment dengan status Pending yang dapat disetujui";
        header("Location: detail.php?id=" . $appointment_id);
        exit;
    }
    
    // Update status to Confirmed
    $stmt = $pdo->prepare("
        UPDATE appointment 
        SET status = 'Confirmed', 
            updated_at = NOW() 
        WHERE appointment_id = ?
    ");
    $stmt->execute([$appointment_id]);
    
    $_SESSION['success'] = "Janji temu berhasil disetujui";
    header("Location: detail.php?id=" . $appointment_id);
    exit;
    
} catch (PDOException $e) {
    error_log("Error approving appointment: " . $e->getMessage());
    $_SESSION['error'] = "Terjadi kesalahan saat menyetujui janji temu";
    header("Location: detail.php?id=" . $appointment_id);
    exit;
}
